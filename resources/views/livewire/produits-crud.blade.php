
<div class="container-fluid py-4" style="background-color: #f8f9fa;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0"><i class="fas fa-boxes me-2 text-primary"></i>Gestion des Produits</h3>
            <small class="text-muted">Gérez votre inventaire et vos réapprovisionnements</small>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4 {{ $updateMode ? 'text-warning' : 'text-primary' }}">
                        <i class="{{ $updateMode ? 'fas fa-edit' : 'fas fa-plus-circle' }} me-2"></i>
                        {{ $updateMode ? 'Modifier le Produit' : 'Nouveau Produit' }}
                    </h5>

                    <form wire:submit.prevent="{{ $updateMode ? 'update' : 'store' }}">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-light border-0" id="libelle" wire:model="LibProd" placeholder="Nom du produit">
                            <label for="libelle">Libellé du produit</label>
                            @error('LibProd') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-light border-0" id="ref" wire:model="Reference" placeholder="REF-123">
                            <label for="ref">Référence</label>
                            @error('Reference') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control bg-light border-0" id="qteR" wire:model="QteReappro" placeholder="0">
                                    <label for="qteR">Qté Réappro</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control bg-light border-0" id="qteM" wire:model="QteMini" placeholder="0">
                                    <label for="qteM">Seuil Min</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea class="form-control bg-light border-0" id="desc" wire:model="Description" style="height: 100px" placeholder="Détails"></textarea>
                            <label for="desc">Description (Optionnel)</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg {{ $updateMode ? 'btn-warning text-white' : 'btn-primary' }} rounded-3 shadow-sm">
                                <i class="fas fa-save me-2"></i>{{ $updateMode ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>
                            
                            @if($updateMode)
                            <button type="button" class="btn btn-light text-muted border-0" wire:click="resetFields">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    
                    <div class="input-group mb-4 shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control border-0 py-3" placeholder="Rechercher par référence, nom...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="border-0 rounded-start ps-3">Produit</th>
                                    <th class="border-0">Stock Config</th>
                                    <th class="border-0 text-end rounded-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($produits as $p)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $p->LibProd }}</div>
                                                <div class="small text-muted"><i class="fas fa-barcode me-1"></i>{{ $p->Reference }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border me-1" title="Seuil Minimum">
                                            <i class="fas fa-arrow-down text-danger me-1"></i>Min: {{ $p->QteMini }}
                                        </span>
                                        <span class="badge bg-light text-dark border" title="Quantité Réappro">
                                            <i class="fas fa-sync text-success me-1"></i>Réap: {{ $p->QteReappro }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light text-primary hover-shadow" wire:click="edit({{ $p->id_produit }})" title="Modifier">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light text-danger hover-shadow" wire:click="delete({{ $p->id_produit }})" 
                                                    onclick="confirm('Êtes-vous sûr ?') || event.stopImmediatePropagation()" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                        <p>Aucun produit trouvé.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $produits->links() }} 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>