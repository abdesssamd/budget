<?php

use Livewire\Volt\Component;
use App\Models\BdgOperationBudg;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use App\Models\BdgObj1;
use App\Models\BdgObj2;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    
    // Option pour impacter l'enveloppe
    public $updateEnvelope = true; 

    // Listes dynamiques
    public $budgets = [];
    public $sections = [];
    public $listeObj1 = [];
    public $listeObj2 = [];

    public array $form = [
        'IDBudjet' => '',
        'IDSection' => '',
        'IDObj1' => '', 
        'IDObj2' => '', 
        'designation' => 'Budget Primitif Global', 
        'Mont_operation' => 0,
        'EXERCICE' => '',
        'numero_decision' => '',
        'date_decision' => '',
        'source_financement' => '',
        'observations' => '',
    ];

    public function mount()
    {
        $this->budgets = BdgBudget::where('Archive', 0)->orderByDesc('EXERCICE')->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->listeObj1 = [];
        $this->form['EXERCICE'] = date('Y');
        $this->form['date_decision'] = date('Y-m-d');
    }

    public function updatedFormIDBudjet($value)
    {
        $b = $this->budgets->find($value);
        if($b) $this->form['EXERCICE'] = $b->EXERCICE;
    }

    public function updatedFormIDSection($value)
    {
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
        $this->form['numero_decision'] = '';
        $this->form['date_decision'] = date('Y-m-d');
        $this->form['source_financement'] = 'Budget Etat';
        $this->form['observations'] = '';
        
        // Par défaut on propose d'augmenter l'enveloppe pour l'alimentation
        $this->updateEnvelope = true;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required', 
            'form.IDObj1' => 'nullable',    
            'form.Mont_operation' => 'required|numeric|min:0.01',
            'form.designation' => 'required|string',
            'form.numero_decision' => 'required|string|max:50',
            'form.date_decision' => 'required|date',
            'form.source_financement' => 'required|string|max:255',
        ]);

        $obsJson = json_encode([
            'numero_decision' => $this->form['numero_decision'],
            'date_decision' => $this->form['date_decision'],
            'source_financement' => $this->form['source_financement'],
            'observations' => $this->form['observations']
        ]);

        // Utilisation d'une transaction pour garantir la cohérence des données
        DB::transaction(function () use ($obsJson) {
            // 1. Création de l'opération d'incorporation
            BdgOperationBudg::create([
                'Num_operation' => (string) rand(10000,99999),
                'designation' => $this->form['designation'],
                'Mont_operation' => $this->form['Mont_operation'],
                'Type_operation' => 1, // Incorporation / Alimentation
                'EXERCICE' => $this->form['EXERCICE'],
                'IDBudjet' => $this->form['IDBudjet'],
                'IDSection' => $this->form['IDSection'],
                'IDObj1' => $this->form['IDObj1'] ?: 0, 
                'IDObj2' => $this->form['IDObj2'] ?: 0,
                'Observations' => $obsJson,
                'Creer_le' => now(),
                'IDLogin' => auth()->id() ?? 0
            ]);

            // 2. Mise à jour de l'enveloppe budgétaire (Si l'option est cochée)
            if ($this->updateEnvelope) {
                $budget = BdgBudget::findOrFail($this->form['IDBudjet']);
                
                // On augmente le Primitif, le Global et le Restant
                $budget->increment('Montant_Primitif', $this->form['Mont_operation']);
                $budget->increment('Montant_Global', $this->form['Mont_operation']);
                $budget->increment('Montant_Restant', $this->form['Mont_operation']);
            }
        });

        $this->showModal = false;
        session()->flash('success', 'Alimentation effectuée et budget mis à jour avec succès !');
    }

    public function delete($id)
    {
        // Pour la suppression, on retire simplement la ligne pour l'instant
        // ATTENTION : Si on veut annuler l'impact sur le budget, il faudrait aussi décrémenter
        // Mais cela dépend si l'argent a déjà été consommé.
        
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold"><i class="fas fa-coins text-success mr-2"></i>Alimentation Budgétaire (Incorporation)</h4>
        <button wire:click="openModal()" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Nouvelle Alimentation
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Historique des alimentations</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Décision / Date</th>
                        <th>Budget / Section</th>
                        <th>Affectation</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                    @php
                        $obs = json_decode($op->Observations, true);
                        $decision = $obs['numero_decision'] ?? '-';
                        $dateDec = $obs['date_decision'] ?? $op->Creer_le;
                    @endphp
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $decision }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($dateDec)->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $op->budget->designation ?? '?' }}</span><br>
                            <small class="font-weight-bold text-muted">
                                {{ $op->section->Num_section ?? '' }} - {{ Str::limit($op->section->NOM_section ?? '', 15) }}
                            </small>
                        </td>
                        <td>
                            @if($op->IDObj1 && $op->obj1)
                                <div class="text-bold text-dark">{{ $op->obj1->Num }} - {{ $op->obj1->designation }}</div>
                                @if($op->IDObj2 && $op->obj2)
                                    <div class="text-muted small ml-3"><i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj2->Num }} {{ $op->obj2->designation }}</div>
                                @endif
                            @else
                                <span class="badge badge-warning text-dark">Non affecté (Global Section)</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            {{ number_format($op->Mont_operation, 2, ',', ' ') }} DA
                        </td>
                        <td class="text-right">
                            <button wire:click="delete({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-outline-danger" onclick="confirm('Supprimer cette ligne ?') || event.stopImmediatePropagation()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucune alimentation trouvée.</td></tr>
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
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-coins mr-2"></i>Alimentation Budgétaire</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        {{-- INFORMATIONS ADMINISTRATIVES --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i>Informations Administratives</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">N° Décision <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.numero_decision" class="form-control" placeholder="Ex: 2025/INC/001">
                                        @error('form.numero_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Date Décision <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="form.date_decision" class="form-control">
                                        @error('form.date_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Source Financement <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.source_financement" class="form-control" placeholder="Ex: Budget État">
                                        @error('form.source_financement') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- AFFECTATION ET MONTANT --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-sitemap mr-2"></i>Affectation Budgétaire</h6>
                            </div>
                            <div class="card-body">
                                <div class="row bg-light p-2 rounded mb-3 border">
                                    <div class="col-md-6">
                                        <label class="small text-muted font-weight-bold">Budget Source</label>
                                        <select wire:model.live="form.IDBudjet" class="form-control">
                                            <option value="">-- Choisir --</option>
                                            @foreach($budgets as $b)
                                                <option value="{{ $b->IDBudjet }}">
                                                    {{ $b->EXERCICE }} - {{ $b->designation }}
                                                    (Primitif actuel: {{ number_format($b->Montant_Primitif, 2) }} DA)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('form.IDBudjet') <span class="text-danger small">Requis</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted font-weight-bold">Section Bénéficiaire</label>
                                        <select wire:model.live="form.IDSection" class="form-control">
                                            <option value="">-- Choisir Section --</option>
                                            @foreach($sections as $s)
                                                <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.IDSection') <span class="text-danger small">Requis</span> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group border-left border-warning pl-3">
                                            <label class="text-warning font-weight-bold">Détail (Optionnel pour incorporation globale)</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <select wire:model.live="form.IDObj1" class="form-control" {{ empty($listeObj1) ? 'disabled' : '' }}>
                                                        <option value="">-- Global (Tous les chapitres) --</option>
                                                        @foreach($listeObj1 as $o1)
                                                            <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <select wire:model="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                                        <option value="">-- Global (Tous les articles) --</option>
                                                        @foreach($listeObj2 as $o2)
                                                            <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <small class="text-muted">Laisser vide pour affecter le montant à la section entière.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-8">
                                        <label>Libellé Opération</label>
                                        <input type="text" wire:model="form.designation" class="form-control">
                                        @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-success font-weight-bold">Montant (DA)</label>
                                        <input type="number" step="0.01" wire:model="form.Mont_operation" class="form-control form-control-lg border-success text-right font-weight-bold">
                                        @error('form.Mont_operation') <span class="text-danger small">Requis</span> @enderror
                                    </div>
                                </div>
                                
                                {{-- OPTION DE MISE A JOUR DU BUDGET --}}
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="updateEnvelopeSwitch" wire:model="updateEnvelope">
                                            <label class="custom-control-label font-weight-bold text-primary" for="updateEnvelopeSwitch">
                                                Augmenter automatiquement l'Enveloppe Budgétaire (Montant Primitif) ?
                                            </label>
                                            <div class="text-muted small">
                                                Si coché, ce montant sera ajouté au <strong>Montant Primitif</strong> et au <strong>Montant Global</strong> du budget sélectionné.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Observations</label>
                                    <textarea wire:model="form.observations" class="form-control" rows="2" placeholder="Notes..."></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>