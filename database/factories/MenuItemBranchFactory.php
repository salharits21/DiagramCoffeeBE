<?php

namespace Database\Factories;

use App\Models\MenuItemBranch;
use App\Models\MenuItem;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItemBranch>
 */
class MenuItemBranchFactory extends Factory
{
    protected $model = MenuItemBranch::class;

    public function definition(): array
    {
        return [
            'menu_item_id' => MenuItem::factory(),
            'branch_id' => Branch::factory(),
            'is_available' => true,
            'stock' => fake()->optional(0.7)->numberBetween(5, 100),
            'discount_type' => null,
            'discount_percentage' => null,
            'discount_amount' => null,
            'is_promo_active' => false,
        ];
    }

    /**
     * Dengan promo persentase.
     */
    public function withPercentageDiscount(float $percentage = 10.00): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'discount_percentage' => $percentage,
            'discount_amount' => null,
            'is_promo_active' => true,
        ]);
    }

    /**
     * Dengan promo potongan langsung.
     */
    public function withFixedDiscount(float $amount = 5000.00): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_percentage' => null,
            'discount_amount' => $amount,
            'is_promo_active' => true,
        ]);
    }

    /**
     * Stok habis / tidak tersedia.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }
}
