<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusChangeNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $userEmail;
    public string $serviceNumber;
    public int $serviceRequestId;
    public string $statusLabel;
    public string $statusCode;
    public ?string $serviceRequestUrl;
    public ?string $deviceName;
    public ?string $deviceModel;
    public ?string $notes;

    public function __construct(
        string $userName,
        string $userEmail,
        string $serviceNumber,
        int $serviceRequestId,
        string $statusLabel,
        string $statusCode,
        ?string $serviceRequestUrl = null,
        ?string $deviceName = null,
        ?string $deviceModel = null,
        ?string $notes = null,
    ) {
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->serviceNumber = $serviceNumber;
        $this->serviceRequestId = $serviceRequestId;
        $this->statusLabel = $statusLabel;
        $this->statusCode = $statusCode;
        $this->serviceRequestUrl = $serviceRequestUrl;
        $this->deviceName = $deviceName;
        $this->deviceModel = $deviceModel;
        $this->notes = $notes;
    }

    public function envelope(): Envelope
    {
        $subject = "Servis {$this->serviceNumber} {$this->statusLabel}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.status-change-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
