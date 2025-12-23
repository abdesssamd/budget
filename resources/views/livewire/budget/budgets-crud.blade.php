<?php

use Livewire\Volt\Component;
use App\Models\BdgBudget;
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
        'IDBudjet' => '',
        'Reference' => '',
        'designation' => '',
        'EXERCICE' => '', 
        'Archive' => false,
    ];

    public function mount() {
        $this->form['EXERCICE'] = date('Y');
    }

    // On sécurise l'ID pour être sûr qu'il est numérique
    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id && is_numeric($id)) {
            $this->editMode = true;
            $budget = BdgBudget::findOrFail($id);
            $this->form = $budget->toArray();
            $this->form['Archive'] = (bool) $budget->Archive;
        } else {
            $this->editMode = false;
            $this->form['EXERCICE'] = date('Y');
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.Reference' => 'nullable|string|max:20',
            'form.designation' => 'required|string|max:50',
            'form.EXERCICE' => 'required|integer|min:2000|max:2100',
            'form.Archive' => 'boolean',
        ]);

        $data = $this->form;
        
        if ($this->editMode) {
            BdgBudget::where('IDBudjet', $this->form['IDBudjet'])->update([
                'Reference' => $data['Reference'],
                'designation' => $data['designation'],
                'EXERCICE' => $data['EXERCICE'],
                'Archive' => $data['Archive'] ? 1 : 0,
            ]);
        } else {
            unset($data['IDBudjet']);
            $data['Archive'] = $data['Archive'] ? 1 : 0;
            BdgBudget::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Budget enregistré.');
    }

    public function delete($id)
    {
        try {
            BdgBudget::findOrFail($id)->delete();
            session()->flash('success', 'Budget supprimé.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer ce budget (données liées).');
        }
    }

    public function with()
    {
        return [
            'budgets' => BdgBudget::where('designation', 'like', "%{$this->search}%")
                    ->orderByDesc('EXERCICE')
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Gestion des Budgets</h3>
            <p class="text-muted small m-0">Définition des exercices budgétaires</p>
        </div>
        <!-- CORRECTION ICI : Ajout des parenthèses () -->
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-coins me-2"></i>Nouveau Budget
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
                <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Chercher...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Année</th>
                        <th>Désignation</th>
                        <th>Référence</th>
                        <th class="text-center">État</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($budgets as $b)
                    <tr class="border-bottom {{ $b->Archive ? 'bg-light text-muted' : '' }}">
                        <td class="ps-4"><span class="badge bg-primary bg-opacity-10 text-primary border px-3">{{ $b->EXERCICE }}</span></td>
                        <td class="fw-bold">{{ $b->designation }}</td>
                        <td>{{ $b->Reference }}</td>
                        <td class="text-center">
                            @if($b->Archive)
                                <span class="badge bg-secondary"><i class="fas fa-archive me-1"></i> Archivé</span>
                            @else
                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Actif</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button wire:click="openModal({{ $b->IDBudjet }})" class="btn btn-sm btn-light text-primary me-1"><i class="fas fa-edit"></i></button>
                            @if(!$b->Archive)
                            <button wire:click="delete({{ $b->IDBudjet }})" 
                                    onclick="confirm('Supprimer ce budget ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-light text-danger"><i class="fas fa-trash-alt"></i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucun budget défini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $budgets->links() }}
        </div>
    </div>

    @if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">{{ $editMode ? 'Modifier' : 'Nouveau' }} Budget</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Exercice</label>
                                <input type="number" wire:model="form.EXERCICE" class="form-control text-center fw-bold" min="2000" max="2100">
                                @error('form.EXERCICE') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Référence</label>
                                <input type="text" wire:model="form.Reference" class="form-control" placeholder="Ex: REF-2024-01">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Désignation</label>
                            <input type="text" wire:model="form.designation" class="form-control form-control-lg" placeholder="Ex: Budget Primitif 2025">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-check form-switch mt-3 p-3 bg-light rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" wire:model="form.Archive" id="checkArchive">
                            <label class="form-check-label fw-bold text-secondary" for="checkArchive">
                                Clôturer / Archiver ce budget ?
                            </label>
                            <div class="small text-muted mt-1 ms-4">Si coché, ce budget ne sera plus proposé dans les saisies.</div>
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