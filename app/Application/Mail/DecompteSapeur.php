<?php

namespace App\Application\Mail;

use App\Models\Sapeur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DecompteSapeur extends Mailable
{
    use Queueable, SerializesModels;

    public $sapeur;
    public $email;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Sapeur $sapeur, $pdf, $responseEmail)
    {
        $this->sapeur = $sapeur;
        $this->pdf = $pdf;
        $this->email = $responseEmail;
    }

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromData(fn() => $this->pdf, 'decompte.pdf')
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no-reply@gestsis.ch', 'GestSIS')
            ->subject("Votre décompte")
            ->text('emails.confirmation_email');
    }
}
