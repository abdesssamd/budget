<div class="container">

    <h3 class="mb-4">Gestion des Produits</h3>

    <!-- Formulaire d'ajout -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Ajouter un produit
        </div>
        <div class="card-body">

            <form wire:submit.prevent="save">

                <div class="mb-3">
                    <label class="form-label">Libellé :</label>
                    <input type="text" class="form-control" wire:model="LibProd">
                </div>

                <div class="mb-3">
                    <label class="form-label">Référence :</label>
                    <input type="text" class="form-control" wire:model="Reference">
                </div>

                <div class="mb-3">
                    <label class="form-label">Qté Réappro :</label>
                    <input type="number" class="form-control" wire:model="QteReappro">
                </div>

                <div class="mb-3">
                    <label class="form-label">Qté Minimum :</label>
                    <input type="number" class="form-control" wire:model="QteMini">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description :</label>
                    <textarea class="form-control" wire:model="Description"></textarea>
                </div>

                <button class="btn btn-success">Enregistrer</button>
            </form>

        </div>
    </div>

    <!-- Liste des produits -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            Liste des produits
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Libellé</th>
                        <th>Référence</th>
                        <th>Qté Min</th>
                        <th>Qté Reappro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $p)
                        <tr>
                            <td>{{ $p->LibProd }}</td>
                            <td>{{ $p->Reference }}</td>
                            <td>{{ $p->QteMini }}</td>
                            <td>{{ $p->QteReappro }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Aucun produit trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
