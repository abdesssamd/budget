

<div class="container-fluid py-4">
   <!--div class="alert alert-warning">
    <b>DEBUG:</b> {{ json_encode($form) }}
</div---> 
    {{-- CARD PRINCIPALE --}}
    <div class="card shadow-sm border-0 rounded-3">
        
        {{-- HEADER DE LA CARTE --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="fas fa-list me-2"></i> {{ $modalTitle ?: 'Liste des données' }}
            </h5>
            
            <div class="d-flex gap-2">
                @if($canCreate)
                    {{-- CHANGEMENT ICI : wire:click="create" au lieu de href --}}
                    <button wire:click="create" class="btn btn-primary btn-sm px-3 shadow-sm">
                        <i class="fas fa-plus me-1"></i> Nouveau
                    </button>
                @endif
            </div>
        </div>

        {{-- BARRE D'OUTILS (Recherche & Filtres) --}}
        <div class="card-body border-bottom bg-light bg-opacity-25">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="form-control border-start-0 ps-0" 
                            placeholder="Rechercher..."
                        >
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <select wire:model.live="perPage" class="form-select d-inline-block w-auto form-select-sm">
                        <option value="10">10 lignes</option>
                        <option value="25">25 lignes</option>
                        <option value="50">50 lignes</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- TABLEAU --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        @foreach($columns as $field => $label)
                            <th scope="col" class="px-3 py-3" style="cursor: pointer;" wire:click="sortBy('{{ $field }}')">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-uppercase small fw-bold">{{ $label }}</span>
                                    @if($sortCol === $field)
                                        <i class="fas fa-sort-{{ $sortAsc ? 'up' : 'down' }} text-primary"></i>
                                    @else
                                        <i class="fas fa-sort text-muted opacity-25"></i>
                                    @endif
                                </div>
                            </th>
                        @endforeach

                        @if($canEdit || $canDelete)
                            <th scope="col" class="text-end px-3 py-3 text-uppercase small fw-bold">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($rows as $row)
                        <tr>
                            @foreach($columns as $field => $label)
                                <td class="px-3 py-3">
                                    @if($field === $primaryKey)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $row->$field }}</span>
                                    @else
                                        <span class="text-dark fw-500">{{ $row->$field }}</span>
                                    @endif
                                </td>
                            @endforeach

                            @if($canEdit || $canDelete)
                                <td class="text-end px-3">
                                    <div class="btn-group btn-group-sm">
                                        
                                        {{-- CHANGEMENT ICI : wire:click="edit" au lieu de href --}}
                                        @if($canEdit)
                                            <button wire:click="edit('{{ $row->$primaryKey }}')" 
                                                    class="btn btn-outline-info" 
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        
                                        @if($canDelete)
                                            <button 
                                                wire:click="delete('{{ $row->$primaryKey }}')"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer cet élément ?"
                                                class="btn btn-outline-danger" 
                                                title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0">Aucun enregistrement trouvé.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex justify-content-end">
                {{ $rows->links() }}
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- NOUVEAU : LE MODAL DYNAMIQUE BOOTSTRAP 5   --}}
    {{-- ========================================== --}}
    
    <div wire:ignore.self class="modal fade" id="universalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">{{ $modalTitle }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form wire:submit="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                                {{-- Génération automatique des champs --}}
                                @foreach($fields as $key => $config)
                                    
                                    {{-- IMPORTANT : On ajoute wire:key pour que Livewire ne s'embrouille pas --}}
                                    <div class="col-md-{{ $config['width'] ?? '12' }}" wire:key="field-{{ $key }}">
                                        
                                        <label class="form-label fw-bold small text-muted text-uppercase">{{ $config['label'] }}</label>
                                        
                                        @if(in_array($config['type'], ['text', 'number', 'email', 'date', 'password']))
                                            <input type="{{ $config['type'] }}" 
                                                wire:model="form.{{ $key }}" 
                                                class="form-control @error('form.'.$key) is-invalid @enderror"
                                                placeholder="{{ $config['label'] }}">
                                        
                                        @elseif($config['type'] === 'textarea')
                                            <textarea wire:model="form.{{ $key }}" 
                                                    class="form-control @error('form.'.$key) is-invalid @enderror" 
                                                    rows="3"></textarea>
                                        @endif

                                        @error('form.'.$key) 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove>Enregistrer</span>
                            <span wire:loading><i class="fas fa-spinner fa-spin"></i> Traitement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT POUR GERER L'OUVERTURE/FERMETURE --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            const modalElement = document.getElementById('universalModal');
            const myModal = new bootstrap.Modal(modalElement);

            Livewire.on('open-modal', () => {
                myModal.show();
            });

            Livewire.on('close-modal', () => {
                myModal.hide();
            });
        });
    </script>

</div>