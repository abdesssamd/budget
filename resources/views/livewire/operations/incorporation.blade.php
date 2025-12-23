<?php

use Livewire\Volt\Component;
use App\Models\BdgOperationBudg;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use App\Models\BdgObj1;
use App\Models\BdgObj2;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    
    // Listes dynamiques
    public $budgets = [];
    public $sections = [];
    public $listeObj1 = []; // Dépend de la Section
    public $listeObj2 = []; // Dépend de OBJ1

    public array $form = [
        'IDBudjet' => '',
        'IDSection' => '',
        'IDObj1' => '',
        'IDObj2' => '', 
        'designation' => 'Budget Primitif', 
        'Mont_operation' => 0,
        'EXERCICE' => '',
    ];

    public function mount()
    {
        $this->budgets = BdgBudget::where('Archive', 0)->orderByDesc('EXERCICE')->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        // On ne charge pas OBJ1 au début, on attend le choix de la section
        $this->listeObj1 = [];
        $this->form['EXERCICE'] = date('Y');
    }

    // Mise à jour de l'exercice selon le budget
    public function updatedFormIDBudjet($value)
    {
        $b = $this->budgets->find($value);
        if($b) $this->form['EXERCICE'] = $b->EXERCICE;
    }

    // --- CORRECTION MAJEURE ICI ---
    // Quand on choisit une Section -> On charge les Chapitres (OBJ1) de cette section
    public function updatedFormIDSection($value)
    {
        // Reset des niveaux inférieurs
        $this->form['IDObj1'] = '';
        $this->form['IDObj2'] = '';
        $this->listeObj2 = [];

        if ($value) {
            $this->listeObj1 = BdgObj1::where('IDSection', $value)
                                      ->orderBy('Num')
                                      ->get();
        } else {
            $this->listeObj1 = [];
        }
    }

    // Quand on choisit un Chapitre -> On charge les Articles (OBJ2)
    public function updatedFormIDObj1($value)
    {
        $this->form['IDObj2'] = '';
        $this->listeObj2 = $value ? BdgObj2::where('IDObj1', $value)->orderBy('Num')->get() : [];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->form['Mont_operation'] = 0;
        $this->form['designation'] = 'Budget Primitif';
        // On garde les sélections précédentes pour enchainer la saisie rapide
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required',
            'form.IDObj1' => 'required',
            'form.Mont_operation' => 'required|numeric|min:0.01',
            'form.designation' => 'required|string',
        ]);

        BdgOperationBudg::create([
            'Num_operation' => rand(10000,99999),
            'designation' => $this->form['designation'],
            'Mont_operation' => $this->form['Mont_operation'],
            'Type_operation' => 1, // Incorporation
            'EXERCICE' => $this->form['EXERCICE'],
            'IDBudjet' => $this->form['IDBudjet'],
            'IDSection' => $this->form['IDSection'],
            'IDObj1' => $this->form['IDObj1'],
            'IDObj2' => $this->form['IDObj2'] ?: 0,
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]);

        $this->showModal = false;
        session()->flash('success', 'Crédit alloué avec succès !');
    }

    public function delete($id)
    {
        BdgOperationBudg::findOrFail($id)->delete();
        session()->flash('success', 'Opération supprimée.');
    }

    public function with()
    {
        return [
            'operations' => BdgOperationBudg::with(['budget', 'section', 'obj1', 'obj2'])
                ->where('Type_operation', 1)
                ->orderByDesc('Creer_le')
                ->paginate(10)
        ];
    }
}; ?>

<div>
    {{-- Bouton et Entête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold"><i class="fas fa-coins text-success mr-2"></i>Incorporation Initiale</h4>
        <button wire:click="openModal()" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Ajouter un Crédit
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tableau --}}
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Historique</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Budget</th>
                        <th>Section</th>
                        <th>Ligne Budgétaire</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                    <tr>
                        <td><span class="badge badge-primary">{{ $op->budget->designation ?? '?' }}</span></td>
                        <td class="small font-weight-bold">{{ $op->section->Num_section ?? '' }} - {{ Str::limit($op->section->NOM_section ?? '', 15) }}</td>
                        <td>
                            <div class="text-bold text-dark">{{ $op->obj1->Num ?? '' }} - {{ $op->obj1->designation ?? '' }}</div>
                            @if($op->IDObj2)
                                <div class="text-muted small ml-3"><i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj2->Num ?? '' }} {{ $op->obj2->designation ?? '' }}</div>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            {{ number_format($op->Mont_operation, 2, ',', ' ') }} DA
                        </td>
                        <td class="text-right">
                            <button wire:click="delete({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-outline-danger" onclick="confirm('Supprimer ?') || event.stopImmediatePropagation()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <div class="float-right">{{ $operations->links() }}</div>
        </div>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Nouveau Crédit</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        <div class="row bg-light p-2 rounded mb-3 border">
                            <div class="col-md-6">
                                <label class="small text-muted font-weight-bold">Budget</label>
                                <select wire:model.live="form.IDBudjet" class="form-control">
                                    <option value="">-- Choisir --</option>
                                    @foreach($budgets as $b)
                                        <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option>
                                    @endforeach
                                </select>
                                @error('form.IDBudjet') <span class="text-danger small">Requis</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted font-weight-bold">Section</label>
                                <select wire:model.live="form.IDSection" class="form-control">
                                    <option value="">-- Choisir Section --</option>
                                    @foreach($sections as $s)
                                        <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option>
                                    @endforeach
                                </select>
                                @error('form.IDSection') <span class="text-danger small">Requis</span> @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Chapitre (OBJ1)</label>
                            <select wire:model.live="form.IDObj1" class="form-control font-weight-bold" {{ empty($listeObj1) ? 'disabled' : '' }}>
                                <option value="">
                                    {{ empty($listeObj1) ? '-- Choisir une Section d\'abord --' : '-- Sélectionner Chapitre --' }}
                                </option>
                                @foreach($listeObj1 as $o1)
                                    <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option>
                                @endforeach
                            </select>
                            @error('form.IDObj1') <span class="text-danger small">Requis</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Article (OBJ2)</label>
                            <select wire:model="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner Article --</option>
                                @foreach($listeObj2 as $o2)
                                    <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <label>Libellé</label>
                                <input type="text" wire:model="form.designation" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="text-success font-weight-bold">Montant (DA)</label>
                                <input type="number" step="0.01" wire:model="form.Mont_operation" class="form-control form-control-lg border-success text-right font-weight-bold">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Annuler</button>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>