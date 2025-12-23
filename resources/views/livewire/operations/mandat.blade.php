<?php

use Livewire\Volt\Component;
use App\Models\BdgMandat;
use App\Models\BdgDetailOpBud;
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
    public $maxLevel = 1; 

    // --- FILTRES LISTE PRINCIPALE ---
    public $filterSection = '';
    public $filterObj1 = '';
    public $filterSearch = '';
    
    public $listSections = [];
    public $listObj1 = [];

    // --- FORMULAIRE ---
    public $budgets = [];
    public $sections = []; // Modal
    
    // Listes en cascade Modal
    public $listeObj1 = []; 
    public $listeObj2 = [];
    public $listeObj3 = [];
    public $listeObj4 = [];
    public $listeObj5 = [];
    
    // Engagements pour la sélection
    public $availableEngagements = [];
    public $selectedEngagements = []; 
    public $amountsToPay = []; 

    public array $form = [
        'IDBudjet' => '', 'IDSection' => '', 
        'IDObj1' => '', 'IDObj2' => '', 'IDObj3' => '', 'IDObj4' => '', 'IDObj5' => '',
        'Num_mandat' => '', 
        'date_mandate' => '',
        'designation' => '',
        'NumFournisseur' => '', 
        'EXERCICE' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        $this->maxLevel = $params->nombre_niveau ?? 1;

        // Chargement initial
        $this->budgets = BdgBudget::where('Archive', 0)->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->listSections = $this->sections; // Pour le filtre
        
        $this->form['EXERCICE'] = date('Y');
        $this->form['date_mandate'] = date('Y-m-d');
    }

    // --- FILTRES TABLEAU ---
    public function updatedFilterSection($value) {
        $this->filterObj1 = '';
        $this->listObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->resetPage();
    }

    // --- LOGIQUE MODAL / ENGAGEMENTS ---
    public function loadEngagements()
    {
        $this->availableEngagements = [];
        
        if ($this->form['IDBudjet'] && $this->form['IDSection'] && $this->form['IDObj1']) {
            
            $query = BdgOperationBudg::where('Type_operation', 3)
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->where('IDSection', $this->form['IDSection'])
                ->where('IDObj1', $this->form['IDObj1']);

            // Filtres optionnels (Correction ici pour inclure tous les niveaux)
            if (!empty($this->form['IDObj2'])) $query->where('IDObj2', $this->form['IDObj2']);
            if (!empty($this->form['IDObj3'])) $query->where('IDObj3', $this->form['IDObj3']);
            if (!empty($this->form['IDObj4'])) $query->where('IDObj4', $this->form['IDObj4']);
            if (!empty($this->form['IDObj5'])) $query->where('IDObj5', $this->form['IDObj5']);

            $engagements = $query->orderByDesc('Creer_le')->get();

            foreach ($engagements as $eng) {
                // Calcul du reste à payer
                $dejaMandate = BdgDetailOpBud::where('IDOperation_Budg', $eng->IDOperation_Budg)->sum('Montant');
                $solde = $eng->Mont_operation - $dejaMandate;

                // On affiche seulement s'il reste quelque chose à payer
                if ($solde > 0.01) {
                    $eng->solde_disponible = $solde;
                    $eng->deja_mandate = $dejaMandate;
                    $this->availableEngagements[] = $eng;
                    
                    // Par défaut, on propose de payer tout le solde
                    if (!isset($this->amountsToPay[$eng->IDOperation_Budg])) {
                        $this->amountsToPay[$eng->IDOperation_Budg] = $solde;
                    }
                }
            }
        }
    }

    public function updatedFormIDBudjet($value) { $this->loadEngagements(); }
    
    // --- CASCADE COMPLETE ---
    public function updatedFormIDSection($value) { 
        $this->resetLevels(1);
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->loadEngagements(); 
    }
    public function updatedFormIDObj1($value) { 
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
        $this->loadEngagements(); 
    }
    public function updatedFormIDObj2($value) {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
        $this->loadEngagements();
    }
    public function updatedFormIDObj3($value) {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
        $this->loadEngagements();
    }
    public function updatedFormIDObj4($value) {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
        $this->loadEngagements();
    }

    private function resetLevels($fromLevel) {
        if($fromLevel <= 1) { $this->form['IDObj1'] = ''; $this->listeObj2 = []; }
        if($fromLevel <= 2) { $this->form['IDObj2'] = ''; $this->listeObj3 = []; }
        if($fromLevel <= 3) { $this->form['IDObj3'] = ''; $this->listeObj4 = []; }
        if($fromLevel <= 4) { $this->form['IDObj4'] = ''; $this->listeObj5 = []; }
        if($fromLevel <= 5) { $this->form['IDObj5'] = ''; }
        
        $this->availableEngagements = []; 
        $this->selectedEngagements = [];
    }

    public function openModal() {
        $this->resetValidation();
        // Reset complet de toutes les listes
        $this->reset('form', 'selectedEngagements', 'availableEngagements', 'amountsToPay', 'listeObj1', 'listeObj2', 'listeObj3', 'listeObj4', 'listeObj5');
        $this->form['date_mandate'] = date('Y-m-d');
        $this->form['EXERCICE'] = date('Y');
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required',
            'form.IDObj1' => 'required',
            'form.Num_mandat' => 'required|string',
            'form.date_mandate' => 'required|date',
            'form.designation' => 'required|string',
            'selectedEngagements' => 'required|array|min:1', 
        ]);

        // Validation des montants saisis
        foreach ($this->selectedEngagements as $opId) {
            $eng = BdgOperationBudg::find($opId);
            if (!$eng) continue;

            $dejaMandate = BdgDetailOpBud::where('IDOperation_Budg', $opId)->sum('Montant');
            $soldeReel = $eng->Mont_operation - $dejaMandate;
            $montantSaisi = (float) ($this->amountsToPay[$opId] ?? 0);

            if ($montantSaisi <= 0) {
                $this->addError("amountsToPay.$opId", "Montant incorrect"); return;
            }
            if ($montantSaisi > ($soldeReel + 0.01)) {
                $this->addError("amountsToPay.$opId", "Dépasse le solde ($soldeReel)"); return;
            }
        }

        // Création Mandat
        $mandat = BdgMandat::create([
            'Num_mandat' => $this->form['Num_mandat'],
            'date_mandate' => $this->form['date_mandate'],
            'designation' => $this->form['designation'],
            'NumFournisseur' => 0, 
            'EXERCICE' => $this->form['EXERCICE'],
            'IDBudjet' => $this->form['IDBudjet'],
            'IDSection' => $this->form['IDSection'],
            'IDObj1' => $this->form['IDObj1'],
            'IDObj2' => $this->form['IDObj2'] ?: 0,
            'IDObj3' => $this->form['IDObj3'] ?: 0,
            'IDObj4' => $this->form['IDObj4'] ?: 0,
            'IDObj5' => $this->form['IDObj5'] ?: 0,
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]);

        // Création Détails
        foreach ($this->selectedEngagements as $opId) {
            $montant = (float) $this->amountsToPay[$opId];
            $engBase = BdgOperationBudg::find($opId);
            
            if ($engBase) {
                BdgDetailOpBud::create([
                    'IDMandat' => $mandat->IDMandat,
                    'IDOperation_Budg' => $opId,
                    'Montant' => $montant,
                    'designation' => $engBase->designation,
                    'Creer_le' => now(),
                    'IDLogin' => auth()->id() ?? 0
                ]);
            }
        }

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id)
    {
        $mandat = BdgMandat::findOrFail($id);
        BdgDetailOpBud::where('IDMandat', $id)->delete();
        $mandat->delete();
        session()->flash('success', __('crud.item_deleted'));
    }

    public function with()
    {
        $query = BdgMandat::with(['budget', 'section', 'obj1', 'obj2', 'obj3', 'details'])
            ->orderByDesc('date_mandate');

        if ($this->filterSection) $query->where('IDSection', $this->filterSection);
        if ($this->filterObj1) $query->where('IDObj1', $this->filterObj1);
        if ($this->filterSearch) {
            $query->where(function($q) {
                $q->where('designation', 'like', '%'.$this->filterSearch.'%')
                  ->orWhere('Num_mandat', 'like', '%'.$this->filterSearch.'%');
            });
        }

        return [
            'mandats' => $query->paginate(10)
        ];
    }
}; ?>

<div>
    {{-- Helpers RTL --}}
    @php
        $isRtl = app()->getLocale() == 'ar';
        $alignText = $isRtl ? 'text-right' : 'text-left';
        $margin = $isRtl ? 'ml-2' : 'mr-2';
        $floatRight = $isRtl ? 'float-left' : 'float-right';
        $closeBtnStyle = $isRtl ? 'margin: -1rem auto -1rem -1rem; float:left;' : '';
    @endphp

    @section('plugins.Select2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-file-invoice text-success {{ $margin }}"></i>{{ __('operations.mandate') }}
        </h4>
        <button wire:click="openModal" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('operations.new_mandate') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="icon fas fa-check {{ $margin }}"></i> {{ session('success') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.mandates_history') }}</h3>
            {{-- Filtres --}}
            <div class="card-tools d-flex">
                <input type="text" wire:model.live="filterSearch" class="form-control form-control-sm mr-2" placeholder="{{ __('crud.search') }}">
                <select wire:model.live="filterSection" class="form-control form-control-sm mr-2" style="width: 150px;">
                    <option value="">{{ __('menu.sections') }}</option>
                    @foreach($listSections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }}</option> @endforeach
                </select>
            </div>
        </div>
        
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>{{ __('visa.number') }}</th>
                        <th>{{ __('operations.date') }}</th>
                        <th>{{ __('crud.designation') }}</th>
                        <th>{{ __('menu.nomenclature') }}</th>
                        <th class="text-right">{{ __('operations.amount') }}</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mandats as $m)
                    <tr>
                        <td><span class="badge badge-success text-md px-2">{{ $m->Num_mandat }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($m->date_mandate)->format('d/m/Y') }}</td>
                        <td class="font-weight-bold">{{ $m->designation }}</td>
                        <td class="small text-muted">
                            <div class="d-block font-weight-bold text-dark">{{ $m->section->Num_section ?? '' }}</div>
                            {{ $m->obj1->Num ?? '' }}
                            @if($m->obj2) / {{ $m->obj2->Num ?? '' }} @endif
                            @if($m->obj3) / {{ $m->obj3->Num ?? '' }} @endif
                        </td>
                        <td class="text-right font-weight-bold text-success" style="font-size: 1.1em;">
                            <span dir="ltr">{{ number_format($m->details->sum('Montant'), 2, ',', ' ') }} DA</span>
                        </td>
                        <td class="text-right">
                            <!-- MENU DÉROULANT IMPRESSION -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-print"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('print.generique', ['dossier' => 'mandat', 'fichier' => 'model1', 'id' => $m->IDMandat]) }}" target="_blank">Modèle 1</a>
                                    <a class="dropdown-item" href="{{ route('print.generique', ['dossier' => 'mandat', 'fichier' => 'model2', 'id' => $m->IDMandat]) }}" target="_blank">Modèle 2</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('print.generique', ['dossier' => 'mandat', 'fichier' => 'bordereau', 'id' => $m->IDMandat]) }}" target="_blank">Bordereau</a>
                                </div>
                            </div>

                            <button wire:click="delete({{ $m->IDMandat }})" class="btn btn-sm btn-outline-danger ml-1" onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="float-right">{{ $mandats->links() }}</div></div>
    </div>

    <!-- MODAL CRÉATION -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 900px;">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title w-100 {{ $alignText }}">{{ __('operations.new_mandate') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <!-- Infos Générales -->
                        <div class="row">
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block">{{ __('visa.number') }}</label>
                                <input type="text" wire:model="form.Num_mandat" class="form-control font-weight-bold" placeholder="Ex: 2025/001">
                                @error('form.Num_mandat') <span class="text-danger small">Requis</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block">{{ __('operations.date') }}</label>
                                <input type="date" wire:model="form.date_mandate" class="form-control">
                            </div>
                             <div class="col-md-4">
                                <label class="{{ $alignText }} d-block">{{ __('crud.designation') }}</label>
                                <input type="text" wire:model="form.designation" class="form-control" placeholder="Objet">
                            </div>
                        </div>

                        <hr>

                        <!-- Filtres Cascade -->
                        <div class="row mb-2 bg-light p-2 rounded">
                            <div class="col-md-4">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.budgets') }}</label>
                                <select wire:model.live="form.IDBudjet" class="form-control form-control-sm"><option value="">{{ __('crud.select_option') }}</option>@foreach($budgets as $b) <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option> @endforeach</select>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.sections') }}</label>
                                <select wire:model.live="form.IDSection" class="form-control form-control-sm"><option value="">{{ __('crud.select_option') }}</option>@foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option> @endforeach</select>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.chapters') }}</label>
                                <select wire:model.live="form.IDObj1" class="form-control form-control-sm" {{ empty($listeObj1) ? 'disabled' : '' }}><option value="">{{ __('crud.select_option') }}</option>@foreach($listeObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option> @endforeach</select>
                            </div>
                        </div>
                        
                        <!-- Niveaux optionnels -->
                        <div class="row mb-2 bg-light p-2 rounded">
                            @if($maxLevel >= 2)
                            <div class="col-md-3">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.articles') }}</label>
                                <select wire:model.live="form.IDObj2" class="form-control form-control-sm" {{ empty($listeObj2) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option> @endforeach</select>
                            </div>
                            @endif
                            @if($maxLevel >= 3)
                            <div class="col-md-3">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.sub_articles') }}</label>
                                <select wire:model.live="form.IDObj3" class="form-control form-control-sm" {{ empty($listeObj3) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj3 as $o3) <option value="{{ $o3->IDObj3 }}">{{ $o3->Num }} - {{ $o3->designation }}</option> @endforeach</select>
                            </div>
                            @endif
                            @if($maxLevel >= 4)
                            <div class="col-md-3">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.rubrics') }}</label>
                                <select wire:model.live="form.IDObj4" class="form-control form-control-sm" {{ empty($listeObj4) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj4 as $o4) <option value="{{ $o4->IDObj4 }}">{{ $o4->Num }} - {{ $o4->designation }}</option> @endforeach</select>
                            </div>
                            @endif
                        </div>

                        <!-- Liste des Engagements -->
                        <div class="form-group border rounded p-0 mt-3" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-head-fixed text-nowrap mb-0">
                                <thead class="bg-secondary">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Engagement</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Déjà Payé</th>
                                        <th class="text-right">Reste</th>
                                        <th class="text-center" style="width: 150px;">A Payer (DA)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableEngagements as $eng)
                                        <tr>
                                            <td class="text-center align-middle">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="eng_{{ $eng->IDOperation_Budg }}" value="{{ $eng->IDOperation_Budg }}" wire:model.live="selectedEngagements">
                                                    <label class="custom-control-label" for="eng_{{ $eng->IDOperation_Budg }}"></label>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-secondary">{{ $eng->Num_operation }}</span>
                                                <span class="small d-block text-truncate" style="max-width: 200px;">{{ $eng->designation }}</span>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold" dir="ltr">{{ number_format($eng->Mont_operation, 2) }}</td>
                                            <td class="text-right align-middle text-muted" dir="ltr">{{ number_format($eng->deja_mandate, 2) }}</td>
                                            <td class="text-right align-middle text-success font-weight-bold" dir="ltr">{{ number_format($eng->solde_disponible, 2) }}</td>
                                            <td>
                                                @if(in_array($eng->IDOperation_Budg, $selectedEngagements))
                                                    <input type="number" step="0.01" 
                                                           wire:model="amountsToPay.{{ $eng->IDOperation_Budg }}" 
                                                           class="form-control form-control-sm text-right font-weight-bold border-success" 
                                                           dir="ltr">
                                                    @error("amountsToPay.".$eng->IDOperation_Budg) <span class="text-danger x-small d-block">{{ $message }}</span> @enderror
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">{{ $form['IDObj1'] ? 'Aucun solde disponible.' : 'Sélectionnez la ligne budgétaire.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @error('selectedEngagements') <span class="text-danger small">{{ __('crud.error_op') }}</span> @enderror

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>