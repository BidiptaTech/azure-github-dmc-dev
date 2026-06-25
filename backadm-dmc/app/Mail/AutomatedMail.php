<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Automated replies from the external API pipeline (quotation, incomplete details, etc.).
 * Supports DMC from/reply-to and email thread headers via email_uuid.
 */
class AutomatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;

    public $emailSubject;

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    public ?string $emailUuid;

    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        ?string $emailUuid = null
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->emailUuid = self::normalizeMessageId($emailUuid);
    }

  /**
     * Normalize payload email_uuid to a valid Message-ID for In-Reply-To / References.
     */
    public static function normalizeMessageId(?string $emailUuid): ?string
    {
        $emailUuid = trim((string) $emailUuid);
        if ($emailUuid === '' || strtolower($emailUuid) === 'no-uuid-provided') {
            return null;
        }

        if (str_starts_with($emailUuid, '<') && str_ends_with($emailUuid, '>')) {
            return $emailUuid;
        }

        if (str_contains($emailUuid, '@')) {
            return '<'.$emailUuid.'>';
        }

        return '<'.$emailUuid.'@travclicks.com>';
    }

    public function headers(): Headers
    {
        if ($this->emailUuid === null) {
            return new Headers();
        }

        return new Headers(
            text: [
                'In-Reply-To' => $this->emailUuid,
                'References' => $this->emailUuid,
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
