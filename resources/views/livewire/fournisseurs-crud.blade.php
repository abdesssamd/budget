<div class="container-fluid py-4" style="background-color: #f8f9fa;">

    <!-- Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark m-0">
                <i class="fas fa-user-tie me-2 text-primary"></i> Gestion des Fournisseurs
            </h3>
            <small class="text-muted">Ajouter, modifier ou supprimer un fournisseur</small>
        </div>
    </div>

    <div class="row g-4">

        <!-- LEFT PANEL : FORM -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                <div class="card-body p-4">

                    <h5 class="card-title fw-bold mb-4 {{ $updateMode ? 'text-warning' : 'text-primary' }}">
                        <i class="{{ $updateMode ? 'fas fa-edit' : 'fas fa-plus-circle' }} me-2"></i>
                        {{ $updateMode ? 'Modifier le Fournisseur' : 'Nouveau Fournisseur' }}
                    </h5>

                    <form wire:submit.prevent="{{ $updateMode ? 'update' : 'store' }}">

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="Nom" class="form-control bg-light border-0" placeholder="Nom">
                            <label>Nom</label>
                            @error('Nom') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="Societe" class="form-control bg-light border-0" placeholder="Société">
                            <label>Société</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="Adresse" class="form-control bg-light border-0" placeholder="Adresse">
                            <label>Adresse</label>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="form-floating mb-3">
                                    <input type="text" wire:model="Telephone" class="form-control bg-light border-0" placeholder="Téléphone">
                                    <label>Téléphone</label>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-floating mb-3">
                                    <input type="text" wire:model="Mobile" class="form-control bg-light border-0" placeholder="Mobile">
                                    <label>Mobile</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" wire:model="Email" class="form-control bg-light border-0" placeholder="Email">
                            <label>Email</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="Ville" class="form-control bg-light border-0" placeholder="Ville">
                            <label>Ville</label>
                        </div>

                        <hr class="my-3">

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="num_carte_fiscale" class="form-control bg-light border-0" placeholder="Carte fiscale">
                            <label>Carte Fiscale *</label>
                            @error('num_carte_fiscale') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" wire:model="num_registre_commerce" class="form-control bg-light border-0" placeholder="Registre commerce">
                            <label>Registre Commerce *</label>
                            @error('num_registre_commerce') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-floating mb-3">
                            <textarea wire:model="Observations" class="form-control bg-light border-0" style="height: 100px" placeholder="Observations"></textarea>
                            <label>Observations</label>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-lg {{ $updateMode ? 'btn-warning text-white' : 'btn-primary' }} rounded-3 shadow-sm">
                                <i class="fas fa-save me-2"></i>
                                {{ $updateMode ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>

                            @if($updateMode)
                            <button type="button" class="btn btn-light text-muted" wire:click="resetFields">
                                <i class="fas fa-times me-2"></i> Annuler
                            </button>
                            @endif
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- RIGHT PANEL : TABLE LIST -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                    <!-- Search -->
                    <div class="input-group mb-4 shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-0 py-3" placeholder="Rechercher un fournisseur...">
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="border-0 ps-3">Fournisseur</th>
                                    <th class="border-0">Société</th>
                                    <th class="border-0">Contact</th>
                                    <th class="border-0 text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fournisseurs as $f)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $f->Nom }}</div>
                                        <div class="small text-muted">{{ $f->Email }}</div>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                            <i class="fas fa-building me-1"></i> {{ $f->Societe ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="small">
                                            <i class="fas fa-phone-alt text-success me-1"></i>{{ $f->Telephone ?? '—' }}
                                            <br>
                                            <i class="fas fa-mobile-alt text-info me-1"></i>{{ $f->Mobile ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light text-primary" wire:click="edit({{ $f->NumFournisseur }})">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>

                                            <button class="btn btn-sm btn-light text-danger"
                                                wire:click="delete({{ $f->NumFournisseur }})"
                                                onclick="confirm('Supprimer ce fournisseur ?') || event.stopImmediatePropagation()">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i>
                                        <p>Aucun fournisseur trouvé.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $fournisseurs->links() }}
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
