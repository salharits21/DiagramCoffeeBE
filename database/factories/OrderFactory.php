<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'subtotal' => fake()->randomFloat(2, 20000, 200000),
            'discount_total' => 0,
            'total_amount' => fake()->randomFloat(2, 20000, 200000),
            'loyalty_points_earned' => 0,
            'notes' => null,
        ];
    }

    public function xendit(): static
    {
        return $this->state(fn () => [
            'payment_method' => 'xendit',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'paid_at' => now(),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function preparing(): static
    {
        return $this->state(fn () => [
            'status' => 'preparing',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn () => [
            'status' => 'ready',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'payment_status' => 'failed',
        ]);
    }
}
