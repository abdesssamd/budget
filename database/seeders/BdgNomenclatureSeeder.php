<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BdgNomenclatureSeeder extends Seeder
{
    public function run(): void
    {
        $exercice = 2025;
        $idLogin = 1;
        $idsection=1;

        // ==========================================================
        // TITRE I : Dépenses de Personnel (باب 1 : نفقات المستخدمين)
        // ==========================================================
        
        // 1. Création Niveau 1 (Obj1 - Titre)
        $idTitre1 = DB::table('bdg_obj1')->insertGetId([
            'Num' => '1',
            'designation' => 'Dépenses de Personnel',
            'designation_ara' => 'نفقات المستخدمين',
            'Mt_projet' => 5000000.00,
            'EXERCICE' => $exercice,
            'IDSection' => $idsection,
            
            'IDLogin' => $idLogin,
            'dep_recette' => 0, // Dépense
        ]);

            // 2. Création Niveau 2 (Obj2 - Partie/Section)
            $idSection1 = DB::table('bdg_obj2')->insertGetId([
                'IDObj1' => $idTitre1, // Lien avec le parent
                'Num' => '1',
                'designation' => 'Rémunérations principales',
                'designation_ara' => 'الرواتب الرئيسية',
                'Mt_projet' => 4000000.00,
                'EXERCICE' => $exercice,
                'IDSection' => $idsection,
                'IDLogin' => $idLogin,
            ]);

                // 3. Création Niveau 3 (Obj3 - Chapitre)
                $idChapitre1 = DB::table('bdg_obj3')->insertGetId([
                    'IDObj2' => $idSection1,
                    'Num' => '31-11',
                    'designation' => 'Salaires et indemnités',
                    'designation_ara' => 'الرواتب والتعويضات',
                    'Mt_projet' => 3000000.00,
                    'EXERCICE' => $exercice,
                    'IDSection' => $idsection,
                    'IDLogin' => $idLogin,
                ]);

                    // 4. Création Niveau 4 (Obj4 - Article)
                    $idArticle1 = DB::table('bdg_obj4')->insertGetId([
                        'IDObj3' => $idChapitre1,
                        'Num' => '01',
                        'designation' => 'Personnel Titulaire',
                        'designation_ara' => 'الموظفون المرسمون',
                        'Mt_projet' => 2000000.00,
                        'EXERCICE' => $exercice,
                        'IDSection' => $idsection,
                        'IDLogin' => $idLogin,
                    ]);

                        // 5. Création Niveau 5 (Obj5 - Paragraphe)
                        DB::table('bdg_obj5')->insert([
                            'IDObj4' => $idArticle1,
                            'Num' => '01',
                            'designation' => 'Salaire de base',
                            'designation_ara' => 'الأجر القاعدي',
                            'Mt_projet' => 1500000.00,
                            'EXERCICE' => $exercice,
                            'IDSection' => $idsection,
                            'IDLogin' => $idLogin,
                        ]);

                        DB::table('bdg_obj5')->insert([
                            'IDObj4' => $idArticle1,
                            'Num' => '02',
                            'designation' => 'Primes et indemnités',
                            'designation_ara' => 'المنح و التعويضات',
                            'Mt_projet' => 500000.00,
                            'EXERCICE' => $exercice,
                            'IDSection' => $idsection,
                            'IDLogin' => $idLogin,
                        ]);

        // ==========================================================
        // TITRE II : Matériel et Fonctionnement (باب 2 : الأدوات و التسيير)
        // ==========================================================
        
        $idTitre2 = DB::table('bdg_obj1')->insertGetId([
            'Num' => '2',
            'designation' => 'Fonctionnement des services',
            'designation_ara' => 'تسيير المصالح',
            'Mt_projet' => 2000000.00,
            'EXERCICE' => $exercice,
            'IDSection' => 2,
            'IDLogin' => $idLogin,
        ]);

            $idSection2 = DB::table('bdg_obj2')->insertGetId([
                'IDObj1' => $idTitre2,
                'Num' => '1',
                'designation' => 'Fournitures',
                'designation_ara' => 'الادوات',
                'Mt_projet' => 500000.00,
                'IDSection' => 2,
                'EXERCICE' => $exercice,
                'IDLogin' => $idLogin,
            ]);

                $idChapitre2 = DB::table('bdg_obj3')->insertGetId([
                    'IDObj2' => $idSection2,
                    'Num' => '32-12',
                    'designation' => 'Fournitures de bureau et informatique',
                    'designation_ara' => 'لوازم المكتب و الإعلام الآلي',
                    'Mt_projet' => 200000.00,
                    'EXERCICE' => $exercice,
                    'IDSection' => 2,
                    'IDLogin' => $idLogin,
                ]);
                
                    // Article unique sans détail paragraphe pour cet exemple
                    DB::table('bdg_obj4')->insert([
                        'IDObj3' => $idChapitre2,
                        'Num' => '01',
                        'designation' => 'Papeterie',
                        'designation_ara' => 'الوراقة',
                        'Mt_projet' => 100000.00,
                        'EXERCICE' => $exercice,
                        'IDSection' => 2,
                        'IDLogin' => $idLogin,
                    ]);
    }
}