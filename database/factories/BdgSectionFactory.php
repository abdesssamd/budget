<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BdgSectionFactory extends Factory
{
    protected $table = 'bdg_section';

    public function definition(): array
    {
        return [
            'Num_section' => $this->faker->unique()->numerify('##'),
            'NOM_section' => 'section ' . $this->faker->numberBetween(1, 99),
            'NOM_section_ara' => 'القسم ' . $this->faker->numberBetween(1, 99),

            'dep_recette' => $this->faker->randomElement([0, 1]),
            'IDLogin' => 0,

            'Estmateriel' => 0,
            'EstMantGarde_fin_exrc' => 0,

            'Mt_genr' => 0,
            'Mt_projet' => 0,
            'Mt_projet_Nv' => 0,
            'Mt_Total' => 0,

            'EXERCICE' => date('Y'),
            'Creer_le' => now(),
        ];
    }
}
