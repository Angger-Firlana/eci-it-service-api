<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class UserContactAdmin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public string $name;
    public string $email;

    // `message` kept for backward compatibility with previously queued jobs.
    public ?string $message = null;
    public ?string $userMessage = null;

    public ?string $attachmentPath = null;
    public ?string $device = null;
    public ?string $deviceModel = null;

    /** @var array<int,string> */
    public array $damages = [];

    public ?int $serviceRequestId = null;
    public ?string $serviceRequestUrl = null;
    public ?string $serviceNumber = null;

    /** @var array<int,array<string, mixed>> */
    public array $serviceRequestItems = [];

    public function __construct(
        string $name,
        string $email,
        ?string $userMessage,
        ?string $attachmentPath = null,
        ?string $device = null,
        ?string $deviceModel = null,
        array $damages = [],
        ?int $serviceRequestId = null,
        ?string $serviceRequestUrl = null,
        ?string $serviceNumber = null,
        array $serviceRequestItems = [],
    ) {
        $this->name = $name;
        $this->email = $email;

        $this->userMessage = $userMessage;
        $this->message = $userMessage;

        $this->attachmentPath = $attachmentPath;
        $this->device = $device;
        $this->deviceModel = $deviceModel;
        $this->damages = $damages;
        $this->serviceRequestId = $serviceRequestId;
        $this->serviceRequestUrl = $serviceRequestUrl;
        $this->serviceNumber = $serviceNumber;
        $this->serviceRequestItems = $serviceRequestItems;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectParts = ['Permintaan Servis'];
        if (is_string($this->serviceNumber) && trim($this->serviceNumber) !== '') {
            $subjectParts[] = $this->serviceNumber;
        }
        if (is_int($this->serviceRequestId)) {
            $subjectParts[] = '#'.$this->serviceRequestId;
        }
        if (is_string($this->deviceModel) && trim($this->deviceModel) !== '') {
            $subjectParts[] = $this->deviceModel;
        } elseif (is_string($this->device) && trim($this->device) !== '') {
            $subjectParts[] = $this->device;
        }

        return new Envelope(
            subject: implode(' - ', $subjectParts),
            replyTo: [new Address($this->email, $this->name)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.user-contact-admin',
            with: [
                // Backward-compatible name for older templates/jobs
                'message' => $this->userMessage ?? $this->message,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if (!$this->attachmentPath) {
            return [];
        }

        return [
            Attachment::fromPath(storage_path('app/' . $this->attachmentPath)),
        ];
    }

}
