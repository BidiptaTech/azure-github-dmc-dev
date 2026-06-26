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

    /** @var list<string> */
    public array $referenceMessageIds;

    public ?string $fromEmail;

    public ?string $fromName;

    public ?string $replyToEmail;

    /**
     * @param  list<string>  $referenceMessageIds  Bare or bracketed Message-IDs for References chain
     */
    public function __construct(
        $htmlContent,
        $subject = null,
        ?string $emailUuid = null,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        array $referenceMessageIds = []
    ) {
        $this->htmlContent = $htmlContent;
        $this->emailSubject = $subject ?: 'Booking Confirmation';
        $this->emailUuid = $emailUuid;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyToEmail = $replyToEmail;
        $this->referenceMessageIds = $referenceMessageIds;
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
            $inReplyTo = trim($this->emailUuid, '<>');
            $references = $this->referenceMessageIds !== []
                ? array_values(array_map(static fn (string $id): string => trim($id, '<>'), $this->referenceMessageIds))
                : [$inReplyTo];

            if (! in_array($inReplyTo, $references, true)) {
                $references[] = $inReplyTo;
            }

            $mail->withSymfonyMessage(function ($message) use ($inReplyTo, $references) {
                $headers = $message->getHeaders();
                if ($headers->has('In-Reply-To')) {
                    $headers->remove('In-Reply-To');
                }
                if ($headers->has('References')) {
                    $headers->remove('References');
                }
                $headers->addIdHeader('In-Reply-To', $inReplyTo);
                $headers->addIdHeader('References', $references);
            });
        }

        return $mail;
    }
}
