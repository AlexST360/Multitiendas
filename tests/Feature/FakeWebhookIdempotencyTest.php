<?php

namespace Tests\Feature;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FakeWebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_fake_is_idempotent_and_marks_order_paid(): void
    {
        $store = Store::create([
            'name' => 'Default Store',
            'slug' => 'default',
        ]);

        $order = Order::create([
            'store_id'       => $store->id,
            'public_token'   => (string) Str::uuid(),
            'status'         => OrderStatus::PendingPayment,
            'customer_name'  => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '123456789',
            'currency'       => 'CLP',
            'subtotal'       => 10000,
            'total'          => 10000,
        ]);

        $payload = [
            'store_slug'        => $store->slug,
            'order_token'       => $order->public_token,
            'amount'            => 10000,
            'currency'          => 'CLP',
            'idempotency_key'   => 'replay_test_1',
            'gateway_reference' => 'fake_ref_1',
        ];

        // 1ra llamada
        $r1 = $this->postJson('/webhooks/fake', $payload, [
            'Accept' => 'application/json',
        ]);

        $r1->assertOk();
        $r1->assertJson([
            'ok'             => true,
            'order_status'   => 'paid',
            'payment_status' => 'approved',
        ]);

        // 2da llamada (misma idempotency_key) → no debe crear otro Payment
        $r2 = $this->postJson('/webhooks/fake', $payload, [
            'Accept' => 'application/json',
        ]);

        $r2->assertOk();
        $r2->assertJson([
            'ok'             => true,
            'order_status'   => 'paid',
            'payment_status' => 'approved',
        ]);

        // Order quedó paid
        $order->refresh();
        $this->assertSame('paid', $order->status->value);

        // Idempotencia: un solo Payment para esa key en esa tienda
        $this->assertSame(1, Payment::query()
            ->where('store_id', $store->id)
            ->where('idempotency_key', 'replay_test_1')
            ->count());

        // Edge case: no puede existir más de un Payment approved para la misma orden
        $this->assertSame(1, Payment::query()
            ->where('store_id', $store->id)
            ->where('order_id', $order->id)
            ->where('status', PaymentStatus::Approved->value)
            ->count());

        // El payment corresponde a la orden con todos sus datos correctos
        $payment = Payment::query()
            ->where('store_id', $store->id)
            ->where('idempotency_key', 'replay_test_1')
            ->first();

        $this->assertNotNull($payment);
        $this->assertSame($order->id, $payment->order_id);
        $this->assertSame('fake', $payment->gateway);
        $this->assertSame(PaymentStatus::Approved->value, $payment->status->value);
        $this->assertSame(10000, (int) $payment->amount);
        $this->assertSame('CLP', $payment->currency);
    }
}