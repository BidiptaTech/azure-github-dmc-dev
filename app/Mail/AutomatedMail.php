<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class DmcMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;
    public $emailSubject;
    public $emailUuid;

    public function __construct(
        $htmlContent,
        $subject = null,
        $emailUuid = null
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->emailUuid = $emailUuid;
    }

    /**
     * Email Threading Headers
     */
    public function headers(): Headers
    {
        if (empty($this->emailUuid)) {
            return new Headers();
        }

        return new Headers(
            text: [
                'In-Reply-To' => $this->emailUuid,
                'References'  => $this->emailUuid,
            ]
        );
    }

    public function build()
    {
        return $this->subject($this->emailSubject)
                    ->html($this->htmlContent);
    }
}