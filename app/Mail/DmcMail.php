<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DmcMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;

    public $emailSubject;

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    /** @var list<string> */
    public array $ccEmails;

    /**
     * @param  list<string>  $ccEmails
     */
    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        array $ccEmails = []
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->ccEmails = $ccEmails;
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

        foreach ($this->ccEmails as $ccEmail) {
            if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->cc($ccEmail);
            }
        }

        return $mail;
    }
}
