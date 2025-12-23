<?php

use Livewire\Volt\Component;
use App\Models\BdgCompte;
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
        'IDBdg_Compte' => '',
        'Num_Compte' => '',
        'designation' => '',
        'EXERCICE' => '',
        'dep_recette' => 0, // Par défaut Dépense (0)
    ];

    public function mount() {
        $this->form['EXERCICE'] = date('Y');
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id && is_numeric($id)) {
            $this->editMode = true;
            $compte = BdgCompte::findOrFail($id);
            $this->form = $compte->toArray();
        } else {
            $this->editMode = false;
            $this->form['EXERCICE'] = date('Y');
            $this->form['dep_recette'] = 0;
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.Num_Compte' => 'required|string|max:20',
            'form.designation' => 'required|string|max:50',
            'form.EXERCICE' => 'required|integer|min:2000|max:2100',
            'form.dep_recette' => 'required|boolean', // 0 ou 1
        ]);

        $data = $this->form;
        
        if ($this->editMode) {
            BdgCompte::where('IDBdg_Compte', $this->form['IDBdg_Compte'])->update([
                'Num_Compte' => $data['Num_Compte'],
                'designation' => $data['designation'],
                'EXERCICE' => $data['EXERCICE'],
                'dep_recette' => $data['dep_recette'],
            ]);
        } else {
            unset($data['IDBdg_Compte']); // Auto-increment supposé
            BdgCompte::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Compte enregistré avec succès.');
    }

    public function delete($id)
    {
        try {
            BdgCompte::findOrFail($id)->delete();
            session()->flash('success', 'Compte supprimé.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer ce compte.');
        }
    }

    public function with()
    {
        return [
            'comptes' => BdgCompte::where('Num_Compte', 'like', "%{$this->search}%")
                    ->orWhere('designation', 'like', "%{$this->search}%")
                    ->orderBy('Num_Compte')
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Comptes Budgétaires</h3>
            <p class="text-muted small m-0">Plan comptable et imputation</p>
        </div>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-file-invoice me-2"></i>Nouveau Compte
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
                <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Rechercher (Numéro, Nom)...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Numéro</th>
                        <th>Désignation</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Exercice</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comptes as $c)
                    <tr class="border-bottom">
                        <td class="ps-4"><span class="badge bg-dark text-white border px-3 rounded-pill">{{ $c->Num_Compte }}</span></td>
                        <td class="fw-bold text-dark">{{ $c->designation }}</td>
                        <td class="text-center">
                            @if($c->dep_recette == 1)
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-arrow-up me-1"></i> Recette</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning"><i class="fas fa-arrow-down me-1"></i> Dépense</span>
                            @endif
                        </td>
                        <td class="text-center text-muted small fw-bold">{{ $c->EXERCICE }}</td>
                        <td class="text-end pe-4">
                            <button wire:click="openModal({{ $c->IDBdg_Compte }})" class="btn btn-sm btn-light text-primary me-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $c->IDBdg_Compte }})" 
                                    onclick="confirm('Supprimer ce compte ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-light text-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucun compte trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $comptes->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">{{ $editMode ? 'Modifier' : 'Ajouter' }} un Compte</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Numéro de Compte</label>
                            <input type="text" wire:model="form.Num_Compte" class="form-control form-control-lg" placeholder="Ex: 623">
                            @error('form.Num_Compte') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Désignation</label>
                            <input type="text" wire:model="form.designation" class="form-control" placeholder="Ex: Publicité et Annonces">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Exercice</label>
                                <input type="number" wire:model="form.EXERCICE" class="form-control text-center">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Type</label>
                                <select wire:model="form.dep_recette" class="form-select text-center fw-bold {{ $form['dep_recette'] == 1 ? 'text-success' : 'text-warning' }}">
                                    <option value="0" class="text-warning">Dépense</option>
                                    <option value="1" class="text-success">Recette</option>
                                </select>
                            </div>
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