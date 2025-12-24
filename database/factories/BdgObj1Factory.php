<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BdgObj1Factory extends Factory
{
    protected $table = 'bdg_obj1';

    public function definition(): array
    {
        return [
            'Code_obj1' => $this->faker->unique()->numerify('##'),
            'Libelle_obj1' => 'Objet 1 - ' . $this->faker->word(),
            'Libelle_obj1_ara' => 'البند 1',
            'EXERCICE' => date('Y'),
            'IDLogin' => 0,
        ];
    }
}
