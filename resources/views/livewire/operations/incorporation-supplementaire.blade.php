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
    
    // Type de budget supplémentaire
    public $typeBudgetSupplementaire = 'supplementaire'; // ou 'rectificatif', 'virement'
    
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
        'type_budget' => 'supplementaire', // supplementaire, rectificatif, virement
        'designation' => 'Budget Supplémentaire', 
        'Mont_operation' => 0,
        'EXERCICE' => '',
        'numero_decision' => '', // Numéro de décision administrative
        'date_decision' => '', // Date de la décision
        'source_financement' => '', // Origine des fonds
        'observations' => '',
    ];

    // Stats
    public $totalPrimitif = 0;
    public $totalSupplementaire = 0;
    public $totalGlobal = 0;

    public function mount()
    {
        $this->budgets = BdgBudget::where('Archive', 0)->orderByDesc('EXERCICE')->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->listeObj1 = [];
        $this->form['EXERCICE'] = date('Y');
        $this->form['date_decision'] = date('Y-m-d');
        
        $this->calculerStats();
    }

    public function calculerStats()
    {
        $exercice = $this->form['EXERCICE'] ?: date('Y');
        
        // Budget primitif
        $this->totalPrimitif = BdgOperationBudg::where('Type_operation', 1)
            ->where('EXERCICE', $exercice)
            ->where('designation', 'LIKE', '%Primitif%')
            ->sum('Mont_operation');
        
        // Budgets supplémentaires
        $this->totalSupplementaire = BdgOperationBudg::where('Type_operation', 1)
            ->where('EXERCICE', $exercice)
            ->where('designation', 'NOT LIKE', '%Primitif%')
            ->sum('Mont_operation');
        
        $this->totalGlobal = $this->totalPrimitif + $this->totalSupplementaire;
    }

    public function updatedFormIDBudjet($value)
    {
        $b = $this->budgets->find($value);
        if($b) $this->form['EXERCICE'] = $b->EXERCICE;
        $this->calculerStats();
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

    public function updatedFormTypeBudget($value)
    {
        // Mise à jour automatique du libellé selon le type
        $labels = [
            'supplementaire' => 'Budget Supplémentaire',
            'rectificatif' => 'Budget Rectificatif',
            'virement' => 'Virement de Crédits',
            'report' => 'Report de Crédits',
        ];
        
        $this->form['designation'] = $labels[$value] ?? 'Budget Supplémentaire';
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->form['Mont_operation'] = 0;
        $this->form['type_budget'] = 'supplementaire';
        $this->form['designation'] = 'Budget Supplémentaire';
        $this->form['date_decision'] = date('Y-m-d');
        $this->form['numero_decision'] = '';
        $this->form['source_financement'] = '';
        $this->form['observations'] = '';
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
            'form.type_budget' => 'required|in:supplementaire,rectificatif,virement,report',
            'form.numero_decision' => 'required|string|max:50',
            'form.date_decision' => 'required|date',
            'form.source_financement' => 'required|string|max:255',
        ], [
            'form.numero_decision.required' => 'Le numéro de décision est obligatoire',
            'form.date_decision.required' => 'La date de décision est obligatoire',
            'form.source_financement.required' => 'La source de financement est obligatoire',
        ]);

        // Création de l'opération
        BdgOperationBudg::create([
            'Num_operation' => $this->genererNumeroOperation(),
            'designation' => $this->form['designation'] . ' - ' . $this->form['numero_decision'],
            'Mont_operation' => $this->form['Mont_operation'],
            'Type_operation' => 1, // Incorporation
            'type_incorp' => $this->getTypeIncorpCode($this->form['type_budget']),
            'EXERCICE' => $this->form['EXERCICE'],
            'IDBudjet' => $this->form['IDBudjet'],
            'IDSection' => $this->form['IDSection'],
            'IDObj1' => $this->form['IDObj1'],
            'IDObj2' => $this->form['IDObj2'] ?: 0,
            'Observations' => json_encode([
                'numero_decision' => $this->form['numero_decision'],
                'date_decision' => $this->form['date_decision'],
                'source_financement' => $this->form['source_financement'],
                'observations' => $this->form['observations'],
            ]),
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]);

        $this->showModal = false;
        $this->calculerStats();
        session()->flash('success', 'Budget supplémentaire incorporé avec succès !');
    }

    protected function genererNumeroOperation()
    {
        $prefix = match($this->form['type_budget']) {
            'supplementaire' => 'BS',
            'rectificatif' => 'BR',
            'virement' => 'VC',
            'report' => 'RC',
            default => 'BS',
        };
        
        $year = substr($this->form['EXERCICE'], -2);
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $year . $random;
    }

    protected function getTypeIncorpCode($type)
    {
        return match($type) {
            'supplementaire' => 2,
            'rectificatif' => 3,
            'virement' => 4,
            'report' => 5,
            default => 1,
        };
    }

    public function delete($id)
    {
        BdgOperationBudg::findOrFail($id)->delete();
        $this->calculerStats();
        session()->flash('success', 'Opération supprimée.');
    }

    public function with()
    {
        $exercice = request()->get('exercice', date('Y'));
        
        return [
            'operations' => BdgOperationBudg::with(['budget', 'section', 'obj1', 'obj2'])
                ->where('Type_operation', 1)
                ->where('designation', 'NOT LIKE', '%Primitif%')
                ->where('EXERCICE', $exercice)
                ->orderByDesc('Creer_le')
                ->paginate(15)
        ];
    }
}; ?>

<div>
    {{-- Statistiques en haut --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Budget Primitif</span>
                    <span class="info-box-number">{{ number_format($totalPrimitif, 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-plus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Budgets Supplémentaires</span>
                    <span class="info-box-number">{{ number_format($totalSupplementaire, 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Budget Total</span>
                    <span class="info-box-number">{{ number_format($totalGlobal, 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton et Entête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-plus-square text-warning mr-2"></i>
            Incorporation Budget Supplémentaire
        </h4>
        <button wire:click="openModal()" class="btn btn-warning shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Nouveau Budget Supplémentaire
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tableau --}}
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Historique des Budgets Supplémentaires</h3>
            <div class="card-tools">
                <span class="badge badge-warning">{{ $operations->total() }} opérations</span>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>N° Opération</th>
                        <th>Type</th>
                        <th>Budget</th>
                        <th>Section</th>
                        <th>Ligne Budgétaire</th>
                        <th>Infos Décision</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                    <tr>
                        <td>
                            <span class="badge badge-dark">{{ $op->Num_operation }}</span>
                        </td>
                        <td>
                            @php
                                $typeBadge = 'secondary';
                                $typeIcon = 'fa-question';
                                if(str_contains($op->designation, 'Supplémentaire')) {
                                    $typeBadge = 'warning';
                                    $typeIcon = 'fa-plus-circle';
                                } elseif(str_contains($op->designation, 'Rectificatif')) {
                                    $typeBadge = 'info';
                                    $typeIcon = 'fa-edit';
                                } elseif(str_contains($op->designation, 'Virement')) {
                                    $typeBadge = 'primary';
                                    $typeIcon = 'fa-exchange-alt';
                                } elseif(str_contains($op->designation, 'Report')) {
                                    $typeBadge = 'success';
                                    $typeIcon = 'fa-arrow-right';
                                }
                            @endphp
                            <span class="badge badge-{{ $typeBadge }}">
                                <i class="fas {{ $typeIcon }}"></i>
                                {{ explode(' ', $op->designation)[0] }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-primary">{{ $op->budget->designation ?? '?' }}</span>
                        </td>
                        <td class="small font-weight-bold">
                            {{ $op->section->Num_section ?? '' }} - {{ Str::limit($op->section->NOM_section ?? '', 15) }}
                        </td>
                        <td>
                            <div class="text-bold text-dark">
                                {{ $op->obj1->Num ?? '' }} - {{ Str::limit($op->obj1->designation ?? '', 30) }}
                            </div>
                            @if($op->IDObj2)
                                <div class="text-muted small ml-3">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> 
                                    {{ $op->obj2->Num ?? '' }} {{ Str::limit($op->obj2->designation ?? '', 25) }}
                                </div>
                            @endif
                        </td>
                        <td class="small">
                            @php
                                $obs = json_decode($op->Observations, true);
                            @endphp
                            @if($obs)
                                <div><strong>Décision:</strong> {{ $obs['numero_decision'] ?? 'N/A' }}</div>
                                <div class="text-muted">{{ \Carbon\Carbon::parse($obs['date_decision'] ?? now())->format('d/m/Y') }}</div>
                                <div class="text-info"><i class="fas fa-money-bill-wave"></i> {{ Str::limit($obs['source_financement'] ?? '', 20) }}</div>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-warning">
                            {{ number_format($op->Mont_operation, 2, ',', ' ') }} DA
                        </td>
                        <td class="text-right">
                            <div class="btn-group btn-group-sm">
                                <button 
                                    class="btn btn-outline-info" 
                                    title="Détails"
                                    data-toggle="tooltip">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button 
                                    wire:click="delete({{ $op->IDOperation_Budg }})" 
                                    class="btn btn-outline-danger"
                                    title="Supprimer"
                                    data-toggle="tooltip"
                                    onclick="return confirm('Supprimer ce budget supplémentaire ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                            Aucun budget supplémentaire enregistré pour cet exercice
                        </td>
                    </tr>
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
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Nouveau Budget Supplémentaire
                    </h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        {{-- TYPE DE BUDGET --}}
                        <div class="alert alert-info mb-3">
                            <h6><i class="fas fa-info-circle mr-2"></i>Type de Budget</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type1" wire:model.live="form.type_budget" value="supplementaire" class="custom-control-input">
                                        <label class="custom-control-label" for="type1">
                                            <strong>Supplémentaire</strong><br>
                                            <small>Crédits additionnels</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type2" wire:model.live="form.type_budget" value="rectificatif" class="custom-control-input">
                                        <label class="custom-control-label" for="type2">
                                            <strong>Rectificatif</strong><br>
                                            <small>Modification budget</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type3" wire:model.live="form.type_budget" value="virement" class="custom-control-input">
                                        <label class="custom-control-label" for="type3">
                                            <strong>Virement</strong><br>
                                            <small>Transfert crédits</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type4" wire:model.live="form.type_budget" value="report" class="custom-control-input">
                                        <label class="custom-control-label" for="type4">
                                            <strong>Report</strong><br>
                                            <small>Crédits reportés</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INFORMATIONS ADMINISTRATIVES --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i>Informations Administratives</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">N° Décision <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.numero_decision" class="form-control" placeholder="Ex: 2025/BS/001">
                                        @error('form.numero_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Date Décision <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="form.date_decision" class="form-control">
                                        @error('form.date_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Source Financement <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.source_financement" class="form-control" placeholder="Ex: Subvention État">
                                        @error('form.source_financement') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- AFFECTATION BUDGÉTAIRE --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-sitemap mr-2"></i>Affectation Budgétaire</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Budget <span class="text-danger">*</span></label>
                                        <select wire:model.live="form.IDBudjet" class="form-control">
                                            <option value="">-- Choisir --</option>
                                            @foreach($budgets as $b)
                                                <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.IDBudjet') <span class="text-danger small">Requis</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Section <span class="text-danger">*</span></label>
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
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Chapitre (OBJ1) <span class="text-danger">*</span></label>
                                        <select wire:model.live="form.IDObj1" class="form-control" {{ empty($listeObj1) ? 'disabled' : '' }}>
                                            <option value="">
                                                {{ empty($listeObj1) ? '-- Choisir Section d\'abord --' : '-- Sélectionner Chapitre --' }}
                                            </option>
                                            @foreach($listeObj1 as $o1)
                                                <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.IDObj1') <span class="text-danger small">Requis</span> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Article (OBJ2)</label>
                                        <select wire:model="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                            <option value="">-- Sélectionner Article --</option>
                                            @foreach($listeObj2 as $o2)
                                                <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MONTANT ET OBSERVATIONS --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Montant et Observations</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Libellé <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.designation" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-warning font-weight-bold">Montant (DA) <span class="text-danger">*</span></label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model="form.Mont_operation" 
                                            class="form-control form-control-lg border-warning text-right font-weight-bold"
                                            placeholder="0.00">
                                        @error('form.Mont_operation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-3 mb-0">
                                    <label class="font-weight-bold">Observations</label>
                                    <textarea wire:model="form.observations" class="form-control" rows="3" placeholder="Notes complémentaires..."></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check mr-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
