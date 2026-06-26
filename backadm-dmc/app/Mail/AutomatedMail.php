<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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

        if (! empty($this->emailUuid)) {
            $parentMessageId = $this->emailUuid;
            $mail->withSymfonyMessage(function ($message) use ($parentMessageId) {
                $headers = $message->getHeaders();
                $headers->remove('In-Reply-To');
                $headers->remove('References');
                $headers->addTextHeader('In-Reply-To', $parentMessageId);
                $headers->addTextHeader('References', $parentMessageId);
            });
        }

        return $mail;
    }
}
