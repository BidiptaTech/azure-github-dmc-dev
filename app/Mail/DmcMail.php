<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DmcMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $htmlContent;
    public $emailSubject;

    public function __construct($htmlContent, $subject = null)
    {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
    }

    public function build()
    {
        return $this->subject($this->emailSubject)
                    ->html($this->htmlContent);
    }
}
