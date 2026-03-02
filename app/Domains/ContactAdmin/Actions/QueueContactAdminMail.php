<?php

namespace App\Domains\ContactAdmin\Actions;

use App\Domains\ContactAdmin\Support\ContactAdminContextResolver;
use App\Mail\UserContactAdmin;
use Illuminate\Support\Facades\Mail;

class QueueContactAdminMail
{
    public function __construct(
        protected ContactAdminContextResolver $contextResolver
    ) {
    }

    public function execute(array $data): void
    {
        $adminEmail = $this->contextResolver->adminEmail();
        $attachmentPath = $this->contextResolver->attachmentPath($data);

        [
            $device,
            $deviceModel,
            $damages,
            $serviceRequestId,
            $serviceRequestUrl,
            $serviceNumber,
            $serviceRequestItems,
        ] = $this->contextResolver->serviceRequestContext($data);

        Mail::to($adminEmail)->queue(new UserContactAdmin(
            name: $data['name'],
            email: $data['email'],
            userMessage: $data['message'],
            attachmentPath: $attachmentPath,
            device: $device,
            deviceModel: $deviceModel,
            damages: $damages,
            serviceRequestId: $serviceRequestId,
            serviceRequestUrl: $serviceRequestUrl,
            serviceNumber: $serviceNumber,
            serviceRequestItems: $serviceRequestItems,
        ));
    }
}
