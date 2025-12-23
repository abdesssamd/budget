@extends('layouts.app')

{{-- Titre de la page pour le navigateur --}}
@section('title', 'Paramétrage Général')

{{-- En-tête H1 géré par AdminLTE --}}
@section('header')
    <i class="fas fa-cogs mr-2"></i> Paramétrage Général
@endsection

@section('content')

<form action="{{ route('parametres.update') }}" method="POST">
    @csrf

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <p class="text-muted m-0">Configuration complète de l’établissement</p>
            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
                <i class="fas fa-save mr-2"></i> ENREGISTRER
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Succès !</h5>
            {{ session('success') }}
        </div>
    @endif

    {{-- Carte avec Onglets (Style AdminLTE) --}}
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="paramTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="structure-tab" data-toggle="pill" href="#structure" role="tab">
                        <i class="fas fa-sitemap mr-2"></i> Structure
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="identite-tab" data-toggle="pill" href="#identite" role="tab">
                        <i class="fas fa-building mr-2"></i> Identité
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="localisation-tab" data-toggle="pill" href="#localisation" role="tab">
                        <i class="fas fa-map-marker-alt mr-2"></i> Localisation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="finances-tab" data-toggle="pill" href="#finances" role="tab">
                        <i class="fas fa-wallet mr-2"></i> Finances
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="options-tab" data-toggle="pill" href="#options" role="tab">
                        <i class="fas fa-toggle-on mr-2"></i> Options
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content" id="paramTabContent">

                {{-- ONGLET 1 : STRUCTURE --}}
                <div class="tab-pane fade show active" id="structure" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="text-primary font-weight-bold">Nombre de niveaux hiérarchiques</label>
                            <input type="number" min="1" max="5" class="form-control form-control-lg"
                                   id="nombre_niveau" name="nombre_niveau"
                                   value="{{ old('nombre_niveau', $settings->nombre_niveau ?? 1) }}">
                        </div>
                    </div>

                    <div id="zones_niveaux" class="bg-light p-3 rounded border">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="row mb-3 niveau niveau-{{ $i }}">
                                <div class="col-md-6">
                                    <label>Libellé Niveau {{ $i }} (FR)</label>
                                    <input type="text" class="form-control"
                                           name="LIBellé_niveau{{ $i }}_fr"
                                           value="{{ old('LIBellé_niveau'.$i.'_fr', $settings->{'LIBellé_niveau'.$i.'_fr'} ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="float-right">المستوى {{ $i }}</label>
                                    <input type="text" class="form-control text-right" dir="rtl"
                                           name="LIBellé_niveau{{ $i }}_ara"
                                           value="{{ old('LIBellé_niveau'.$i.'_ara', $settings->{'LIBellé_niveau'.$i.'_ara'} ?? '') }}">
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- ONGLET 2 : IDENTITÉ --}}
                <div class="tab-pane fade" id="identite" role="tabpanel">
                    {{-- Ministère --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Ministère de tutelle</label>
                            <input type="text" class="form-control" name="Ministaire_tutel"
                                   value="{{ old('Ministaire_tutel', $settings->Ministaire_tutel ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="float-right">الوزارة الوصية</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="Ministére_de_tutelle_ara"
                                   value="{{ old('Ministére_de_tutelle_ara', $settings->Ministére_de_tutelle_ara ?? '') }}">
                        </div>
                    </div>

                    {{-- Établissement --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Nom Établissement</label>
                            <input type="text" class="form-control font-weight-bold" name="Nom_etatblissement"
                                   value="{{ old('Nom_etatblissement', $settings->Nom_etatblissement ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="float-right font-weight-bold">اسم المؤسسة</label>
                            <input type="text" class="form-control text-right font-weight-bold" dir="rtl" name="Nom_etatblissement_ara"
                                   value="{{ old('Nom_etatblissement_ara', $settings->Nom_etatblissement_ara ?? '') }}">
                        </div>
                    </div>

                    <hr>

                    {{-- Hiérarchie 1 --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Hiérarchie 1 (FR)</label>
                            <input type="text" class="form-control" name="Nom_hie_etatblisement"
                                   value="{{ old('Nom_hie_etatblisement', $settings->Nom_hie_etatblisement ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="float-right">الهيكل الإداري الأول</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="Nom_hie_etatblisement_ara"
                                   value="{{ old('Nom_hie_etatblisement_ara', $settings->Nom_hie_etatblisement_ara ?? '') }}">
                        </div>
                    </div>

                    {{-- Hiérarchie 2 --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Hiérarchie 2 (FR)</label>
                            <input type="text" class="form-control" name="Nom_hie_etatblisement_second_fr"
                                   value="{{ old('Nom_hie_etatblisement_second_fr', $settings->Nom_hie_etatblisement_second_fr ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="float-right">الهيكل الإداري الثاني</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="Nom_hie_etatblisement_second"
                                   value="{{ old('Nom_hie_etatblisement_second', $settings->Nom_hie_etatblisement_second ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- ONGLET 3 : LOCALISATION --}}
                <div class="tab-pane fade" id="localisation" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Wilaya</label>
                            <input type="text" class="form-control" name="wilaya"
                                   value="{{ old('wilaya', $settings->wilaya ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label>Commune</label>
                            <input type="text" class="form-control" name="Commune_fr"
                                   value="{{ old('Commune_fr', $settings->Commune_fr ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label>Daïra</label>
                            <input type="text" class="form-control" name="Daira_fr"
                                   value="{{ old('Daira_fr', $settings->Daira_fr ?? '') }}">
                        </div>
                    </div>
                    
                    {{-- Version Arabe --}}
                    <div class="row mb-3 bg-light p-2 rounded">
                         <div class="col-md-4">
                            <label class="float-right">الولاية</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="wilaya_ara"
                                   value="{{ old('wilaya_ara', $settings->wilaya_ara ?? '') }}">
                        </div>
                        <div class="col-md-4">
                             <label class="float-right">البلدية</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="Commune_ara"
                                   value="{{ old('Commune_ara', $settings->Commune_ara ?? '') }}">
                        </div>
                        <div class="col-md-4">
                             <label class="float-right">الدائرة</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="Daira_ara"
                                   value="{{ old('Daira_ara', $settings->Daira_ara ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label>Adresse</label>
                            <input type="text" class="form-control" name="Adresse"
                                   value="{{ old('Adresse', $settings->Adresse ?? '') }}">
                        </div>
                         <div class="col-md-4">
                            <label class="float-right">العنوان</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="adresse_ara"
                                   value="{{ old('adresse_ara', $settings->adresse_ara ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label><i class="fas fa-phone mr-2"></i>Téléphone</label>
                            <input type="text" class="form-control" name="Tel"
                                   value="{{ old('Tel', $settings->Tel ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label><i class="fas fa-fax mr-2"></i>Fax</label>
                            <input type="text" class="form-control" name="Fax"
                                   value="{{ old('Fax', $settings->Fax ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- ONGLET 4 : FINANCES --}}
                <div class="tab-pane fade" id="finances" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="font-weight-bold">Ordonnateur</label>
                            <input type="text" class="form-control" name="ordonateur"
                                   value="{{ old('ordonateur', $settings->ordonateur ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label>N° Compte Ordonnateur (Trésor)</label>
                            <input type="text" class="form-control font-monospace" name="Num_cpt_ordonateur"
                                   value="{{ old('Num_cpt_ordonateur', $settings->Num_cpt_ordonateur ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>N° Compte Trésorier</label>
                            <input type="text" class="form-control font-monospace" name="Num_cpt_tresorier"
                                   value="{{ old('Num_cpt_tresorier', $settings->Num_cpt_tresorier ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label>N° CCP Ordonnateur</label>
                            <input type="text" class="form-control font-monospace" name="Num_cpt_ordonateur_ccp"
                                   value="{{ old('Num_cpt_ordonateur_ccp', $settings->Num_cpt_ordonateur_ccp ?? '') }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Ligne Comptable</label>
                            <input type="text" class="form-control" name="ligne_contable"
                                   value="{{ old('ligne_contable', $settings->ligne_contable ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="float-right">الخط المحاسبي</label>
                            <input type="text" class="form-control text-right" dir="rtl" name="ligne_contable_ara"
                                   value="{{ old('ligne_contable_ara', $settings->ligne_contable_ara ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- ONGLET 5 : OPTIONS --}}
                <div class="tab-pane fade" id="options" role="tabpanel">
                    <div class="alert alert-warning">
                         <h5><i class="icon fas fa-exclamation-triangle"></i> Attention</h5>
                         Ces options modifient le comportement global de l'application.
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="EstcequeArticledependpere" name="EstcequeArticledependpere" value="1"
                                   {{ !empty($settings->EstcequeArticledependpere) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="EstcequeArticledependpere">Article dépend d’un parent ?</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estcequeArticledependpere_dep" name="estcequeArticledependpere_dep" value="1"
                                   {{ !empty($settings->estcequeArticledependpere_dep) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="estcequeArticledependpere_dep">Dépense dépend d’un parent ?</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estcequeArborCommun_dep_rec" name="estcequeArborCommun_dep_rec" value="1"
                                   {{ !empty($settings->estcequeArborCommun_dep_rec) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="estcequeArborCommun_dep_rec">Arborescence commune (Dépenses/Recettes) ?</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

{{-- SCRIPT DYNAMIQUE --}}
@section('js')
<script>
    // Utilisation de jQuery (inclus dans AdminLTE) pour simplifier
    $(document).ready(function() {
        var $nombreInput = $('#nombre_niveau');
        var $zones = $('.niveau');

        function updateNiveaux() {
            var nb = parseInt($nombreInput.val()) || 1;
            
            $zones.each(function(index) {
                var level = index + 1;
                if (level <= nb) {
                    $(this).removeClass('d-none').addClass('d-flex');
                } else {
                    $(this).removeClass('d-flex').addClass('d-none');
                }
            });
        }

        $nombreInput.on('input change', updateNiveaux);
        updateNiveaux(); // Init
    });
</script>
@stop

@endsection