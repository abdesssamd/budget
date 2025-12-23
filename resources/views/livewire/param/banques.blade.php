<?php

use Livewire\Volt\Component;
use App\Models\ParamBanque;
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
        'IDParam_banq' => '',
        'Banq' => '',
        'ABV' => '',
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id && is_numeric($id)) {
            $this->editMode = true;
            $banque = ParamBanque::findOrFail($id);
            $this->form = $banque->toArray();
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.Banq' => 'required|string|max:50',
            'form.ABV' => 'nullable|string|max:10',
        ]);

        $data = $this->form;
        
        if ($this->editMode) {
            ParamBanque::where('IDParam_banq', $this->form['IDParam_banq'])->update([
                'Banq' => $data['Banq'],
                'ABV' => $data['ABV'],
            ]);
        } else {
            unset($data['IDParam_banq']); // Auto-increment
            ParamBanque::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Banque enregistrée avec succès.');
    }

    public function delete($id)
    {
        try {
            ParamBanque::findOrFail($id)->delete();
            session()->flash('success', 'Banque supprimée.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer cette banque.');
        }
    }

    public function with()
    {
        return [
            'banques' => ParamBanque::where('Banq', 'like', "%{$this->search}%")
                    ->orWhere('ABV', 'like', "%{$this->search}%")
                    ->orderBy('Banq')
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Gestion des Banques</h3>
            <p class="text-muted small m-0">Liste des établissements bancaires</p>
        </div>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-university me-2"></i>Nouvelle Banque
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
                <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Rechercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nom de la Banque</th>
                        <th>Abréviation</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banques as $b)
                    <tr class="border-bottom">
                        <td class="ps-4 fw-bold text-muted">#{{ $b->IDParam_banq }}</td>
                        <td class="fw-bold text-dark">{{ $b->Banq }}</td>
                        <td><span class="badge bg-info bg-opacity-10 text-dark border">{{ $b->ABV }}</span></td>
                        <td class="text-end pe-4">
                            <button wire:click="openModal({{ $b->IDParam_banq }})" class="btn btn-sm btn-light text-primary me-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $b->IDParam_banq }})" 
                                    onclick="confirm('Supprimer cette banque ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-light text-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucune banque trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $banques->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">{{ $editMode ? 'Modifier' : 'Ajouter' }} une Banque</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Nom de la Banque</label>
                            <input type="text" wire:model="form.Banq" class="form-control form-control-lg" placeholder="Ex: Banque Nationale d'Algérie">
                            @error('form.Banq') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Abréviation / Code</label>
                            <input type="text" wire:model="form.ABV" class="form-control" placeholder="Ex: BNA">
                            @error('form.ABV') <span class="text-danger small">{{ $message }}</span> @enderror
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