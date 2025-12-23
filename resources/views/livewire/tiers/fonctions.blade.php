<?php

use Livewire\Volt\Component;
use App\Models\ParamFonction;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    use WithFileUploads; 

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public bool $showModal = false;
    public bool $showImportModal = false; 
    public bool $editMode = false;
    
    public $importFile;

    public array $form = [
        'IDParam_fonction' => '',
        'designation' => '',
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        // On vérifie si un ID est passé ET s'il est numérique
        if ($id && is_numeric($id)) {
            $this->editMode = true;
            // On utilise findOrFail pour être sûr de récupérer la donnée
            $fonction = ParamFonction::findOrFail($id);
            $this->form = $fonction->toArray();
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function openImportModal()
    {
        $this->resetValidation();
        $this->importFile = null;
        $this->showImportModal = true;
    }

    public function closeModal() { 
        $this->showModal = false; 
        $this->showImportModal = false;
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|max:10240', 
        ]);

        $path = $this->importFile->getRealPath();
        $file = fopen($path, 'r');
        
        $count = 0;
        
        while (($line = fgetcsv($file, 1000, ";")) !== FALSE) { 
            if (count($line) == 1 && strpos($line[0], ',') !== false) {
                $line = explode(',', $line[0]);
            }

            $designation = $line[0] ?? '';
            $designation = preg_replace('/^\xEF\xBB\xBF/', '', $designation);
            $designation = trim($designation);
            $designation = mb_substr($designation, 0, 50);

            if (empty($designation) || strtolower($designation) == 'designation' || strtolower($designation) == 'libelle') {
                continue;
            }

            $exists = ParamFonction::where('designation', $designation)->exists();

            if (!$exists) {
                ParamFonction::create(['designation' => $designation]);
                $count++;
            }
        }

        fclose($file);

        $this->closeModal();
        session()->flash('success', "$count fonctions importées avec succès !");
    }

    public function save()
    {
        $this->validate([
            'form.designation' => 'required|string|max:50',
        ]);

        if ($this->editMode) {
            ParamFonction::where('IDParam_fonction', $this->form['IDParam_fonction'])->update([
                'designation' => $this->form['designation'],
            ]);
        } else {
            $data = $this->form;
            unset($data['IDParam_fonction']); 
            ParamFonction::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Fonction enregistrée avec succès.');
    }

    public function delete($id)
    {
        try {
            ParamFonction::findOrFail($id)->delete();
            session()->flash('success', 'Fonction supprimée.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer cette fonction (liée à un tiers).');
        }
    }

    public function with()
    {
        return [
            'fonctions' => ParamFonction::where('designation', 'like', "%{$this->search}%")
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">Gestion des Fonctions</h3>
            <p class="text-muted small m-0">Postes et Titres RH</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="openImportModal()" class="btn btn-success shadow-sm text-white">
                <i class="fas fa-file-excel me-2"></i>Importer CSV
            </button>
            <button wire:click="openModal()" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-2"></i>Nouvelle Fonction
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Carte Tableau -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom-0 py-3">
            <div class="input-group w-50">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Rechercher une fonction...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Désignation</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fonctions as $f)
                    <!-- CORRECTION MAJEURE ICI : Ajout de wire:key -->
                    <tr class="border-bottom" wire:key="func-{{ $f->IDParam_fonction }}">
                        <td class="ps-4 text-muted">#{{ $f->IDParam_fonction }}</td>
                        <td class="fw-bold text-dark">{{ $f->designation }}</td>
                        <td class="text-end pe-4">
                            <!-- CORRECTION : Ajout de .prevent -->
                            <button wire:click.prevent="openModal({{ $f->IDParam_fonction }})" class="btn btn-sm btn-light text-primary me-1"><i class="fas fa-edit"></i></button>
                            <button wire:click.prevent="delete({{ $f->IDParam_fonction }})" 
                                    onclick="confirm('Êtes-vous sûr de vouloir supprimer ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-light text-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="fas fa-briefcase fa-3x mb-3 opacity-25"></i>
                            <p class="m-0">Aucune fonction trouvée.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $fonctions->links() }}
        </div>
    </div>

    <!-- MODAL EDITION / AJOUT -->
    @if($showModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">
                        {{ $editMode ? 'Modifier' : 'Ajouter' }} une Fonction
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Intitulé de la fonction</label>
                            <input type="text" wire:model="form.designation" class="form-control form-control-lg" placeholder="Ex: Directeur Général">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
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

    <!-- MODAL IMPORTATION CSV -->
    @if($showImportModal)
    <div class="modal-backdrop fade show" style="z-index: 1050;"></div>
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-success text-white border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-csv me-2"></i>Importer des Fonctions</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                
                <form wire:submit.prevent="import">
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 d-flex align-items-center mb-4">
                            <i class="fas fa-info-circle fa-2x me-3 opacity-50"></i>
                            <div>
                                <strong>Format attendu :</strong><br>
                                Fichier CSV (Excel > Enregistrer sous > CSV UTF-8).<br>
                                Une seule colonne avec le nom de la fonction.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Sélectionner le fichier CSV</label>
                            <input type="file" wire:model="importFile" class="form-control form-control-lg" accept=".csv, .txt">
                            @error('importFile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Chargement en cours -->
                        <div wire:loading wire:target="importFile" class="text-muted small">
                            <i class="fas fa-spinner fa-spin me-1"></i> Chargement du fichier...
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top-0 bg-light">
                        <button type="button" class="btn btn-light text-muted" wire:click="closeModal">Annuler</button>
                        <button type="submit" class="btn btn-success fw-bold" wire:loading.attr="disabled" wire:target="importFile, import">
                            <i class="fas fa-upload me-2"></i>Lancer l'import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>