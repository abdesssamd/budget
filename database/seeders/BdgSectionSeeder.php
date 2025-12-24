<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BdgSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bdg_section')->insert([
            [
                'Num_section' => '01',
                'NOM_section' => 'section 1',
                'NOM_section_ara' => 'القسم 1',
                'dep_recette' => 0,
                'Creer_le' => Carbon::now(),
                'IDLogin' => 1,
                'Estmateriel' => 0,
                'EstMantGarde_fin_exrc' => 0,
                'Mt_genr' => 0,
                'Mt_projet' => 0,
                'Mt_projet_Nv' => 0,
                'Mt_Total' => 0,
                'EXERCICE' => 1,
            ],
            [
                'Num_section' => '02',
                'NOM_section' => 'section 2',
                'NOM_section_ara' => 'القسم 2',
                'dep_recette' => 0,
                'Creer_le' => Carbon::now(),
                'IDLogin' => 1,
                'Estmateriel' => 1,
                'EstMantGarde_fin_exrc' => 0,
                'Mt_genr' => 0,
                'Mt_projet' => 0,
                'Mt_projet_Nv' => 0,
                'Mt_Total' => 0,
                'EXERCICE' => 1,
            ],
        ]);
    }
}
