<?php

namespace Database\Factories;

use App\Domain\Orders\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'public_token' => (string) Str::uuid(),
            'status' => OrderStatus::PendingPayment,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => null,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Paid,
        ]);
    }
}