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

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    public ?string $inReplyToMessageId;

    /** @var list<string> */
    public array $referenceMessageIds;

    /**
     * @param  list<string>  $referenceMessageIds
     */
    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        ?string $inReplyToMessageId = null,
        array $referenceMessageIds = []
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->inReplyToMessageId = $inReplyToMessageId;
        $this->referenceMessageIds = $referenceMessageIds;
    }

    /**
     * Thread AI replies into the sender's original email conversation.
     */
    public function headers(): Headers
    {
        if ($this->inReplyToMessageId === null || $this->inReplyToMessageId === '') {
            return new Headers();
        }

        $references = $this->referenceMessageIds !== []
            ? $this->referenceMessageIds
            : [$this->inReplyToMessageId];

        return new Headers(
            references: $references,
            text: [
                'In-Reply-To' => $this->inReplyToMessageId,
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
