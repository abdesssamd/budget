<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BdgObj2Factory extends Factory
{
    protected $table = 'bdg_obj2';

    public function definition(): array
    {
        return [
            'Code_obj2' => $this->faker->unique()->numerify('##'),
            'Libelle_obj2' => 'Objet 2 - ' . $this->faker->word(),
            'Libelle_obj2_ara' => 'البند 2',
            'Code_obj1' => '01',
            'EXERCICE' => date('Y'),
            'IDLogin' => 0,
        ];
    }
}
