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
use App\Models\BdgCf; 
use App\Models\BdgPj;
use App\Models\StkBonCommande; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showVisaModal = false;
    public $showPjModal = false; 
    public $maxLevel = 1; 
    
    // Listes pour le Formulaire (Création)
    public $budgets = [];
    public $sections = [];
    public $listeObj1 = []; 
    public $listeObj2 = []; 
    public $listeObj3 = []; 
    public $listeObj4 = []; 
    public $listeObj5 = []; 

    // Listes pour les FILTRES (Recherche) - AJOUT NIVEAUX 3,4,5
    public $filterListObj1 = [];
    public $filterListObj2 = [];
    public $filterListObj3 = [];
    public $filterListObj4 = [];
    public $filterListObj5 = [];
    
    // Variables de Filtre - AJOUT NIVEAUX 3,4,5
    public $filterSection = '';
    public $filterObj1 = '';
    public $filterObj2 = '';
    public $filterObj3 = '';
    public $filterObj4 = '';
    public $filterObj5 = '';
    
    public $bonsCommandes = [];
    public $maxAmountFromBC = null;
    public $creditOuvert = 0;
    public $creditEngage = 0;
    public $creditDisponible = 0;
    public $soldeInsuffisant = false; 
    public $scanFile; 
    public $pjFiles = [];   
    public $newPjFile;      
    public $currentPjs = []; 
    public $selectedOpId;   

    public array $form = [
        'IDBudjet' => '', 'IDSection' => '', 
        'IDObj1' => '', 'IDObj2' => '', 'IDObj3' => '', 'IDObj4' => '', 'IDObj5' => '',
        'designation' => '', 'Mont_operation' => 0, 'EXERCICE' => '', 'Beneficiaire' => '', 'IDBON' => '',
    ];

    public array $visaForm = [
        'IDOperation_Budg' => '', 'Date_envoi' => '', 'VISA_cf' => '', 'Date_retour' => '', 
        'Observations' => '', 'scan_path' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        $this->maxLevel = $params->nombre_niveau ?? 1;

        $this->budgets = BdgBudget::where('Archive', 0)->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->form['EXERCICE'] = date('Y');
    }

    public function loadBonsCommandes()
    {
        $this->bonsCommandes = StkBonCommande::where('valider', 1)
            ->doesntHave('engagement') 
            ->orderByDesc('date')
            ->limit(100)
            ->get();
    }

    public function updatedFormIDBON($value)
    {
        $this->maxAmountFromBC = null;
        if ($value) {
            $bc = StkBonCommande::with('fournisseur')->find($value);
            if ($bc) {
                $this->form['designation'] = $bc->designation;
                $this->form['Mont_operation'] = $bc->prixtotal;
                $this->maxAmountFromBC = $bc->prixtotal;
                $nomFournisseur = $bc->fournisseur->Nom ?? ($bc->fournisseur->Societe ?? '');
                if ($nomFournisseur) {
                    $this->form['designation'] .= ' - ' . $nomFournisseur;
                }
            }
        }
        $this->calculateBalance();
    }

    // --- LOGIQUE DES FILTRES DE RECHERCHE (CASCADE COMPLÈTE) ---
    public function updatedFilterSection($value)
    {
        $this->filterObj1 = ''; $this->filterObj2 = ''; $this->filterObj3 = ''; $this->filterObj4 = ''; $this->filterObj5 = '';
        $this->filterListObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->resetPage(); 
    }

    public function updatedFilterObj1($value)
    {
        $this->filterObj2 = ''; $this->filterObj3 = ''; $this->filterObj4 = ''; $this->filterObj5 = '';
        if ($this->maxLevel >= 2) {
            $this->filterListObj2 = $value ? BdgObj2::where('IDObj1', $value)->orderBy('Num')->get() : [];
        }
        $this->resetPage();
    }

    public function updatedFilterObj2($value)
    {
        $this->filterObj3 = ''; $this->filterObj4 = ''; $this->filterObj5 = '';
        if ($this->maxLevel >= 3) {
            $this->filterListObj3 = $value ? BdgObj3::where('IDObj2', $value)->orderBy('Num')->get() : [];
        }
        $this->resetPage();
    }

    public function updatedFilterObj3($value)
    {
        $this->filterObj4 = ''; $this->filterObj5 = '';
        if ($this->maxLevel >= 4) {
            $this->filterListObj4 = $value ? BdgObj4::where('IDObj3', $value)->orderBy('Num')->get() : [];
        }
        $this->resetPage();
    }

    public function updatedFilterObj4($value)
    {
        $this->filterObj5 = '';
        if ($this->maxLevel >= 5) {
            $this->filterListObj5 = $value ? BdgObj5::where('IDObj4', $value)->orderBy('Num')->get() : [];
        }
        $this->resetPage();
    }

    public function updatedFilterObj5() { $this->resetPage(); }
    // ----------------------------------------

    // --- CALCUL DU SOLDE ET VÉRIFICATION ---
    public function calculateBalance()
    {
        $this->soldeInsuffisant = false;

        if ($this->form['IDBudjet'] && $this->form['IDSection'] && $this->form['IDObj1']) {
            
            $queryAlloue = BdgOperationBudg::where('Type_operation', 2)
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->where('IDSection', $this->form['IDSection'])
                ->where('IDObj1', $this->form['IDObj1']);
            
            if($this->form['IDObj2']) $queryAlloue->where('IDObj2', $this->form['IDObj2']);
            if($this->form['IDObj3']) $queryAlloue->where('IDObj3', $this->form['IDObj3']);
            if($this->form['IDObj4']) $queryAlloue->where('IDObj4', $this->form['IDObj4']);
            
            $this->creditOuvert = $queryAlloue->sum('Mont_operation');

            $queryEngage = BdgOperationBudg::where('Type_operation', 3)
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->where('IDSection', $this->form['IDSection'])
                ->where('IDObj1', $this->form['IDObj1']);

            if($this->form['IDObj2']) $queryEngage->where('IDObj2', $this->form['IDObj2']);
            if($this->form['IDObj3']) $queryEngage->where('IDObj3', $this->form['IDObj3']);
            if($this->form['IDObj4']) $queryEngage->where('IDObj4', $this->form['IDObj4']);

            $this->creditEngage = $queryEngage->sum('Mont_operation');
            $this->creditDisponible = $this->creditOuvert - $this->creditEngage;

            if ($this->form['Mont_operation'] > $this->creditDisponible) {
                $this->soldeInsuffisant = true;
            }

        } else {
            $this->creditOuvert = 0; $this->creditDisponible = 0;
        }
    }

    public function updatedFormMontOperation() { $this->calculateBalance(); }

    public function updatedFormIDBudjet($value) {
        $b = $this->budgets->find($value);
        if($b) $this->form['EXERCICE'] = $b->EXERCICE;
        $this->calculateBalance();
    }
    public function updatedFormIDSection($value) {
        $this->resetLevels(1); 
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->calculateBalance();
    }
    public function updatedFormIDObj1($value) {
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
        $this->calculateBalance();
    }
    public function updatedFormIDObj2($value) {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
        $this->calculateBalance();
    }
    public function updatedFormIDObj3($value) {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
        $this->calculateBalance();
    }
    public function updatedFormIDObj4($value) {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
        $this->calculateBalance();
    }

    private function resetLevels($fromLevel) {
        if($fromLevel <= 1) { $this->form['IDObj1'] = ''; $this->listeObj2 = []; }
        if($fromLevel <= 2) { $this->form['IDObj2'] = ''; $this->listeObj3 = []; }
        if($fromLevel <= 3) { $this->form['IDObj3'] = ''; $this->listeObj4 = []; }
        if($fromLevel <= 4) { $this->form['IDObj4'] = ''; $this->listeObj5 = []; }
        if($fromLevel <= 5) { $this->form['IDObj5'] = ''; }
        $this->calculateBalance();
    }

    public function openModal() {
        $this->resetValidation();
        $this->form['Mont_operation'] = 0;
        $this->form['designation'] = '';
        $this->form['IDBON'] = ''; 
        $this->maxAmountFromBC = null;
        $this->pjFiles = []; 
        $this->soldeInsuffisant = false;
        $this->loadBonsCommandes(); 
        $this->showModal = true;
        $this->calculateBalance();
    }

    public function save()
    {
        $this->validate([
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required',
            'form.IDObj1' => 'required',
            'form.Mont_operation' => 'required|numeric|min:0.01',
            'form.designation' => 'required|string',
            'pjFiles.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($this->maxAmountFromBC !== null && $this->form['Mont_operation'] > $this->maxAmountFromBC) {
            $this->addError('form.Mont_operation', "Le montant ne peut pas dépasser celui du Bon de Commande.");
            return;
        }

        $this->calculateBalance(); 
        if ($this->soldeInsuffisant) {
            $this->addError('form.Mont_operation', __('messages.insufficient_balance'));
            return;
        }

        $op = BdgOperationBudg::create(array_merge($this->form, [
            'Num_operation' => rand(100000,999999),
            'Type_operation' => 3, 
            'IDObj2' => $this->form['IDObj2'] ?: 0,
            'IDObj3' => $this->form['IDObj3'] ?: 0,
            'IDObj4' => $this->form['IDObj4'] ?: 0,
            'IDObj5' => $this->form['IDObj5'] ?: 0,
            'IDBON' => $this->form['IDBON'] ?: null, 
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]));

        if (!empty($this->pjFiles)) {
            foreach ($this->pjFiles as $file) {
                $path = $file->store('pieces_jointes_engagements', 'public');
                BdgPj::create(['IDOperation_Budg' => $op->IDOperation_Budg, 'chemin_fichier' => $path, 'nom_fichier' => $file->getClientOriginalName(), 'created_at' => now()]);
            }
        }

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id) { 
        $op = BdgOperationBudg::with('cf')->findOrFail($id); 
        if ($op->cf && !empty($op->cf->VISA_cf)) { session()->flash('error', 'Impossible de supprimer : Visé.'); return; } 
        if($op->cf) { if($op->cf->scan_path) Storage::disk('public')->delete($op->cf->scan_path); $op->cf->delete(); } 
        $pjs = BdgPj::where('IDOperation_Budg', $id)->get(); foreach($pjs as $pj) { Storage::disk('public')->delete($pj->chemin_fichier); $pj->delete(); } 
        $op->delete(); session()->flash('success', __('crud.item_deleted')); 
    }
    
    public function openPjModal($opId) { $this->selectedOpId = $opId; $this->newPjFile = null; $this->currentPjs = BdgPj::where('IDOperation_Budg', $opId)->get(); $this->showPjModal = true; }
    public function addPj() { $this->validate(['newPjFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']); $path = $this->newPjFile->store('pieces_jointes_engagements', 'public'); BdgPj::create(['IDOperation_Budg' => $this->selectedOpId, 'chemin_fichier' => $path, 'nom_fichier' => $this->newPjFile->getClientOriginalName(), 'created_at' => now()]); $this->currentPjs = BdgPj::where('IDOperation_Budg', $this->selectedOpId)->get(); $this->newPjFile = null; session()->flash('success_pj', 'Ajouté.'); }
    public function deletePj($pjId) { $pj = BdgPj::findOrFail($pjId); Storage::disk('public')->delete($pj->chemin_fichier); $opId = $pj->IDOperation_Budg; $pj->delete(); $this->currentPjs = BdgPj::where('IDOperation_Budg', $opId)->get(); }
    public function openVisaModal($opId) { $this->resetValidation(); $this->scanFile = null; $visa = BdgCf::where('IDOperation_Budg', $opId)->first(); $this->visaForm = $visa ? $visa->toArray() : ['IDOperation_Budg' => $opId, 'Date_envoi' => date('Y-m-d'), 'VISA_cf' => '', 'Date_retour' => date('Y-m-d'), 'Observations' => '', 'scan_path' => '']; $this->showVisaModal = true; }
    public function saveVisa() { $this->validate(['visaForm.Date_envoi' => 'required|date', 'scanFile' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120']); $filePath = $this->visaForm['scan_path']; if ($this->scanFile) $filePath = $this->scanFile->store('visas_engagements', 'public'); BdgCf::updateOrCreate(['IDOperation_Budg' => $this->visaForm['IDOperation_Budg']], array_merge($this->visaForm, ['scan_path' => $filePath, 'IDLogin' => auth()->id() ?? 0, 'Creer_le' => now()])); $this->showVisaModal = false; session()->flash('success', __('messages.visa_saved')); }

    public function with() {
        // Construction de la requête avec filtres
        $query = BdgOperationBudg::with(['budget', 'section', 'obj1', 'obj2', 'obj3', 'obj4', 'obj5', 'cf', 'pjs', 'bonCommande'])
            ->where('Type_operation', 3);

        // Application des filtres EN CASCADE pour la RECHERCHE
        if ($this->filterSection) { $query->where('IDSection', $this->filterSection); }
        if ($this->filterObj1) { $query->where('IDObj1', $this->filterObj1); }
        if ($this->filterObj2) { $query->where('IDObj2', $this->filterObj2); }
        if ($this->filterObj3) { $query->where('IDObj3', $this->filterObj3); }
        if ($this->filterObj4) { $query->where('IDObj4', $this->filterObj4); }
        if ($this->filterObj5) { $query->where('IDObj5', $this->filterObj5); }

        $totalFiltered = (clone $query)->sum('Mont_operation');

        return [
            'engagements' => $query->orderByDesc('Creer_le')->paginate(10),
            'totalFiltered' => $totalFiltered,
        ];
    }
}; ?>

<div>
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
            <i class="fas fa-file-signature text-primary {{ $margin }}"></i>{{ __('operations.engagement') }}
        </h4>
        <div class="btn-group">
            {{-- BOUTON LISTE GLOBALE --}}
            <a href="{{ route('print.generique', ['dossier' => 'engagement', 'fichier' => 'liste', 'id' => date('Y')]) }}" target="_blank" class="btn btn-secondary shadow-sm mr-2">
                <i class="fas fa-list"></i> Liste PDF
            </a>
            <button wire:click="openModal" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('operations.new_engagement') }}
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="icon fas fa-check {{ $margin }}"></i> {{ session('success') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="icon fas fa-ban {{ $margin }}"></i> {{ session('error') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.engagements_history') }}</h3>
            <div class="card-tools d-flex align-items-center">
                 <span class="font-weight-bold mr-3 text-primary" dir="ltr">Total: {{ number_format($totalFiltered, 2, ',', ' ') }} DA</span>
            </div>
        </div>
        
        <div class="card-body p-2 bg-light border-bottom">
             {{-- BARRE DE FILTRES AVANCÉE --}}
            <div class="row">
                <div class="col-md-2">
                    <select wire:model.live="filterSection" class="form-control form-control-sm">
                        <option value="">{{ __('menu.sections') }}</option>
                        @foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }}</option> @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterObj1" class="form-control form-control-sm" {{ empty($filterSection) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.chapters') }}</option>
                        @foreach($filterListObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }}</option> @endforeach
                    </select>
                </div>
                @if($maxLevel >= 2)
                <div class="col-md-2">
                    <select wire:model.live="filterObj2" class="form-control form-control-sm" {{ empty($filterObj1) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.articles') }}</option>
                        @foreach($filterListObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }}</option> @endforeach
                    </select>
                </div>
                @endif
                @if($maxLevel >= 3)
                <div class="col-md-2">
                    <select wire:model.live="filterObj3" class="form-control form-control-sm" {{ empty($filterObj2) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.sub_articles') }}</option>
                        @foreach($filterListObj3 as $o3) <option value="{{ $o3->IDObj3 }}">{{ $o3->Num }}</option> @endforeach
                    </select>
                </div>
                @endif
                @if($maxLevel >= 4)
                <div class="col-md-2">
                    <select wire:model.live="filterObj4" class="form-control form-control-sm" {{ empty($filterObj3) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.rubrics') }}</option>
                        @foreach($filterListObj4 as $o4) <option value="{{ $o4->IDObj4 }}">{{ $o4->Num }}</option> @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="{{ $alignText }}">{{ __('menu.sections') }}</th>
                        <th class="{{ $alignText }}">{{ __('menu.nomenclature') }} (Arborescence)</th>
                        <th class="{{ $alignText }}">{{ __('operations.object') }}</th>
                        <th class="text-right">{{ __('operations.amount') }}</th>
                        <th class="text-center">{{ __('visa.status') }}</th>
                        <th class="text-center">P.J</th> 
                        <th class="text-center">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($engagements as $op)
                    <tr class="{{ $op->cf && $op->cf->VISA_cf ? 'bg-light' : '' }}">
                        <td class="font-weight-bold text-primary">{{ $op->section->Num_section ?? '' }}</td>
                        <td>
                            <div class="text-bold text-dark">{{ $op->obj1->Num ?? '' }}</div>
                            @if($op->IDObj2) <div class="text-muted small {{ $isRtl ? 'mr-2' : 'ml-2' }}"><i class="fas fa-angle-right"></i> {{ $op->obj2->Num ?? '' }}</div> @endif
                            @if($op->IDObj3) <div class="text-muted small {{ $isRtl ? 'mr-3' : 'ml-3' }}"><i class="fas fa-angle-right"></i> {{ $op->obj3->Num ?? '' }}</div> @endif
                            @if($op->IDObj4) <div class="text-muted small {{ $isRtl ? 'mr-4' : 'ml-4' }}"><i class="fas fa-angle-right"></i> {{ $op->obj4->Num ?? '' }}</div> @endif
                            @if($op->IDObj5) <div class="text-muted small {{ $isRtl ? 'mr-5' : 'ml-5' }}"><i class="fas fa-angle-right"></i> {{ $op->obj5->Num ?? '' }}</div> @endif
                        </td>
                        <td>
                            {{ $op->designation }}
                            @if($op->bonCommande)
                                <br><span class="badge badge-warning border text-dark"><i class="fas fa-shopping-cart"></i> BC N° {{ $op->bonCommande->Num_bon }}</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-primary" style="font-size: 1.1em;">
                            <span dir="ltr">{{ number_format($op->Mont_operation, 2, ',', ' ') }} DA</span>
                        </td>
                        <td class="text-center">
                            @if($op->cf && $op->cf->VISA_cf)
                                <div class="badge badge-success px-2"><i class="fas fa-check"></i> {{ $op->cf->VISA_cf }}</div>
                            @elseif($op->cf)
                                <span class="badge badge-warning text-dark px-2"><i class="fas fa-clock"></i> En cours</span>
                            @else
                                <span class="badge badge-light border">Non</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button wire:click="openPjModal({{ $op->IDOperation_Budg }})" class="btn btn-xs {{ $op->pjs->count() > 0 ? 'btn-info' : 'btn-outline-secondary' }}">
                                <i class="fas fa-paperclip"></i> @if($op->pjs->count() > 0) {{ $op->pjs->count() }} @else + @endif
                            </button>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                {{-- BOUTON IMPRESSION DROPDOWN --}}
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs border dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('print.generique', ['dossier' => 'engagement', 'fichier' => 'fiche', 'id' => $op->IDOperation_Budg]) }}" target="_blank">
                                            <i class="fas fa-file-alt mr-2"></i> Fiche d'engagement
                                        </a>
                                    </div>
                                </div>

                                <button wire:click="openVisaModal({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-default border mx-1" title="Visa">
                                    <i class="fas fa-stamp text-purple"></i>
                                </button>
                                
                                @if($op->cf && $op->cf->VISA_cf)
                                    <button class="btn btn-xs btn-outline-secondary disabled" title="Verrouillé"><i class="fas fa-lock"></i></button>
                                @else
                                    <button wire:click="delete({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-danger" onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="{{ $floatRight }}">{{ $engagements->links() }}</div></div>
    </div>

    <!-- MODAL (Le formulaire reste identique mais utilise bien $maxLevel) -->
    {{-- Je ne répète pas tout le code HTML de la modal car il est déjà correct dans la version précédente, 
         il faut juste s'assurer que les blocs @if($maxLevel >= X) sont bien présents comme dans le PHP ci-dessus --}}
     @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title {{ $alignText }} w-100">{{ __('operations.new_engagement') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        {{-- (Le contenu de la modal avec les selects Obj1-5 comme précédemment) --}}
                         <div class="form-group bg-warning p-2 rounded mb-3">
                            <label class="{{ $alignText }} d-block small text-dark font-weight-bold">
                                <i class="fas fa-file-import {{ $margin }}"></i> Importer depuis un Bon de Commande (Optionnel)
                            </label>
                            <select wire:model.live="form.IDBON" class="form-control form-control-sm font-weight-bold text-dark">
                                <option value="">-- Saisie Manuelle (Aucun BC) --</option>
                                @foreach($bonsCommandes as $bc)
                                    <option value="{{ $bc->IDBON }}">
                                        N° {{ $bc->Num_bon }} | {{ number_format($bc->prixtotal, 2) }} DA | {{ Str::limit($bc->designation, 40) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                         <!-- Info Solde -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-box bg-light shadow-none border">
                                    <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
                                    <div class="info-box-content text-right">
                                        <span class="info-box-text">{{ __('operations.credit_open') }}</span>
                                        <span class="info-box-number" dir="ltr">{{ number_format($creditOuvert, 2, ',', ' ') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box shadow-sm {{ $creditDisponible > 0 ? 'bg-success' : 'bg-danger' }}">
                                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    <div class="info-box-content text-right">
                                        <span class="info-box-text">{{ __('operations.credit_available') }}</span>
                                        <span class="info-box-number h4 mb-0" dir="ltr">{{ number_format($creditDisponible, 2, ',', ' ') }} DA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('menu.budgets') }}</label>
                                <select wire:model.live="form.IDBudjet" class="form-control"><option value="">{{ __('crud.select_option') }}</option>@foreach($budgets as $b) <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option> @endforeach</select>
                            </div>
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('menu.sections') }}</label>
                                <select wire:model.live="form.IDSection" class="form-control"><option value="">{{ __('crud.select_option') }}</option>@foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option> @endforeach</select>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.chapters') }}</label>
                            <select wire:model.live="form.IDObj1" class="form-control" {{ empty($listeObj1) ? 'disabled' : '' }}><option value="">{{ __('crud.select_option') }}</option>@foreach($listeObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option> @endforeach</select>
                        </div>
                        @if($maxLevel >= 2)
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('menu.articles') }}</label>
                            <select wire:model.live="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option> @endforeach</select>
                        </div>
                        @endif
                        @if($maxLevel >= 3 && !empty($form['IDObj2']))
                        <div class="form-group ml-4 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.sub_articles') }}</label>
                            <select wire:model.live="form.IDObj3" class="form-control form-control-sm" {{ empty($listeObj3) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj3 as $o3) <option value="{{ $o3->IDObj3 }}">{{ $o3->Num }} - {{ $o3->designation }}</option> @endforeach</select>
                        </div>
                        @endif
                        @if($maxLevel >= 4 && !empty($form['IDObj3']))
                        <div class="form-group ml-5 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.rubrics') }}</label>
                            <select wire:model.live="form.IDObj4" class="form-control form-control-sm" {{ empty($listeObj4) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj4 as $o4) <option value="{{ $o4->IDObj4 }}">{{ $o4->Num }} - {{ $o4->designation }}</option> @endforeach</select>
                        </div>
                        @endif
                        @if($maxLevel >= 5 && !empty($form['IDObj4']))
                        <div class="form-group ml-5 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.sub_rubrics') }}</label>
                            <select wire:model.live="form.IDObj5" class="form-control form-control-sm" {{ empty($listeObj5) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj5 as $o5) <option value="{{ $o5->IDObj5 }}">{{ $o5->Num }} - {{ $o5->designation }}</option> @endforeach</select>
                        </div>
                        @endif
                        <hr>
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('operations.object') }}</label>
                            <input type="text" wire:model="form.designation" class="form-control">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group bg-light p-2 rounded border-dashed">
                            <label class="{{ $alignText }} d-block mb-1 font-weight-bold"><i class="fas fa-paperclip {{ $margin }}"></i> Pièces Jointes</label>
                            <input type="file" wire:model="pjFiles" class="form-control-file" multiple accept="image/*,application/pdf" capture="environment">
                            <div wire:loading wire:target="pjFiles" class="text-info small mt-1"><i class="fas fa-spinner fa-spin"></i> ...</div>
                            @error('pjFiles.*') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('operations.amount') }} (DA)</label>
                                <input type="number" step="0.01" wire:model.live="form.Mont_operation" class="form-control form-control-lg {{ $soldeInsuffisant ? 'is-invalid border-danger' : 'border-primary' }} text-right font-weight-bold" dir="ltr">
                                @if($soldeInsuffisant)
                                    <span class="text-danger small font-weight-bold d-block mt-1">
                                        <i class="fas fa-exclamation-circle"></i> {{ __('messages.insufficient_balance') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="pjFiles" {{ $creditDisponible <= 0 || $soldeInsuffisant ? 'disabled' : '' }}>{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    {{-- AUTRES MODALS (VISA, PJ) --}}
    @if($showPjModal) <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-secondary text-white"><h5 class="modal-title {{ $alignText }} w-100"><i class="fas fa-paperclip {{ $margin }}"></i>Pièces Jointes</h5><button type="button" class="close text-white" wire:click="$set('showPjModal', false)" style="{{ $closeBtnStyle }}">&times;</button></div><div class="modal-body p-0"><div class="p-3 bg-light border-bottom"><label class="small font-weight-bold text-muted {{ $alignText }} d-block">Ajouter un document</label><div class="input-group"><input type="file" class="form-control form-control-sm" wire:model="newPjFile" accept="image/*,application/pdf"><div class="input-group-append"><button class="btn btn-sm btn-success" wire:click="addPj" wire:loading.attr="disabled" wire:target="newPjFile"><i class="fas fa-plus"></i></button></div></div><div wire:loading wire:target="newPjFile" class="text-info small mt-1"><i class="fas fa-spinner fa-spin"></i> ...</div></div><ul class="list-group list-group-flush">@forelse($currentPjs as $pj)<li class="list-group-item d-flex justify-content-between align-items-center"><a href="{{ Storage::url($pj->chemin_fichier) }}" target="_blank" class="text-dark text-decoration-none"><i class="far fa-file-pdf text-danger {{ $margin }}"></i> {{ $pj->nom_fichier ?? 'Doc' }}</a><button wire:click="deletePj({{ $pj->ID_PJ }})" class="btn btn-xs btn-outline-danger" onclick="confirm('Supprimer ?') || event.stopImmediatePropagation()"><i class="fas fa-times"></i></button></li>@empty<li class="list-group-item text-center text-muted py-3 small">Aucun fichier.</li>@endforelse</ul></div><div class="modal-footer p-2"><button type="button" class="btn btn-secondary btn-sm" wire:click="$set('showPjModal', false)">{{ __('crud.close') }}</button></div></div></div></div>@endif
    @if($showVisaModal) <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-purple text-white"><h5 class="modal-title {{ $alignText }} w-100"><i class="fas fa-stamp {{ $margin }}"></i>{{ __('visa.financial_control') }}</h5><button type="button" class="close text-white" wire:click="$set('showVisaModal', false)" style="{{ $closeBtnStyle }}">&times;</button></div><form wire:submit.prevent="saveVisa"><div class="modal-body"><div class="form-group"><label class="{{ $alignText }} d-block">{{ __('visa.sent_date') }}</label><input type="date" wire:model="visaForm.Date_envoi" class="form-control"></div><div class="row"><div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('visa.number') }}</label><input type="text" wire:model="visaForm.VISA_cf" class="form-control font-weight-bold"></div><div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('visa.date') }}</label><input type="date" wire:model="visaForm.Date_retour" class="form-control"></div></div><div class="form-group mt-2"><label class="{{ $alignText }} d-block">{{ __('visa.scan') }}</label><input type="file" wire:model="scanFile" class="form-control-file"></div><div class="form-group mt-2"><label class="{{ $alignText }} d-block">{{ __('visa.observations') }}</label><textarea wire:model="visaForm.Observations" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" wire:click="$set('showVisaModal', false)">{{ __('crud.close') }}</button><button type="submit" class="btn bg-purple" wire:loading.attr="disabled">{{ __('visa.save') }}</button></div></form></div></div></div>@endif
</div>