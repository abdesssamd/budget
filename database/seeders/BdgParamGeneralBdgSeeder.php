<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BdgParamGeneralBdgSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bdg_param_general_bdg')->insert([
            'IDLogin' => 1,
            'nombre_niveau' => 5,
            
            // Libellés standards
            'LIBellé_niveau1_fr' => 'Titre',
            'LIBellé_niveau1_ara' => 'الباب',
            'LIBellé_niveau2_fr' => 'Partie',
            'LIBellé_niveau2_ara' => 'القسم',
            'LIBellé_niveau3_fr' => 'Chapitre',
            'LIBellé_niveau3_ara' => 'الفصل',
            'LIBellé_niveau4_fr' => 'Article',
            'LIBellé_niveau4_ara' => 'المادة',
            'LIBellé_niveau5fr'   => 'Paragraphe',
            'LIBellé_niveau5_ara' => 'الفقرة',

            // Informations de l'établissement
            'Ministaire_tutel' => 'Ministère de la Santé',
            'Ministére_de_tutelle_ara' => 'وزارة الصحة',
            'Nom_etatblissement' => 'Etablissement Public Hospitalier Tlemcen',
            'Nom_etatblissement_ara' => 'المؤسسة العمومية الاستشفائية تلمسان',
            
            'wilaya' => 'Tlemcen',
            'wilaya_ara' => 'تلمسان',
            'Ville' => 'Mansourah',
            'ville_ara' => 'المنصورة',
            
            'ordonateur' => 'Directeur',
            'Num_cpt_tresorier' => '3000.123.456',
            
            // J'ai supprimé la ligne 'EXERCICE_ACTUEL' qui causait l'erreur
        ]);
    }
}