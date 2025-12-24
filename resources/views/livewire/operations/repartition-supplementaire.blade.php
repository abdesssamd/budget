<?php

use Livewire\Volt\Component;
use App\Models\BdgOperationBudg;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use App\Models\BdgObj1;
use App\Models\BdgObj2;
use App\Models\BdgObj3;
use App\Models\BdgObj4;
use App\Models\BdgObj5;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    
    // Configuration
    public $maxLevel = 5;
    
    // Données
    public $budgets = [];
    public $sections = [];
    public $listeObj1 = [];
    public $listeObj2 = [];
    public $listeObj3 = [];
    public $listeObj4 = [];
    public $listeObj5 = [];

    // Budgets supplémentaires disponibles pour répartition
    public $budgetsSupplementairesDisponibles = [];
    public $budgetSourceSelectionne = null;
    public $montantDisponible = 0;

    public array $form = [
        'IDOperation_Source' => '', // Budget supplémentaire source
        'IDBudjet' => '',
        'IDSection' => '',
        'IDObj1' => '',
        'IDObj2' => '',
        'IDObj3' => '',
        'IDObj4' => '',
        'IDObj5' => '',
        'designation' => 'Répartition Budget Supplémentaire',
        'Mont_operation' => 0,
        'EXERCICE' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        $this->maxLevel = $params->nombre_niveau ?? 5;

        $this->budgets = BdgBudget::where('Archive', 0)->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->form['EXERCICE'] = date('Y');
        
        $this->chargerBudgetsSupplementaires();
    }

    public function chargerBudgetsSupplementaires()
    {
        $exercice = $this->form['EXERCICE'] ?: date('Y');
        
        // Récupérer tous les budgets supplémentaires
        $incorporations = BdgOperationBudg::with(['section', 'obj1', 'obj2'])
            ->where('Type_operation', 1)
            ->where('designation', 'NOT LIKE', '%Primitif%')
            ->where('EXERCICE', $exercice)
            ->get();

        $this->budgetsSupplementairesDisponibles = $incorporations->map(function($incorp) {
            // Calculer le montant déjà réparti
            $montantReparti = BdgOperationBudg::where('Type_operation', 2)
                ->where('IDbdg_rel_niveau', $incorp->IDOperation_Budg)
                ->sum('Mont_operation');
            
            $disponible = $incorp->Mont_operation - $montantReparti;
            
            return [
                'id' => $incorp->IDOperation_Budg,
                'numero' => $incorp->Num_operation,
                'designation' => $incorp->designation,
                'section' => $incorp->section->Num_section . ' - ' . $incorp->section->NOM_section,
                'ligne' => $incorp->obj1->Num . ' - ' . $incorp->obj1->designation,
                'montant_total' => $incorp->Mont_operation,
                'montant_reparti' => $montantReparti,
                'montant_disponible' => $disponible,
                'pourcent_reparti' => $incorp->Mont_operation > 0 ? ($montantReparti / $incorp->Mont_operation) * 100 : 0,
            ];
        })->filter(function($budget) {
            return $budget['montant_disponible'] > 0; // Seulement ceux avec solde
        });
    }

    public function updatedFormIDOperationSource($value)
    {
        $budget = collect($this->budgetsSupplementairesDisponibles)->firstWhere('id', $value);
        if ($budget) {
            $this->budgetSourceSelectionne = $budget;
            $this->montantDisponible = $budget['montant_disponible'];
        }
    }

    public function updatedFormIDBudjet($value)
    {
        $b = $this->budgets->find($value);
        if($b) $this->form['EXERCICE'] = $b->EXERCICE;
    }

    public function updatedFormIDSection($value)
    {
        $this->resetLevels(1);
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
    }

    public function updatedFormIDObj1($value)
    {
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) {
            $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj2($value)
    {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) {
            $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj3($value)
    {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) {
            $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj4($value)
    {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) {
            $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
        }
    }

    protected function resetLevels($fromLevel)
    {
        if ($fromLevel <= 1) $this->form['IDObj1'] = '';
        if ($fromLevel <= 2) { $this->form['IDObj2'] = ''; $this->listeObj2 = []; }
        if ($fromLevel <= 3) { $this->form['IDObj3'] = ''; $this->listeObj3 = []; }
        if ($fromLevel <= 4) { $this->form['IDObj4'] = ''; $this->listeObj4 = []; }
        if ($fromLevel <= 5) { $this->form['IDObj5'] = ''; $this->listeObj5 = []; }
    }

    public function openModal()
    {
        $this->chargerBudgetsSupplementaires();
        $this->resetValidation();
        $this->form['Mont_operation'] = 0;
        $this->form['designation'] = 'Répartition Budget Supplémentaire';
        $this->budgetSourceSelectionne = null;
        $this->montantDisponible = 0;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.IDOperation_Source' => 'required',
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required',
            'form.IDObj1' => 'required',
            'form.Mont_operation' => 'required|numeric|min:0.01',
            'form.designation' => 'required|string',
        ], [
            'form.IDOperation_Source.required' => 'Veuillez sélectionner un budget supplémentaire source',
        ]);

        // Vérifier que le montant ne dépasse pas le disponible
        if ($this->form['Mont_operation'] > $this->montantDisponible) {
            session()->flash('error', 'Le montant saisi dépasse le crédit disponible (' . number_format($this->montantDisponible, 2) . ' DA)');
            return;
        }

        // Déterminer le niveau le plus bas sélectionné
        $niveauCible = $this->determinerNiveauCible();

        DB::beginTransaction();
        try {
            // Créer la répartition
            BdgOperationBudg::create([
                'Num_operation' => 'REP-BS-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
                'designation' => $this->form['designation'],
                'Mont_operation' => $this->form['Mont_operation'],
                'Type_operation' => 2, // Répartition
                'EXERCICE' => $this->form['EXERCICE'],
                'IDBudjet' => $this->form['IDBudjet'],
                'IDSection' => $this->form['IDSection'],
                'IDObj1' => $this->form['IDObj1'],
                'IDObj2' => $this->form['IDObj2'] ?: 0,
                'IDObj3' => $this->form['IDObj3'] ?: 0,
                'IDObj4' => $this->form['IDObj4'] ?: 0,
                'IDObj5' => $this->form['IDObj5'] ?: 0,
                'IDbdg_rel_niveau' => $this->form['IDOperation_Source'], // Lien vers le budget supplémentaire source
                'Creer_le' => now(),
                'IDLogin' => auth()->id() ?? 0
            ]);

            DB::commit();
            $this->showModal = false;
            $this->chargerBudgetsSupplementaires();
            session()->flash('success', 'Répartition effectuée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la répartition : ' . $e->getMessage());
        }
    }

    protected function determinerNiveauCible()
    {
        if ($this->form['IDObj5']) return 5;
        if ($this->form['IDObj4']) return 4;
        if ($this->form['IDObj3']) return 3;
        if ($this->form['IDObj2']) return 2;
        return 1;
    }

    public function delete($id)
    {
        BdgOperationBudg::findOrFail($id)->delete();
        $this->chargerBudgetsSupplementaires();
        session()->flash('success', 'Répartition supprimée.');
    }

    public function with()
    {
        $exercice = request()->get('exercice', date('Y'));
        
        return [
            'repartitions' => BdgOperationBudg::with([
                'budget', 'section', 'obj1', 'obj2', 'obj3', 'obj4', 'obj5',
                'operationSource' => function($query) {
                    $query->select('IDOperation_Budg', 'Num_operation', 'designation', 'Mont_operation');
                }
            ])
                ->where('Type_operation', 2)
                ->whereHas('operationSource', function($query) {
                    $query->where('designation', 'NOT LIKE', '%Primitif%');
                })
                ->where('EXERCICE', $exercice)
                ->orderByDesc('Creer_le')
                ->paginate(15)
        ];
    }
}; ?>

<div>
    {{-- Budgets supplémentaires disponibles --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-wallet mr-2"></i>
                        Budgets Supplémentaires Disponibles pour Répartition
                    </h3>
                </div>
                <div class="card-body">
                    @if(count($budgetsSupplementairesDisponibles) > 0)
                        <div class="row">
                            @foreach($budgetsSupplementairesDisponibles as $budget)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">
                                                <i class="fas fa-hashtag"></i> {{ $budget['numero'] }}
                                            </h6>
                                            <p class="card-text small mb-2">{{ Str::limit($budget['designation'], 40) }}</p>
                                            <p class="small text-muted mb-1">
                                                <i class="fas fa-sitemap"></i> {{ $budget['section'] }}<br>
                                                <i class="fas fa-layer-group"></i> {{ $budget['ligne'] }}
                                            </p>
                                            
                                            <hr class="my-2">
                                            
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small">Total:</span>
                                                <span class="font-weight-bold">{{ number_format($budget['montant_total'], 2, ',', ' ') }} DA</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small">Réparti:</span>
                                                <span class="text-danger">{{ number_format($budget['montant_reparti'], 2, ',', ' ') }} DA</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="small font-weight-bold">Disponible:</span>
                                                <span class="text-success font-weight-bold">{{ number_format($budget['montant_disponible'], 2, ',', ' ') }} DA</span>
                                            </div>
                                            
                                            <div class="progress mt-2" style="height: 10px;">
                                                <div 
                                                    class="progress-bar bg-danger" 
                                                    role="progressbar" 
                                                    style="width: {{ $budget['pourcent_reparti'] }}%"
                                                    title="{{ number_format($budget['pourcent_reparti'], 1) }}% réparti">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($budget['pourcent_reparti'], 1) }}% réparti</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Aucun budget supplémentaire disponible pour répartition</p>
                            <p class="small">Veuillez d'abord incorporer des budgets supplémentaires</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton et Entête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-share-alt text-warning mr-2"></i>
            Répartition des Budgets Supplémentaires
        </h4>
        <button 
            wire:click="openModal()" 
            class="btn btn-warning shadow-sm"
            @if(count($budgetsSupplementairesDisponibles) == 0) disabled @endif>
            <i class="fas fa-plus-circle mr-2"></i>Nouvelle Répartition
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="icon fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tableau des répartitions --}}
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Historique des Répartitions</h3>
            <div class="card-tools">
                <span class="badge badge-warning">{{ $repartitions->total() }} répartitions</span>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Budget Source</th>
                        <th>Section</th>
                        <th>Ligne Budgétaire Cible</th>
                        <th class="text-right">Montant</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repartitions as $rep)
                    <tr>
                        <td>
                            <span class="badge badge-dark">{{ $rep->operationSource->Num_operation ?? 'N/A' }}</span>
                            <div class="small text-muted">{{ Str::limit($rep->operationSource->designation ?? '', 30) }}</div>
                        </td>
                        <td class="small">
                            {{ $rep->section->Num_section ?? '' }} - {{ Str::limit($rep->section->NOM_section ?? '', 20) }}
                        </td>
                        <td>
                            <div class="small">
                                @if($rep->IDObj1)
                                    <div class="font-weight-bold">
                                        <i class="fas fa-angle-right text-muted"></i> 
                                        {{ $rep->obj1->Num ?? '' }} - {{ Str::limit($rep->obj1->designation ?? '', 25) }}
                                    </div>
                                @endif
                                @if($rep->IDObj2)
                                    <div class="ml-3">
                                        <i class="fas fa-angle-right text-muted"></i> 
                                        {{ $rep->obj2->Num ?? '' }} - {{ Str::limit($rep->obj2->designation ?? '', 20) }}
                                    </div>
                                @endif
                                @if($rep->IDObj3)
                                    <div class="ml-4">
                                        <i class="fas fa-angle-right text-muted"></i> 
                                        {{ $rep->obj3->Num ?? '' }} - {{ Str::limit($rep->obj3->designation ?? '', 18) }}
                                    </div>
                                @endif
                                @if($rep->IDObj4)
                                    <div class="ml-5">
                                        <i class="fas fa-angle-right text-muted"></i> 
                                        {{ $rep->obj4->Num ?? '' }} - {{ Str::limit($rep->obj4->designation ?? '', 15) }}
                                    </div>
                                @endif
                                @if($rep->IDObj5)
                                    <div class="ml-5">
                                        <i class="fas fa-angle-right text-muted"></i> 
                                        {{ $rep->obj5->Num ?? '' }} - {{ Str::limit($rep->obj5->designation ?? '', 12) }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="text-right font-weight-bold text-warning">
                            {{ number_format($rep->Mont_operation, 2, ',', ' ') }} DA
                        </td>
                        <td class="small text-muted">
                            {{ \Carbon\Carbon::parse($rep->Creer_le)->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-right">
                            <button 
                                wire:click="delete({{ $rep->IDOperation_Budg }})" 
                                class="btn btn-sm btn-outline-danger"
                                title="Supprimer"
                                onclick="return confirm('Supprimer cette répartition ?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                            Aucune répartition de budget supplémentaire
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <div class="float-right">{{ $repartitions->links() }}</div>
        </div>
    </div>

    {{-- MODAL DE RÉPARTITION --}}
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-share-alt mr-2"></i>
                        Nouvelle Répartition de Budget Supplémentaire
                    </h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        {{-- SÉLECTION DU BUDGET SOURCE --}}
                        <div class="alert alert-warning mb-4">
                            <h6><i class="fas fa-hand-point-right mr-2"></i>1. Sélectionner le Budget Supplémentaire Source</h6>
                            <select wire:model.live="form.IDOperation_Source" class="form-control form-control-lg">
                                <option value="">-- Choisir un budget supplémentaire --</option>
                                @foreach($budgetsSupplementairesDisponibles as $budget)
                                    <option value="{{ $budget['id'] }}">
                                        {{ $budget['numero'] }} - {{ $budget['designation'] }} 
                                        (Disponible: {{ number_format($budget['montant_disponible'], 2, ',', ' ') }} DA)
                                    </option>
                                @endforeach
                            </select>
                            @error('form.IDOperation_Source') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                            
                            @if($budgetSourceSelectionne)
                                <div class="mt-3 p-3 bg-white rounded">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Montant total:</strong> {{ number_format($budgetSourceSelectionne['montant_total'], 2, ',', ' ') }} DA</p>
                                            <p class="mb-1"><strong>Déjà réparti:</strong> <span class="text-danger">{{ number_format($budgetSourceSelectionne['montant_reparti'], 2, ',', ' ') }} DA</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Disponible:</strong> <span class="text-success font-weight-bold h5">{{ number_format($montantDisponible, 2, ',', ' ') }} DA</span></p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- AFFECTATION BUDGÉTAIRE --}}
                        @if($budgetSourceSelectionne)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-crosshairs mr-2"></i>2. Définir la Ligne Budgétaire Cible</h6>
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

                                {{-- Niveaux hiérarchiques --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Chapitre <span class="text-danger">*</span></label>
                                        <select wire:model.live="form.IDObj1" class="form-control" {{ empty($listeObj1) ? 'disabled' : '' }}>
                                            <option value="">{{ empty($listeObj1) ? '-- Section d\'abord --' : '-- Sélectionner --' }}</option>
                                            @foreach($listeObj1 as $o)
                                                <option value="{{ $o->IDObj1 }}">{{ $o->Num }} - {{ $o->designation }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.IDObj1') <span class="text-danger small">Requis</span> @enderror
                                    </div>

                                    @if($maxLevel >= 2)
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Article</label>
                                        <select wire:model.live="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($listeObj2 as $o)
                                                <option value="{{ $o->IDObj2 }}">{{ $o->Num }} - {{ $o->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>

                                @if($maxLevel >= 3)
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Sous-Article</label>
                                        <select wire:model.live="form.IDObj3" class="form-control" {{ empty($listeObj3) ? 'disabled' : '' }}>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($listeObj3 as $o)
                                                <option value="{{ $o->IDObj3 }}">{{ $o->Num }} - {{ $o->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($maxLevel >= 4)
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Rubrique</label>
                                        <select wire:model.live="form.IDObj4" class="form-control" {{ empty($listeObj4) ? 'disabled' : '' }}>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($listeObj4 as $o)
                                                <option value="{{ $o->IDObj4 }}">{{ $o->Num }} - {{ $o->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                                @endif

                                @if($maxLevel >= 5)
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Sous-Rubrique</label>
                                        <select wire:model="form.IDObj5" class="form-control" {{ empty($listeObj5) ? 'disabled' : '' }}>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach($listeObj5 as $o)
                                                <option value="{{ $o->IDObj5 }}">{{ $o->Num }} - {{ $o->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- MONTANT --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>3. Montant à Répartir</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="font-weight-bold">Libellé</label>
                                        <input type="text" wire:model="form.designation" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-warning font-weight-bold">Montant (DA) <span class="text-danger">*</span></label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model="form.Mont_operation" 
                                            max="{{ $montantDisponible }}"
                                            class="form-control form-control-lg border-warning text-right font-weight-bold"
                                            placeholder="0.00">
                                        <small class="text-muted">Max: {{ number_format($montantDisponible, 2, ',', ' ') }} DA</small>
                                        @error('form.Mont_operation') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-warning" @if(!$budgetSourceSelectionne) disabled @endif>
                            <i class="fas fa-check mr-1"></i> Répartir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
