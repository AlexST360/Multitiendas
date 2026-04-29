<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly Store $store,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pedido fue confirmado! — ' . $this->store->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.paid',
            with: [
                'order'    => $this->order,
                'store'    => $this->store,
                'orderUrl' => route('storefront.order.thankyou', [
                    $this->store->slug,
                    $this->order->public_token,
                ]),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
