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

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    /** @var list<string> */
    public array $ccEmails;

    /** @var list<string> */
    public array $bccEmails;

    public ?string $emailUuid;

    /** @var list<string> */
    public array $referenceMessageIds;

    /** @var array<string, mixed>|null */
    protected ?array $runtimeMailConfig;

    /**
     * @param  list<string>  $ccEmails
     * @param  list<string>  $bccEmails
     * @param  array<string, mixed>|null  $runtimeMailConfig
     * @param  list<string>  $referenceMessageIds
     */
    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        array $ccEmails = [],
        array $bccEmails = [],
        ?array $runtimeMailConfig = null,
        ?string $emailUuid = null,
        array $referenceMessageIds = []
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->ccEmails = $ccEmails;
        $this->bccEmails = $bccEmails;
        $this->runtimeMailConfig = $runtimeMailConfig;
        $this->emailUuid = $emailUuid;
        $this->referenceMessageIds = $referenceMessageIds;
    }

    /**
     * AI automation mail: runtime SMTP from API / DMC AI mailbox, or a thread reply.
     * Support mail: DMC emails_setup SMTP (guest credentials, hotel, etc.).
     */
    public function isAiAutomationMail(): bool
    {
        return $this->runtimeMailConfig !== null || ! empty($this->emailUuid);
    }

    public function headers(): Headers
    {
        if (! $this->isAiAutomationMail() || empty($this->emailUuid)) {
            return new Headers;
        }

        $parentId = trim($this->emailUuid, '<>');
        $references = [];

        foreach (array_merge($this->referenceMessageIds, [$parentId]) as $messageId) {
            $id = trim((string) $messageId, '<>');
            if ($id !== '' && ! in_array($id, $references, true)) {
                $references[] = $id;
            }
        }

        return new Headers(
            references: $references,
            text: [
                'In-Reply-To' => '<'.$parentId.'>',
            ],
        );
    }

    public function build()
    {
        $setup = null;

        if ($this->isAiAutomationMail() && $this->runtimeMailConfig !== null) {
            // AI automation: send from the DMC AI mailbox (API / stored SMTP).
            \App\Helpers\CommonHelper::applyRuntimeMailConfig($this->runtimeMailConfig);
            $this->fromEmail = $this->fromEmail ?: ($this->runtimeMailConfig['from_email'] ?? null);
            $this->fromName = $this->fromName ?: ($this->runtimeMailConfig['from_name'] ?? null);
        } else {
            // Support mail: DMC emails_setup SMTP (falls back to .env if not configured).
            $setup = \App\Helpers\CommonHelper::applyEmailsSetupMailConfig();
        }

        // Only use emails_setup From_Email when that row actually applied SMTP.
        // Mixing .env SMTP (support@travclicks.com) with a different From_Email
        // causes Hostinger 553 5.7.1 "Sender address rejected: not owned by user".
        $smtpWasApplied = $setup && ! empty($setup->SMTP_Host) && ! empty($setup->SMTP_User) && ! empty($setup->SMTP_Pass);
        if (empty($this->fromEmail) && $smtpWasApplied && ! empty($setup->From_Email)) {
            $this->fromEmail = $setup->From_Email;
            $this->fromName = $setup->From_Name ?: $this->fromName;
        } elseif (empty($this->fromName) && $setup && ! empty($setup->From_Name)) {
            $this->fromName = $setup->From_Name;
        }

        $this->alignFromAddressWithSmtpUser();

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

    /**
     * Hostinger (and most SMTP hosts) reject MAIL FROM when it is not owned by
     * the authenticated user: 553 5.7.1 Sender address rejected.
     * Keep a mismatched address as Reply-To so replies still go to the DMC.
     */
    private function alignFromAddressWithSmtpUser(): void
    {
        $smtpUser = trim((string) config('mail.mailers.smtp.username'));
        if ($smtpUser === '' || ! filter_var($smtpUser, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $currentFrom = trim((string) $this->fromEmail);
        if ($currentFrom !== '' && strcasecmp($currentFrom, $smtpUser) !== 0) {
            if (empty($this->replyToEmail) && filter_var($currentFrom, FILTER_VALIDATE_EMAIL)) {
                $this->replyToEmail = $currentFrom;
            }
        }

        $this->fromEmail = $smtpUser;
        if (empty($this->fromName)) {
            $this->fromName = (string) config('mail.from.name', config('app.name'));
        }
    }
}
