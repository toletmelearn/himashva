<?php

namespace App\Mail;

use App\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartReminder extends Mailable
{
    use Queueable, SerializesModels;

    public AbandonedCart $cart;

    /**
     * Create a new message instance.
     */
    public function __construct(AbandonedCart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You left something behind at Himashva 🕯️',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned-cart-reminder',
            with: [
                'cart' => $this->cart,
            ],
        );
    }
}
