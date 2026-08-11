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

    /** @var list<string> */
    public array $bccEmails;

    /**
     * @param  list<string>  $ccEmails
     * @param  list<string>  $bccEmails
     */
    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        array $ccEmails = [],
        array $bccEmails = []
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->ccEmails = $ccEmails;
        $this->bccEmails = $bccEmails;
    }

    public function build()
    {
        // Prefer DMC emails_setup SMTP over .env for every send.
        $setup = \App\Helpers\CommonHelper::applyEmailsSetupMailConfig();

        if (empty($this->fromEmail) && $setup && !empty($setup->From_Email)) {
            $this->fromEmail = $setup->From_Email;
            $this->fromName = $setup->From_Name ?: $this->fromName;
        }

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

        foreach ($this->bccEmails as $bccEmail) {
            if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->bcc($bccEmail);
            }
        }

        return $mail;
    }
}
