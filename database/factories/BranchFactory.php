<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name' => 'Cabang ' . fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ];
    }

    /**
     * Cabang yang tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
