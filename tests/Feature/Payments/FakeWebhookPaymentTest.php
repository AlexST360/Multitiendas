<?php

namespace Tests\Feature\Payments;

use App\Domain\Orders\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FakeWebhookPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_confirms_payment_and_marks_order_paid(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PendingPayment,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $payload = [
            'store_slug' => $order->store->slug,
            'order_token' => $order->public_token,
            'amount' => 10000,
            'currency' => 'CLP',
            'idempotency_key' => 'test-001',
            'gateway_reference' => 'fake-001',
        ];

        $res = $this->postJson('/webhooks/fake', $payload);

        $res->assertOk()
            ->assertJson([
                'ok' => true,
                'order_status' => 'paid',
                'payment_status' => 'approved',
            ]);

        $order->refresh();
        $this->assertSame('paid', $order->status->value);

        $this->assertDatabaseHas('payments', [
            'store_id' => $order->store_id,
            'order_id' => $order->id,
            'idempotency_key' => 'test-001',
        ]);
    }

    public function test_webhook_is_idempotent_same_key_returns_same_payment(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PendingPayment,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $payload = [
            'store_slug' => $order->store->slug,
            'order_token' => $order->public_token,
            'amount' => 10000,
            'currency' => 'CLP',
            'idempotency_key' => 'test-002',
            'gateway_reference' => 'fake-002',
        ];

        $first = $this->postJson('/webhooks/fake', $payload)->assertOk()->json();
        $second = $this->postJson('/webhooks/fake', $payload)->assertOk()->json();

        $this->assertSame($first['payment_id'], $second['payment_id']);

        $this->assertSame(1, Payment::query()
            ->where('store_id', $order->store_id)
            ->where('idempotency_key', 'test-002')
            ->count());
    }

    public function test_webhook_rejects_new_key_if_order_already_paid(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::PendingPayment,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        // paga una vez
        $this->postJson('/webhooks/fake', [
            'store_slug' => $order->store->slug,
            'order_token' => $order->public_token,
            'amount' => 10000,
            'currency' => 'CLP',
            'idempotency_key' => 'test-003',
            'gateway_reference' => 'fake-003',
        ])->assertOk();

        // intenta pagar de nuevo con otra key
        $res = $this->postJson('/webhooks/fake', [
            'store_slug' => $order->store->slug,
            'order_token' => $order->public_token,
            'amount' => 10000,
            'currency' => 'CLP',
            'idempotency_key' => 'test-004',
            'gateway_reference' => 'fake-004',
        ]);

        $res->assertStatus(409)
            ->assertJson([
                'ok' => false,
                'order_status' => 'paid',
            ]);
    }
}