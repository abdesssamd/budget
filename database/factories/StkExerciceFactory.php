<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StkExerciceFactory extends Factory
{
    protected $table = 'stk_exercice';

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2020, 2035);

        return [
            'Libellé' => 'Exercice ' . $year,
            'anne' => (string) $year,
            'Ouvert' => $this->faker->randomElement([0, 1]),
        ];
    }

    /**
     * État spécifique : exercice ouvert
     */
    public function ouvert(): static
    {
        return $this->state(fn () => [
            'Ouvert' => 1,
        ]);
    }

    /**
     * État spécifique : exercice fermé
     */
    public function ferme(): static
    {
        return $this->state(fn () => [
            'Ouvert' => 0,
        ]);
    }
}
