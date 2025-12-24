<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StkExerciceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stk_exercice')->insert([
            'Libellé' => 'Exercice 2026',
            'anne' => '2026',
            'Ouvert' => 1,
        ]);
    }
}
