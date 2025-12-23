<?php

use Livewire\Volt\Component;
use App\Models\BdgBudget;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    public $budgets = [];
    public $showModal = false;
    
    public array $form = [
        'IDBudjet' => '',
        'designation' => '',
        'Montant_Global' => 0,
    ];

    public function mount() {
        $this->refreshList();
    }

    public function refreshList() {
        $this->budgets = BdgBudget::where('Archive', 0)->orderByDesc('EXERCICE')->get();
    }

    public function openModal($id) {
        $b = BdgBudget::findOrFail($id);
        $this->form = [
            'IDBudjet' => $b->IDBudjet,
            'designation' => $b->designation,
            'Montant_Global' => $b->Montant_Global,
        ];
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save() {
        $this->validate([
            'form.Montant_Global' => 'required|numeric|min:0'
        ]);

        $budget = BdgBudget::findOrFail($this->form['IDBudjet']);
        
        // Calcul du différentiel pour ajuster le reste
        // Si on augmente le global, le reste augmente. Si on diminue, il diminue.
        $diff = $this->form['Montant_Global'] - $budget->Montant_Global;
        
        $budget->Montant_Global = $this->form['Montant_Global'];
        $budget->Montant_Restant = $budget->Montant_Restant + $diff;
        
        $budget->save();

        $this->showModal = false;
        $this->refreshList();
        session()->flash('success', __('crud.success_op'));
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-sack-dollar text-primary mr-2"></i>Incorporation Globale
        </h4>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check mr-2"></i> {{ session('success') }} 
            <button class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        @foreach($budgets as $b)
        <div class="col-md-4">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header text-center bg-light">
                    <h3 class="card-title float-none font-weight-bold">{{ $b->EXERCICE }} - {{ $b->designation }}</h3>
                </div>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Enveloppe Totale</h5>
                    <h2 class="text-primary font-weight-bold mb-3">{{ number_format($b->Montant_Global, 2, ',', ' ') }} DA</h2>
                    
                    <div class="progress mb-3" style="height: 10px;">
                        @php
                            $percent = $b->Montant_Global > 0 ? (1 - ($b->Montant_Restant / $b->Montant_Global)) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%"></div>
                    </div>

                    <div class="row border-top pt-2">
                        <div class="col-6 border-right">
                            <span class="text-success font-weight-bold d-block small">Disponible</span>
                            <span>{{ number_format($b->Montant_Restant, 2, ',', ' ') }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-danger font-weight-bold d-block small">Distribué</span>
                            <span>{{ number_format($b->Montant_Global - $b->Montant_Restant, 2, ',', ' ') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button wire:click="openModal({{ $b->IDBudjet }})" class="btn btn-block btn-outline-primary">
                        <i class="fas fa-edit mr-2"></i> Modifier l'enveloppe
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5   class="modal-title">Définir le montant : {{ $form['designation'] }}</h5>
                    <button type="button" class="close text-white" wire:click="closeModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Montant Global du Budget (DA)</label>
                        <input type="number" step="0.01" wire:model="form.Montant_Global" class="form-control form-control-lg text-right font-weight-bold text-primary">
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Le "Reste à distribuer" sera recalculé automatiquement.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('crud.cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="save">{{ __('crud.save') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>