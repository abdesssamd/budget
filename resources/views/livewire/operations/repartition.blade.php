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
    
    // Configuration Générale
    public $maxLevel = 1; 
    
    // Données pour les listes
    public $budgets = [];
    public $sections = [];
    
    // Listes en cascade
    public $listeObj1 = []; 
    public $listeObj2 = []; 
    public $listeObj3 = []; 
    public $listeObj4 = []; 
    public $listeObj5 = []; 

    public $selectedBudgetRestant = 0;
    public $scanFile; 

    public array $form = [
        'IDBudjet' => '', 'IDSection' => '', 
        'IDObj1' => '', 'IDObj2' => '', 'IDObj3' => '', 'IDObj4' => '', 'IDObj5' => '',
        'designation' => '', 'Mont_operation' => 0, 'EXERCICE' => '',
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
        
        $defaultLabel = __('operations.repartition_default_label');
        if($defaultLabel === 'operations.repartition_default_label') $defaultLabel = 'Répartition Budget';
        
        $this->form['designation'] = $defaultLabel;
    }

    public function updatedFormIDBudjet($value) {
        $b = $this->budgets->find($value);
        if($b) { $this->form['EXERCICE'] = $b->EXERCICE; $this->selectedBudgetRestant = $b->Montant_Restant; }
    }

    // --- CASCADE DYNAMIQUE ---

    public function updatedFormIDSection($value) {
        $this->resetLevels(1); 
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
    }

    public function updatedFormIDObj1($value) {
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) {
            $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj2($value) {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) {
            $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj3($value) {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) {
            $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
        }
    }

    public function updatedFormIDObj4($value) {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) {
            $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
        }
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
        $this->form['Mont_operation'] = 0;
        $this->showModal = true;
    }

    // --- VISA ---

    public function openVisaModal($opId) {
        $this->resetValidation();
        $this->scanFile = null;
        $visa = BdgCf::where('IDOperation_Budg', $opId)->first();
        $this->visaForm = $visa ? $visa->toArray() : [
            'IDOperation_Budg' => $opId, 'Date_envoi' => date('Y-m-d'), 
            'VISA_cf' => '', 'Date_retour' => date('Y-m-d'), 'Observations' => '', 'scan_path' => ''
        ];
        $this->showVisaModal = true;
    }

    public function saveVisa() {
        $this->validate([
            'visaForm.Date_envoi' => 'required|date',
            'scanFile' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);
        
        $filePath = $this->visaForm['scan_path'];
        if ($this->scanFile) $filePath = $this->scanFile->store('visas', 'public');

        BdgCf::updateOrCreate(
            ['IDOperation_Budg' => $this->visaForm['IDOperation_Budg']],
            array_merge($this->visaForm, ['scan_path' => $filePath, 'IDLogin' => auth()->id() ?? 0, 'Creer_le' => now()])
        );
        $this->showVisaModal = false;
        session()->flash('success', __('messages.visa_saved'));
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

        $budget = BdgBudget::find($this->form['IDBudjet']);
        if ($this->form['Mont_operation'] > $budget->Montant_Restant) {
            $this->addError('form.Mont_operation', __('messages.insufficient_balance') ?? 'Solde insuffisant');
            return;
        }

        BdgOperationBudg::create([
            'Num_operation' => rand(10000,99999),
            'designation' => $this->form['designation'],
            'Mont_operation' => $this->form['Mont_operation'],
            'Type_operation' => 2,
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

        $budget->decrement('Montant_Restant', $this->form['Mont_operation']);
        $this->selectedBudgetRestant -= $this->form['Mont_operation'];
        $this->showModal = false;
        session()->flash('success', __('messages.repartition_success'));
    }

    public function delete($id) {
        $op = BdgOperationBudg::findOrFail($id);
        if($op->cf) $op->cf->delete();
        $budget = BdgBudget::find($op->IDBudjet);
        if($budget) $budget->increment('Montant_Restant', $op->Mont_operation);
        $op->delete();
        session()->flash('success', __('messages.repartition_cancelled'));
    }

    public function with() {
        return [
            // Eager loading complet des 5 niveaux
            'operations' => BdgOperationBudg::with(['budget', 'section', 'obj1', 'obj2', 'obj3', 'obj4', 'obj5', 'cf'])
                ->where('Type_operation', 2)
                ->orderByDesc('Creer_le')
                ->paginate(10)
        ];
    }
}; ?>

<div>
    @section('plugins.Select2', true)

    {{-- Helpers RTL --}}
    @php
        $isRtl = app()->getLocale() == 'ar';
        $alignText = $isRtl ? 'text-right' : 'text-left';
        $margin = $isRtl ? 'ml-2' : 'mr-2';
        $closeBtnStyle = $isRtl ? 'margin: -1rem auto -1rem -1rem; float:left;' : '';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-chart-pie text-info mr-2"></i>{{ __('operations.repartition') }}
        </h4>
        <button wire:click="openModal" class="btn btn-info shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>{{ __('operations.new_repartition') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check mr-2"></i> {{ session('success') }} 
            <button class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.history') ?? 'Historique' }}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>{{ __('menu.sections') }}</th>
                        <th>{{ __('crud.designation') }} ({{ __('menu.nomenclature') }})</th>
                        <th class="text-right">{{ __('operations.amount') }}</th>
                        <th class="text-center">{{ __('visa.status') }}</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operations as $op)
                    <tr>
                        <td class="font-weight-bold text-info">{{ $op->section->Num_section ?? '' }}</td>
                        <td>
                            <!-- Affichage en arborescence complète -->
                            <div class="text-bold text-dark">{{ $op->obj1->Num ?? '' }} - {{ $op->obj1->designation ?? '' }}</div>
                            
                            @if($op->IDObj2) 
                                <div class="text-muted small ml-2">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj2->Num ?? '' }} {{ $op->obj2->designation ?? '' }}
                                </div> 
                            @endif
                            @if($op->IDObj3) 
                                <div class="text-muted small ml-3">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj3->Num ?? '' }} {{ $op->obj3->designation ?? '' }}
                                </div> 
                            @endif
                            @if($op->IDObj4) 
                                <div class="text-muted small ml-4">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj4->Num ?? '' }} {{ $op->obj4->designation ?? '' }}
                                </div> 
                            @endif
                            @if($op->IDObj5) 
                                <div class="text-muted small ml-5">
                                    <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> {{ $op->obj5->Num ?? '' }} {{ $op->obj5->designation ?? '' }}
                                </div> 
                            @endif
                        </td>
                        <td class="text-right font-weight-bold text-info" style="font-size: 1.1em;">
                            <span dir="ltr">{{ number_format($op->Mont_operation, 2, ',', ' ') }} DA</span>
                        </td>
                        <td class="text-center">
                            @if($op->cf && $op->cf->VISA_cf)
                                <div class="badge badge-success px-2">
                                    <i class="fas fa-check mr-1"></i> {{ $op->cf->VISA_cf }}
                                </div>
                                @if($op->cf->scan_path) 
                                    <br>
                                    <a href="{{ Storage::url($op->cf->scan_path) }}" target="_blank" title="{{ __('visa.view_scan') }}">
                                        <i class="fas fa-paperclip mt-1 text-dark"></i>
                                    </a> 
                                @endif
                            @else
                                <span class="badge badge-light border">{{ __('visa.not_sent') }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="openVisaModal({{ $op->IDOperation_Budg }})" class="btn btn-xs btn-default border mr-1" title="{{ __('visa.financial_control') }}">
                                <i class="fas fa-stamp text-purple"></i>
                            </button>
                            <button wire:click="delete({{ $op->IDOperation_Budg }})" 
                                    class="btn btn-xs btn-outline-danger" 
                                    onclick="confirm('{{ __('messages.confirm_cancel_repartition') }}') || event.stopImmediatePropagation()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="float-right">{{ $operations->links() }}</div></div>
    </div>

    <!-- MODAL REPARTITION -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title {{ $alignText }} w-100">{{ __('operations.distribute_credits') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        @if($form['IDBudjet'])
                        <div class="alert alert-light border border-info text-center p-2 mb-3">
                            <span class="text-muted">{{ __('operations.available') }} :</span>
                            <h4 class="text-info font-weight-bold m-0" dir="ltr">{{ number_format($selectedBudgetRestant, 2, ',', ' ') }} DA</h4>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('menu.budgets') }}</label>
                                <select wire:model.live="form.IDBudjet" class="form-control font-weight-bold">
                                    <option value="">{{ __('crud.select_option') }}</option>
                                    @foreach($budgets as $b) <option value="{{ $b->IDBudjet }}">{{ $b->EXERCICE }} - {{ $b->designation }}</option> @endforeach
                                </select>
                                @error('form.IDBudjet') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('menu.sections') }}</label>
                                <select wire:model.live="form.IDSection" class="form-control">
                                    <option value="">{{ __('crud.select_option') }}</option>
                                    @foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option> @endforeach
                                </select>
                                @error('form.IDSection') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- LOGIQUE DYNAMIQUE DES NIVEAUX -->
                        
                        <!-- NIVEAU 1 (Obligatoire) -->
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('menu.chapters') }} (OBJ1)</label>
                            <select wire:model.live="form.IDObj1" class="form-control font-weight-bold" {{ empty($listeObj1) ? 'disabled' : '' }}>
                                <option value="">{{ empty($listeObj1) ? '...' : __('crud.select_option') }}</option>
                                @foreach($listeObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }} - {{ $o1->designation }}</option> @endforeach
                            </select>
                            @error('form.IDObj1') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- NIVEAU 2 -->
                        @if($maxLevel >= 2)
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('menu.articles') }} (OBJ2)</label>
                            <select wire:model.live="form.IDObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                <option value="">{{ __('crud.select_option') }}</option>
                                @foreach($listeObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }} - {{ $o2->designation }}</option> @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- NIVEAU 3 -->
                        @if($maxLevel >= 3 && !empty($form['IDObj2']))
                        <div class="form-group ml-4 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.sub_articles') }} (OBJ3)</label>
                            <select wire:model.live="form.IDObj3" class="form-control form-control-sm" {{ empty($listeObj3) ? 'disabled' : '' }}>
                                <option value="">{{ __('crud.select_option') }}</option>
                                @foreach($listeObj3 as $o3) <option value="{{ $o3->IDObj3 }}">{{ $o3->Num }} - {{ $o3->designation }}</option> @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- NIVEAU 4 -->
                        @if($maxLevel >= 4 && !empty($form['IDObj3']))
                        <div class="form-group ml-5 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.rubrics') }} (OBJ4)</label>
                            <select wire:model.live="form.IDObj4" class="form-control form-control-sm" {{ empty($listeObj4) ? 'disabled' : '' }}>
                                <option value="">{{ __('crud.select_option') }}</option>
                                @foreach($listeObj4 as $o4) <option value="{{ $o4->IDObj4 }}">{{ $o4->Num }} - {{ $o4->designation }}</option> @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- NIVEAU 5 -->
                        @if($maxLevel >= 5 && !empty($form['IDObj4']))
                        <div class="form-group ml-5 border-left pl-2">
                            <label class="{{ $alignText }} d-block">{{ __('menu.sub_rubrics') }} (OBJ5)</label>
                            <select wire:model.live="form.IDObj5" class="form-control form-control-sm" {{ empty($listeObj5) ? 'disabled' : '' }}>
                                <option value="">{{ __('crud.select_option') }}</option>
                                @foreach($listeObj5 as $o5) <option value="{{ $o5->IDObj5 }}">{{ $o5->Num }} - {{ $o5->designation }}</option> @endforeach
                            </select>
                        </div>
                        @endif

                        <hr>

                        <div class="row">
                            <div class="col-md-8">
                                <label class="{{ $alignText }} d-block">{{ __('crud.designation') }}</label>
                                <input type="text" wire:model="form.designation" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block text-info font-weight-bold">{{ __('operations.amount') }}</label>
                                <input type="number" step="0.01" wire:model="form.Mont_operation" class="form-control form-control-lg border-info text-right font-weight-bold" dir="ltr">
                                @error('form.Mont_operation') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-info">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL VISA -->
    @if($showVisaModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title {{ $alignText }} w-100"><i class="fas fa-stamp {{ $margin }}"></i>{{ __('visa.financial_control') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showVisaModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="saveVisa">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('visa.sent_date') }}</label>
                            <input type="date" wire:model="visaForm.Date_envoi" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('visa.number') }}</label><input type="text" wire:model="visaForm.VISA_cf" class="form-control font-weight-bold"></div>
                            <div class="col-md-6"><label class="{{ $alignText }} d-block">{{ __('visa.date') }}</label><input type="date" wire:model="visaForm.Date_retour" class="form-control"></div>
                        </div>
                        <div class="form-group mt-2">
                            <label class="{{ $alignText }} d-block">{{ __('visa.scan') }}</label>
                            <input type="file" wire:model="scanFile" class="form-control-file">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showVisaModal', false)">{{ __('crud.close') }}</button>
                        <button type="submit" class="btn bg-purple" wire:loading.attr="disabled">{{ __('visa.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>