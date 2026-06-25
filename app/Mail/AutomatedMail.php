<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AutomatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;

    public $emailSubject;

    public ?string $emailUuid;

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $emailUuid = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->emailUuid = $emailUuid;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
    }

    /**
     * Thread this reply into the sender's original email conversation.
     */
    public function headers(): Headers
    {
        if (empty($this->emailUuid)) {
            return new Headers();
        }

        return new Headers(
            references: [$this->emailUuid],
            text: [
                'In-Reply-To' => $this->emailUuid,
            ]
        );
    }

    public function build()
    {
        $mail = $this->subject($this->emailSubject)->html($this->htmlContent);

        if ($this->fromEmail && filter_var($this->fromEmail, FILTER_VALIDATE_EMAIL)) {
            $mail->from($this->fromEmail, (string) ($this->fromName ?? ''));
        }

        $replyTo = $this->replyToEmail ?: $this->fromEmail;
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->replyTo($replyTo, (string) ($this->fromName ?? ''));
        }

        return $mail;
    }
}
