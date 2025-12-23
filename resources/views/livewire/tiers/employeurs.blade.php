<?php

use Livewire\Volt\Component;
use App\Models\ParamEmployeur;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public bool $showModal = false;
    public bool $editMode = false;

    public array $form = [
        'IDParam_Employeur' => '',
        'Code' => '',
        'designation' => '',
        'ABV' => '',
        'Jour_veressement' => '',
        'Adresse' => '',
        'Tel' => '',
        'Fax' => '',
        'EMail' => '',
        'Observations' => '',
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id && is_numeric($id)) {
            $this->editMode = true;
            $emp = ParamEmployeur::findOrFail($id);
            $this->form = $emp->toArray();
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.Code' => 'nullable|string|max:50',
            'form.designation' => 'required|string|max:50',
            'form.ABV' => 'nullable|string|max:5',
            'form.EMail' => 'nullable|email|max:40',
            'form.Tel' => 'nullable|string|max:20',
        ]);

        $data = $this->form;
        // Clean empty fields to avoid SQL errors if strict mode
        foreach ($data as $key => $value) {
            if ($value === '') $data[$key] = null;
        }

        if ($this->editMode) {
            ParamEmployeur::where('IDParam_Employeur', $this->form['IDParam_Employeur'])->update($data);
        } else {
            unset($data['IDParam_Employeur']);
            ParamEmployeur::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Employeur enregistré.');
    }

    public function delete($id)
    {
        try {
            ParamEmployeur::findOrFail($id)->delete();
            session()->flash('success', 'Supprimé avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer cet enregistrement.');
        }
    }

    public function with()
    {
        return [
            'employeurs' => ParamEmployeur::where('designation', 'like', "%{$this->search}%")
                    ->orWhere('Code', 'like', "%{$this->search}%")
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Gestion des Employeurs</h3>
            <p class="text-muted small m-0">Liste des organismes employeurs</p>
        </div>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-building me-2"></i>Ajouter
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom-0 py-3">
            <div class="input-group w-50">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Rechercher (Nom, Code)...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Désignation</th>
                        <th>ABV</th>
                        <th>Contact</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employeurs as $e)
                    <tr class="border-bottom">
                        <td class="ps-4"><span class="badge bg-light text-dark border">{{ $e->Code }}</span></td>
                        <td class="fw-bold text-dark">{{ $e->designation }}</td>
                        <td>{{ $e->ABV }}</td>
                        <td class="small text-muted">
                            @if($e->Tel) <div><i class="fas fa-phone me-1"></i> {{ $e->Tel }}</div> @endif
                            @if($e->EMail) <div><i class="fas fa-envelope me-1"></i> {{ $e->EMail }}</div> @endif
                        </td>
                        <td class="text-end pe-4">
                            <button wire:click="openModal({{ $e->IDParam_Employeur }})" class="btn btn-sm btn-light text-primary me-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $e->IDParam_Employeur }})" 
                                    onclick="confirm('Confirmer suppression ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-light text-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucun résultat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $employeurs->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">{{ $editMode ? 'Modifier' : 'Ajouter' }} un Employeur</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-muted">Code</label>
                                <input type="text" wire:model="form.Code" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-muted">Abréviation (ABV)</label>
                                <input type="text" wire:model="form.ABV" class="form-control" placeholder="EX: SARL">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold text-muted">Jour Versement</label>
                                <input type="number" wire:model="form.Jour_veressement" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Désignation *</label>
                            <input type="text" wire:model="form.designation" class="form-control form-control-lg">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Téléphone</label>
                                <input type="text" wire:model="form.Tel" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Email</label>
                                <input type="email" wire:model="form.EMail" class="form-control">
                                @error('form.EMail') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Adresse</label>
                            <textarea wire:model="form.Adresse" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Observations</label>
                            <textarea wire:model="form.Observations" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light">
                        <button type="button" class="btn btn-light text-muted" wire:click="closeModal">Annuler</button>
                        <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>