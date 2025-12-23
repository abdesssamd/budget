@extends('layouts.app')
@section('title', 'Gestion des Budgets')

@section('content')

@if(session('budget_id'))
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="fas fa-check-circle me-2"></i> Budget Actif : <strong>{{ session('budget_nom') }} ({{ session('exercice_actuel') }})</strong>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header card-header-custom"><i class="fas fa-plus me-2"></i> Nouveau Budget</div>
            <div class="card-body">
                <form action="{{ route('budgets.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Désignation</label>
                        <input type="text" name="designation" class="form-control" placeholder="Ex: Budget Primitif..." required>
                    </div>
                    <div class="mb-3">
                        <label>Référence</label>
                        <input type="text" name="Reference" class="form-control" placeholder="Réf...">
                    </div>
                    <div class="mb-3">
                        <label>Exercice</label>
                        <select name="EXERCICE" class="form-select">
                            @foreach($exercices as $ex)
                                <option value="{{ $ex->anne }}">{{ $ex->anne }} - {{ $ex->Libellé }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Créer</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header card-header-custom"><i class="fas fa-folder-open me-2"></i> Liste des Budgets</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Exercice</th>
                            <th>Désignation</th>
                            <th>Ref</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $bg)
                        <tr class="{{ session('budget_id') == $bg->IDBudjet ? 'table-primary' : '' }}">
                            <td class="ps-3 fw-bold">{{ $bg->EXERCICE }}</td>
                            <td>{{ $bg->designation }}</td>
                            <td><small class="text-muted">{{ $bg->Reference }}</small></td>
                            <td class="text-center">
                                @if(session('budget_id') == $bg->IDBudjet)
                                    <button class="btn btn-sm btn-success disabled"><i class="fas fa-check"></i> Actif</button>
                                @else
                                    <a href="{{ route('budgets.selectionner', $bg->IDBudjet) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-mouse-pointer me-1"></i> Sélectionner
                                    </a>
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