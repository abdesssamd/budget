<?php

use Livewire\Volt\Component;
use App\Models\BdgFacture;
use App\Models\BdgOperationBudg;
use App\Models\StkFournisseur;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use App\Models\BdgMandat;      // Nouveau
use App\Models\BdgDetailOpBud; // Nouveau
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showMandatModal = false; // Nouvelle modale pour mandater
    
    // Listes de filtres
    public $budgets = [];
    public $sections = [];
    
    // Listes opérationnelles
    public $fournisseurs = [];
    public $engagements = []; 

    public $selectedEngagementInfo = null;

    // Formulaire Facture
    public array $form = [
        'IDBudjet' => '', 'IDSection' => '', 
        'IDOperation_Budg' => '', 'NumFournisseur' => '',
        'num_facture' => '', 'date_facture' => '',
        'Montant' => 0, 'Observations' => '',
    ];

    // Formulaire Mandat Rapide
    public array $mandatForm = [
        'IDbdg_facture' => null, // La facture qu'on paie
        'Num_mandat' => '',
        'date_mandate' => '',
        'designation' => '',
    ];

    public function mount()
    {
        $this->budgets = BdgBudget::where('Archive', 0)->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->fournisseurs = StkFournisseur::orderBy('Nom')->get();
        $this->form['date_facture'] = date('Y-m-d');
    }

    public function loadEngagements()
    {
        if ($this->form['IDBudjet'] && $this->form['IDSection']) {
            $this->engagements = BdgOperationBudg::with(['obj1', 'obj2'])
                ->where('Type_operation', 3) 
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->where('IDSection', $this->form['IDSection'])
                ->orderByDesc('Creer_le')
                ->get();
        } else {
            $this->engagements = [];
        }
    }

    public function updatedFormIDBudjet() { $this->loadEngagements(); }
    public function updatedFormIDSection() { $this->loadEngagements(); }

    public function updatedFormIDOperation_Budg($value)
    {
        if ($value) {
            $eng = BdgOperationBudg::find($value);
            $this->selectedEngagementInfo = $eng;
            
            // Calcul du reste à liquider (Engagement - Somme des factures déjà saisies)
            $dejaLiquide = BdgFacture::where('IDOperation_Budg', $eng->IDOperation_Budg)->sum('Montant');
            $reste = $eng->Mont_operation - $dejaLiquide;
            
            // On propose le reste par défaut
            $this->form['Montant'] = $reste > 0 ? $reste : 0;
        } else {
            $this->selectedEngagementInfo = null;
            $this->form['Montant'] = 0;
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset('form', 'selectedEngagementInfo', 'engagements');
        $this->form['date_facture'] = date('Y-m-d');
        $this->fournisseurs = StkFournisseur::orderBy('Nom')->get();
        $this->showModal = true;
    }

    // --- NOUVEAU : OUVERTURE MODALE MANDAT ---
    public function openMandatModal($factureId)
    {
        $f = BdgFacture::with('engagement')->findOrFail($factureId);
        
        $this->mandatForm = [
            'IDbdg_facture' => $f->IDbdg_facture,
            'Num_mandat' => '', // À saisir
            'date_mandate' => date('Y-m-d'),
            'designation' => 'Paiement Facture N° ' . $f->num_facture,
        ];
        
        $this->showMandatModal = true;
    }

    public function saveMandat()
    {
        $this->validate([
            'mandatForm.Num_mandat' => 'required|string',
            'mandatForm.date_mandate' => 'required|date',
            'mandatForm.designation' => 'required|string',
        ]);

        $facture = BdgFacture::with('engagement')->findOrFail($this->mandatForm['IDbdg_facture']);
        $engagement = $facture->engagement;

        // 1. Créer le Mandat
        $mandat = BdgMandat::create([
            'Num_mandat' => $this->mandatForm['Num_mandat'],
            'date_mandate' => $this->mandatForm['date_mandate'],
            'designation' => $this->mandatForm['designation'],
            'NumFournisseur' => $facture->NumFournisseur, 
            'EXERCICE' => $facture->IDExercice ?? date('Y'), // Fallback année
            'IDBudjet' => $facture->IDBudjet,
            'IDSection' => $facture->IDSection,
            'IDObj1' => $facture->IDObj1,
            'IDObj2' => $facture->IDObj2,
            'IDObj3' => $facture->IDObj3,
            'IDObj4' => $facture->IDObj4,
            'IDObj5' => $facture->IDObj5,
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]);

        // 2. Créer le détail (Ligne budgétaire)
        BdgDetailOpBud::create([
            'IDMandat' => $mandat->IDMandat,
            'IDOperation_Budg' => $engagement->IDOperation_Budg,
            'Montant' => $facture->Montant, // On mandate le montant exact de la facture
            'designation' => $facture->Observations . ' (Facture '.$facture->num_facture.')',
            'NumFournisseur' => $facture->NumFournisseur,
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]);

        // 3. Mettre à jour la facture avec l'ID du mandat
        $facture->update(['IDMandat' => $mandat->IDMandat]);

        $this->showMandatModal = false;
        session()->flash('success', 'Mandat créé avec succès !');
    }

    public function save()
    {
        $this->validate([
            'form.IDOperation_Budg' => 'required|exists:bdg_operation_budg,IDOperation_Budg',
            'form.num_facture' => 'required|string',
            'form.date_facture' => 'required|date',
            'form.NumFournisseur' => 'required|exists:stk_fournisseur,NumFournisseur',
            'form.Montant' => 'required|numeric|min:0.01',
        ]);

        $engagement = BdgOperationBudg::findOrFail($this->form['IDOperation_Budg']);

        // --- CORRECTION CRITIQUE : Vérification du cumul des factures ---
        $dejaLiquide = BdgFacture::where('IDOperation_Budg', $engagement->IDOperation_Budg)->sum('Montant');
        $nouveauTotal = $dejaLiquide + $this->form['Montant'];

        if ($nouveauTotal > $engagement->Mont_operation) {
            $reste = $engagement->Mont_operation - $dejaLiquide;
            $this->addError('form.Montant', __('messages.insufficient_balance') . ' (Max: ' . number_format($reste, 2) . ')');
            return;
        }

        BdgFacture::create([
            'Reference' => 'FACT-'.rand(1000,9999), 
            'num_facture' => $this->form['num_facture'],
            'date_facture' => $this->form['date_facture'],
            'Montant' => $this->form['Montant'],
            'NumFournisseur' => $this->form['NumFournisseur'],
            'IDOperation_Budg' => $this->form['IDOperation_Budg'],
            'IDBudjet' => $engagement->IDBudjet,
            'IDSection' => $engagement->IDSection,
            'IDObj1' => $engagement->IDObj1,
            'IDObj2' => $engagement->IDObj2,
            'IDObj3' => $engagement->IDObj3,
            'IDObj4' => $engagement->IDObj4,
            'IDObj5' => $engagement->IDObj5,
            'Observations' => $this->form['Observations']
        ]);

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id)
    {
        BdgFacture::findOrFail($id)->delete();
        session()->flash('success', __('crud.item_deleted'));
    }

    public function with()
    {
        return [
            'factures' => BdgFacture::with(['engagement.obj1', 'fournisseur', 'mandat'])
                ->orderByDesc('date_facture')
                ->paginate(10)
        ];
    }
}; ?>

<div>
    {{-- Helpers RTL --}}
    @php
        $isRtl = app()->getLocale() == 'ar';
        $alignText = $isRtl ? 'text-right' : 'text-left';
        $margin = $isRtl ? 'ml-2' : 'mr-2';
        $closeBtnStyle = $isRtl ? 'margin: -1rem auto -1rem -1rem; float:left;' : '';
    @endphp

    @section('plugins.Select2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-file-invoice text-warning {{ $margin }}"></i>{{ __('operations.liquidation') }}
        </h4>
        <button wire:click="openModal" class="btn btn-warning shadow-sm font-weight-bold">
            <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('operations.new_liquidation') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check {{ $margin }}"></i> {{ session('success') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.invoices_history') }}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="{{ $alignText }}">{{ __('operations.invoice_num') }}</th>
                        <th class="{{ $alignText }}">{{ __('operations.invoice_date') }}</th>
                        <th class="{{ $alignText }}">{{ __('operations.supplier') }}</th>
                        <th class="{{ $alignText }}">{{ __('operations.engagement') }}</th>
                        <th class="text-right">{{ __('operations.amount') }}</th>
                        <th class="text-center">Mandaté ?</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $f)
                    <tr>
                        <td class="font-weight-bold">{{ $f->num_facture }}</td>
                        <td>{{ \Carbon\Carbon::parse($f->date_facture)->format('d/m/Y') }}</td>
                        <td>{{ $f->fournisseur->Nom ?? ($f->fournisseur->Societe ?? '?') }}</td>
                        <td class="small text-muted">
                            @if($f->engagement)
                                <div class="text-dark font-weight-bold">N° {{ $f->engagement->Num_operation }}</div>
                                {{ Str::limit($f->engagement->designation, 20) }}
                            @else
                                <span class="text-danger">--</span>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-dark" dir="ltr">
                            {{ number_format($f->Montant, 2, ',', ' ') }} DA
                        </td>
                        <td class="text-center">
                            @if($f->IDMandat)
                                <span class="badge badge-success">Oui (N° {{ $f->mandat->Num_mandat ?? '' }})</span>
                            @else
                                {{-- BOUTON MANDATER RAPIDE --}}
                                <button wire:click="openMandatModal({{ $f->IDbdg_facture }})" class="btn btn-xs btn-outline-success shadow-sm font-weight-bold">
                                    <i class="fas fa-money-bill-wave {{ $margin }}"></i> Mandater
                                </button>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="delete({{ $f->IDbdg_facture }})" 
                                    class="btn btn-xs btn-outline-danger" 
                                    onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()"
                                    @if($f->IDMandat) disabled title="Déjà mandaté" @endif>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="float-right">{{ $factures->links() }}</div></div>
    </div>

    <!-- MODAL LIQUIDATION (Existante) -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold">{{ __('operations.new_liquidation') }}</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <!-- Sélection Engagement -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.budgets') }}</label>
                                <select wire:model.live="form.IDBudjet" class="form-control form-control-sm"><option value="">{{ __('crud.select_option') }}</option>@foreach($budgets as $b) <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option> @endforeach</select>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted {{ $alignText }} d-block">{{ __('menu.sections') }}</label>
                                <select wire:model.live="form.IDSection" class="form-control form-control-sm"><option value="">{{ __('crud.select_option') }}</option>@foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option> @endforeach</select>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label class="{{ $alignText }} d-block">{{ __('operations.engagement') }}</label>
                            <select wire:model.live="form.IDOperation_Budg" class="form-control font-weight-bold" {{ empty($engagements) ? 'disabled' : '' }}>
                                <option value="">{{ empty($engagements) ? 'Filtrez d\'abord...' : __('crud.select_option') }}</option>
                                @foreach($engagements as $e) <option value="{{ $e->IDOperation_Budg }}">N° {{ $e->Num_operation }} | {{ number_format($e->Mont_operation, 2) }} DA | {{ Str::limit($e->designation, 40) }}</option> @endforeach
                            </select>
                        </div>
                        @if($selectedEngagementInfo)
                            <div class="alert alert-light border-left border-primary p-2">
                                <div class="d-flex justify-content-between">
                                    <span><strong>Objet :</strong> {{ $selectedEngagementInfo->designation }}</span>
                                    <span class="text-primary font-weight-bold">{{ number_format($selectedEngagementInfo->Mont_operation, 2) }} DA</span>
                                </div>
                            </div>
                        @endif

                        <!-- Facture -->
                        <h6 class="text-warning font-weight-bold border-bottom pb-2 mb-3 mt-4"><i class="fas fa-file-invoice {{ $margin }}"></i> Détails Facture</h6>
                        <div class="row">
                            <div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('operations.invoice_num') }}</label><input type="text" wire:model="form.num_facture" class="form-control"></div>
                            <div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('operations.invoice_date') }}</label><input type="date" wire:model="form.date_facture" class="form-control"></div>
                        </div>
                        <div class="form-group mt-2">
                            <label class="{{ $alignText }} d-block">{{ __('operations.supplier') }}</label>
                            <select wire:model="form.NumFournisseur" class="form-control"><option value="">{{ __('crud.select_option') }}</option>@foreach($fournisseurs as $fr) <option value="{{ $fr->NumFournisseur }}">{{ $fr->Nom }} {{ $fr->Societe }}</option> @endforeach</select>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-8"><label class="{{ $alignText }} d-block">{{ __('visa.observations') }}</label><textarea wire:model="form.Observations" class="form-control" rows="1"></textarea></div>
                            <div class="col-md-4"><label class="{{ $alignText }} d-block text-warning font-weight-bold">{{ __('operations.invoice_amount') }} (DA)</label><input type="number" step="0.01" wire:model="form.Montant" class="form-control form-control-lg border-warning text-right font-weight-bold" dir="ltr">@error('form.Montant') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-warning text-dark font-weight-bold">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- NOUVELLE MODALE MANDAT RAPIDE -->
    @if($showMandatModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title {{ $alignText }} w-100"><i class="fas fa-money-bill-wave {{ $margin }}"></i> Créer le Mandat</h5>
                    <button type="button" class="close text-white" wire:click="$set('showMandatModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="saveMandat">
                    <div class="modal-body">
                        <div class="alert alert-success bg-white border-success text-success p-2 mb-3 shadow-sm rounded">
                            <i class="fas fa-check-circle {{ $margin }}"></i> Vous allez mandater la facture <strong>{{ $mandatForm['designation'] }}</strong>.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('visa.number') }}</label>
                                <input type="text" wire:model="mandatForm.Num_mandat" class="form-control font-weight-bold" placeholder="Ex: 2025/123" autofocus>
                                @error('mandatForm.Num_mandat') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('operations.date') }}</label>
                                <input type="date" wire:model="mandatForm.date_mandate" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="{{ $alignText }} d-block">{{ __('crud.designation') }}</label>
                            <input type="text" wire:model="mandatForm.designation" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showMandatModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-check {{ $margin }}"></i> Confirmer le Mandat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>