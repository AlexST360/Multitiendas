<?php

namespace Tests\Unit;

use App\Domain\Orders\Enums\OrderStatus;
use App\Domain\Payments\ConfirmPaymentService;
use App\Domain\Payments\DTO\ConfirmedPayment;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Domain\Payments\Exceptions\PaymentAmountMismatchException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ConfirmPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(Store $store, int $total = 10000): Order
    {
        return Order::create([
            'store_id'         => $store->id,
            'public_token'     => (string) Str::uuid(),
            'status'           => OrderStatus::PendingPayment,
            'customer_name'    => 'Test',
            'customer_email'   => 'test@example.com',
            'customer_phone'   => null,
            'shipping_address' => 'Av. Test 123',
            'shipping_city'    => 'Santiago',
            'shipping_region'  => 'Metropolitana',
            'currency'         => 'CLP',
            'subtotal'         => $total,
            'total'            => $total,
        ]);
    }

    private function makeDto(Order $order, array $overrides = []): ConfirmedPayment
    {
        return new ConfirmedPayment(
            storeId:          $overrides['storeId']          ?? $order->store_id,
            orderId:          $overrides['orderId']           ?? $order->id,
            gateway:          $overrides['gateway']           ?? 'fake',
            gatewayReference: $overrides['gatewayReference']  ?? 'ref-123',
            amount:           $overrides['amount']            ?? $order->total,
            currency:         $overrides['currency']          ?? 'CLP',
            idempotencyKey:   $overrides['idempotencyKey']    ?? 'idem-' . Str::random(6),
            rawPayload:       $overrides['rawPayload']        ?? [],
        );
    }

    public function test_idempotent_same_key_creates_only_one_payment(): void
    {
        $store = Store::factory()->create();
        $order = $this->makeOrder($store);
        $svc   = new ConfirmPaymentService();

        $dto = $this->makeDto($order, ['idempotencyKey' => 'idem-aaa']);

        $p1 = $svc->confirmDto($dto);
        $p2 = $svc->confirmDto($dto);

        $this->assertSame($p1->id, $p2->id);
        $this->assertEquals(1, Payment::count());
    }

    public function test_throws_exception_when_amount_does_not_match(): void
    {
        $store = Store::factory()->create();
        $order = $this->makeOrder($store, 10000);
        $svc   = new ConfirmPaymentService();

        $this->expectException(PaymentAmountMismatchException::class);

        $svc->confirmDto($this->makeDto($order, ['amount' => 9999, 'idempotencyKey' => 'idem-mismatch']));
    }

    public function test_marks_order_as_paid_on_success(): void
    {
        $store = Store::factory()->create();
        $order = $this->makeOrder($store, 15000);
        $svc   = new ConfirmPaymentService();

        $payment = $svc->confirmDto($this->makeDto($order, ['amount' => 15000, 'idempotencyKey' => 'idem-ok']));

        $order->refresh();

        $this->assertEquals(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertEquals(1, Payment::count());
        $this->assertEquals(PaymentStatus::Approved, $payment->status);
    }
}
