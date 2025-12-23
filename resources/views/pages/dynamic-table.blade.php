{{-- On étend votre layout principal G-Stock --}}
@extends('layouts.app')

{{-- Titre de la page --}}
@section('header', $title ?? 'Gestion')
@section('title', $title ?? 'Gestion')

@section('content')
    {{-- On appelle le composant Livewire en lui passant les variables reçues depuis la route --}}
    <livewire:shared.universal-data-table 
        :model="$model"
        :columns="$columns"
        :fields="$fields ?? []"
        :primary-key="$primaryKey ?? 'id'"
        :can-create="$canCreate ?? false"
        :can-edit="$canEdit ?? false"
        :can-delete="$canDelete ?? false"
    />
@endsection