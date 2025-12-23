@extends('layouts.app')
@section('title', 'Gestion des Exercices')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header card-header-custom"><i class="fas fa-plus me-2"></i> Nouvel Exercice</div>
            <div class="card-body">
                <form action="{{ route('exercices.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Année</label>
                        <input type="number" name="anne" class="form-control" value="{{ date('Y') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Libellé</label>
                        <input type="text" name="Libellé" class="form-control" value="Exercice {{ date('Y') }}" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="Ouvert" value="1" checked>
                        <label class="form-check-label">Exercice Ouvert ?</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header card-header-custom"><i class="fas fa-list me-2"></i> Liste des Exercices</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Année</th>
                            <th>Libellé</th>
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exercices as $ex)
                        <tr>
                            <td class="fw-bold">{{ $ex->anne }}</td>
                            <td>{{ $ex->Libellé }}</td>
                            <td>
                                @if($ex->Ouvert == 1)
                                    <span class="badge bg-success">Ouvert</span>
                                @else
                                    <span class="badge bg-secondary">Clôturé</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection