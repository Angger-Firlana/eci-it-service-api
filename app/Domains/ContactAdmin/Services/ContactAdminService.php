<?php

namespace App\Domains\ContactAdmin\Services;

use App\Domains\ContactAdmin\Actions\QueueContactAdminMail;
use App\Domains\ContactAdmin\Actions\SendContactAdminMail;
use Throwable;

class ContactAdminService
{
    public function __construct(
        protected QueueContactAdminMail $queueContactAdminMail,
        protected SendContactAdminMail $sendContactAdminMail
    ) {
    }

    public function queue(array $data): void
    {
        $this->queueContactAdminMail->execute($data);
    }

    public function sendNow(array $data): void
    {
        $this->sendContactAdminMail->execute($data);
    }

    public function sendAdminNotification(int $serviceRequestId, string $actorName, string $actorEmail): void
    {
        try {
            $this->queue([
                'name' => $actorName,
                'email' => $actorEmail,
                'message' => 'A new service request has been created and requires review.',
                'service_request_id' => $serviceRequestId,
            ]);
        } catch (Throwable $e) {
            logger()->error('Failed to queue admin notification email for service request.', [
                'service_request_id' => $serviceRequestId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
