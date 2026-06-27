<?php

namespace App\Mail;

use App\Models\PaymentAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public PaymentAlert $alert)
    {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Alert: ' . str_replace('_', ' ', $this->alert->type),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-alert',
        );
    }
}
