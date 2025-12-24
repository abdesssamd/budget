<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $table = 'bdg_param_general_bdg';
    protected $primaryKey = 'IDParam_general_bdg';
    public $timestamps = false;

    // IMPORTANT : la clé n'est pas auto-increment
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'IDParam_general_bdg',
        'Creer_le',
        'IDLogin',
        'nombre_niveau',

        'LIBellé_niveau1_fr',
        'LIBellé_niveau1_ara',
        'LIBellé_niveau2_fr',
        'LIBellé_niveau2_ara',
        'LIBellé_niveau3_fr',
        'LIBellé_niveau3_ara',
        'LIBellé_niveau4_fr',
        'LIBellé_niveau4_ara',

        'Ministaire_tutel',
        'Ministére_de_tutelle_ara',
        'Nom_hie_etatblisement',
        'Nom_hie_etatblisement_ara',
        'Nom_etatblissement',
        'Nom_etatblissement_ara',
        'Nom_hie_etatblisement_second',
        'Nom_hie_etatblisement_second_fr',

        'wilaya',
        'Ville',
        'ville_ara',
        'wilaya_ara',

        'Adresse',
        'adresse_ara',
        'Tel',
        'Fax',
        'Daira_fr',
        'Daira_ara',
        'Commune_fr',
        'Commune_ara',

        'ordonateur',
        'Num_cpt_ordonateur',
        'Num_cpt_tresorier',
        'Num_cpt_ordonateur_ccp',
        'ligne_contable',
        'ligne_contable_ara',

        'EstcequeArticledependpere',
        'LIBellé_niveau5fr',
        'LIBellé_niveau5_ara',
        'estcequeArticledependpere_dep',
        'estcequeArborCommun_dep_rec',

        'EXERCICE'
    ];
}
