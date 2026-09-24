<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public ReturnRequest $return;

    /**
     * Create a new message instance.
     */
    public function __construct(ReturnRequest $return)
    {
        $this->return = $return->loadMissing('order');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Return Update — #{$this->return->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.return-status-update',
            with: [
                'return' => $this->return,
            ],
        );
    }
}
