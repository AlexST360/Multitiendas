<?php

namespace Tests\Unit;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\Exceptions\PaymentAmountMismatchException;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_is_idempotent_same_idempotency_key_creates_only_one_payment(): void
    {
        $store = Store::factory()->create();

        $order = Order::create([
            'store_id' => $store->id,
            'public_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => OrderStatus::PendingPayment,
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => null,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $svc = new ConfirmPaymentService();

        $p1 = $svc->handle(
            order: $order,
            gateway: 'fake',
            gatewayReference: 'ref-123',
            amount: 10000,
            currency: 'CLP',
            idempotencyKey: 'idem-aaa',
            rawResponse: ['ok' => true]
        );

        $p2 = $svc->handle(
            order: $order,
            gateway: 'fake',
            gatewayReference: 'ref-123',
            amount: 10000,
            currency: 'CLP',
            idempotencyKey: 'idem-aaa',
            rawResponse: ['ok' => true]
        );

        $this->assertSame($p1->id, $p2->id);
        $this->assertEquals(1, Payment::count());
    }

    /** @test */
    public function it_throws_exception_when_amount_does_not_match(): void
    {
        $store = Store::factory()->create();

        $order = Order::create([
            'store_id' => $store->id,
            'public_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => OrderStatus::PendingPayment,
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => null,
            'currency' => 'CLP',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $svc = new ConfirmPaymentService();

        $this->expectException(PaymentAmountMismatchException::class);

        $svc->handle(
            order: $order,
            gateway: 'fake',
            gatewayReference: 'ref-999',
            amount: 9999,
            currency: 'CLP',
            idempotencyKey: 'idem-mismatch',
            rawResponse: []
        );
    }

    /** @test */
    public function it_marks_order_as_paid_on_success(): void
    {
        $store = Store::factory()->create();

        $order = Order::create([
            'store_id' => $store->id,
            'public_token' => (string) \Illuminate\Support\Str::uuid(),
            'status' => OrderStatus::PendingPayment,
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'customer_phone' => null,
            'currency' => 'CLP',
            'subtotal' => 15000,
            'total' => 15000,
        ]);

        $svc = new ConfirmPaymentService();

        $payment = $svc->handle(
            order: $order,
            gateway: 'fake',
            gatewayReference: 'ref-ok',
            amount: 15000,
            currency: 'CLP',
            idempotencyKey: 'idem-ok',
            rawResponse: []
        );

        $order->refresh();

        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(PaymentStatus::Approved, $payment->status);
    }
}