<?php

use Livewire\Volt\Component;
use App\Models\BdgOperationRecette;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use App\Models\BdgObj1;
use App\Models\BdgObj2;
use App\Models\BdgObj3;
use App\Models\BdgObj4;
use App\Models\BdgObj5;
use App\Models\StkFournisseur;
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

    // Listes de sélection
    public $budgets = [];
    public $sections = [];
    public $tiers = [];
    
    // Listes en cascade Modal
    public $listeObj1 = []; 
    public $listeObj2 = [];
    public $listeObj3 = [];
    public $listeObj4 = [];
    public $listeObj5 = [];

    // Listes Filtres
    public $filterListObj1 = [];
    public $filterListObj2 = [];

    // Filtres
    public $filterSection = '';
    public $filterObj1 = '';
    public $filterSearch = '';

    public array $form = [
        'IDBudjet' => '', 'IDSection' => '', 
        'IDObj1' => '', 'IDObj2' => '', 'IDObj3' => '', 'IDObj4' => '', 'IDObj5' => '',
        'designation' => '', 
        'Mont_operation' => 0, 
        'EXERCICE' => '',
        'date_perception' => '',
        'NumFournisseur' => '',
        'Observations' => ''
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        $this->maxLevel = $params->nombre_niveau ?? 1;

        $this->budgets = BdgBudget::where('Archive', 0)->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->tiers = StkFournisseur::orderBy('Nom')->get(); // Pour choisir qui paie
        
        $this->form['EXERCICE'] = date('Y');
        $this->form['date_perception'] = date('Y-m-d');
    }

    // --- FILTRES TABLEAU ---
    public function updatedFilterSection($value) {
        $this->filterObj1 = '';
        $this->filterListObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->resetPage();
    }
    public function updatedFilterObj1($value) { $this->resetPage(); }

    // --- CASCADE MODAL ---
    public function updatedFormIDSection($value) { 
        $this->resetLevels(1);
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
    }
    public function updatedFormIDObj1($value) { 
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
    }
    // ... (Même logique pour Obj2, 3, 4 que dans Engagement)
    public function updatedFormIDObj2($value) {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
    }
    public function updatedFormIDObj3($value) {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
    }
    public function updatedFormIDObj4($value) {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
    }

    private function resetLevels($fromLevel) {
        if($fromLevel <= 1) { $this->form['IDObj1'] = ''; $this->listeObj2 = []; }
        if($fromLevel <= 2) { $this->form['IDObj2'] = ''; $this->listeObj3 = []; }
        if($fromLevel <= 3) { $this->form['IDObj3'] = ''; $this->listeObj4 = []; }
        if($fromLevel <= 4) { $this->form['IDObj4'] = ''; $this->listeObj5 = []; }
        if($fromLevel <= 5) { $this->form['IDObj5'] = ''; }
    }

    public function openModal() {
        $this->resetValidation();
        $this->reset('form', 'listeObj1', 'listeObj2', 'listeObj3');
        $this->form['date_perception'] = date('Y-m-d');
        $this->form['EXERCICE'] = date('Y');
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
            'form.date_perception' => 'required|date',
        ]);

        BdgOperationRecette::create(array_merge($this->form, [
            'IDObj2' => $this->form['IDObj2'] ?: 0,
            'IDObj3' => $this->form['IDObj3'] ?: 0,
            'IDObj4' => $this->form['IDObj4'] ?: 0,
            'IDObj5' => $this->form['IDObj5'] ?: 0,
            'NumFournisseur' => $this->form['NumFournisseur'] ?: null,
            'Creer_le' => now(),
            'IDLogin' => auth()->id() ?? 0
        ]));

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id)
    {
        BdgOperationRecette::findOrFail($id)->delete();
        session()->flash('success', __('crud.item_deleted'));
    }

    public function with()
    {
        $query = BdgOperationRecette::with(['budget', 'section', 'obj1', 'obj2', 'tiers'])
            ->orderByDesc('date_perception');

        if ($this->filterSection) $query->where('IDSection', $this->filterSection);
        if ($this->filterObj1) $query->where('IDObj1', $this->filterObj1);
        if ($this->filterSearch) $query->where('designation', 'like', '%'.$this->filterSearch.'%');

        return [
            'recettes' => $query->paginate(10)
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
            <i class="fas fa-hand-holding-usd text-success {{ $margin }}"></i>{{ __('operations.revenue') }}
        </h4>
        <button wire:click="openModal" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('operations.new_revenue') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="icon fas fa-check {{ $margin }}"></i> {{ session('success') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.revenue_history') }}</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" wire:model.live="filterSearch" class="form-control float-right" placeholder="{{ __('crud.search') }}">
                    <div class="input-group-append"><button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button></div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>{{ __('operations.collection_date') }}</th>
                        <th>{{ __('crud.designation') }}</th>
                        <th>{{ __('operations.payer') }}</th>
                        <th>{{ __('menu.nomenclature') }}</th>
                        <th class="text-right">{{ __('operations.revenue_amount') }}</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recettes as $op)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($op->date_perception)->format('d/m/Y') }}</td>
                        <td class="font-weight-bold">{{ $op->designation }}</td>
                        <td>{{ $op->tiers->Nom ?? ($op->tiers->Societe ?? '-') }}</td>
                        <td class="small text-muted">
                            <span class="text-success font-weight-bold">{{ $op->section->Num_section ?? '' }}</span> / 
                            {{ $op->obj1->Num ?? '' }}
                            @if($op->IDObj2) / {{ $op->obj2->Num ?? '' }} @endif
                        </td>
                        <td class="text-right font-weight-bold text-success" style="font-size: 1.1em;">
                            <span dir="ltr">+ {{ number_format($op->Mont_operation, 2, ',', ' ') }} DA</span>
                        </td>
                        <td class="text-right">
                            <button wire:click="delete({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-outline-danger" onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()">
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
        <div class="card-footer clearfix"><div class="float-right">{{ $recettes->links() }}</div></div>
    </div>

    <!-- MODAL -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title {{ $alignText }} w-100">{{ __('operations.new_revenue') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
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
                            <label class="{{ $alignText }} d-block">{{ __('menu.chapters') }} (OBJ1)</label>
                            <select wire:model.live="form.IDObj1" class="form-control font-weight-bold" {{ empty($listeObj1) ? 'disabled' : '' }}><option value="">{{ __('crud.select_option') }}</option>@foreach($listeObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option> @endforeach</select>
                        </div>
                        
                        @if($maxLevel >= 2)
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('menu.articles') }} (OBJ2)</label>
                            <select wire:model.live="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}><option value="">-- Global --</option>@foreach($listeObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option> @endforeach</select>
                        </div>
                        @endif
                        
                        {{-- Ajoutez Obj3, 4, 5 ici comme pour l'engagement si besoin --}}

                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('crud.designation') }}</label>
                                <input type="text" wire:model="form.designation" class="form-control" placeholder="Source de la recette">
                            </div>
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('operations.collection_date') }}</label>
                                <input type="date" wire:model="form.date_perception" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label class="{{ $alignText }} d-block">{{ __('operations.payer') }} (Optionnel)</label>
                            <select wire:model="form.NumFournisseur" class="form-control">
                                <option value="">-- Aucun --</option>
                                @foreach($tiers as $t) <option value="{{ $t->NumFournisseur }}">{{ $t->Nom }} {{ $t->Societe }}</option> @endforeach
                            </select>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4 offset-md-8">
                                <label class="{{ $alignText }} d-block text-success font-weight-bold">{{ __('operations.revenue_amount') }} (DA)</label>
                                <input type="number" step="0.01" wire:model="form.Mont_operation" class="form-control form-control-lg border-success text-right font-weight-bold" dir="ltr">
                                @error('form.Mont_operation') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>