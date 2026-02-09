<?php

namespace App\Http\Controllers;

use App\Services\ContactAdmin\ContactAdminMailservice;
use App\Http\Requests\ContactAdminMail\ContactAdminRequest;
use Throwable;

class ContactAdminController extends Controller
{
    protected ContactAdminMailservice $mailService;

    public function __construct(ContactAdminMailservice $mailService)
    {
        $this->mailService = $mailService;
    }

    public function send(ContactAdminRequest $request)
    {
        $data = $request->validated();

        $mode = strtolower((string) $request->input('mode', 'queue')); // `queue` (default) or `sync`

        try {
            if ($mode === 'sync') {
                $this->mailService->sendNow($data);

                return response()->json([
                    'message' => 'Message sent successfully',
                    'mode' => 'sync',
                ], 200);
            }

            $this->mailService->queue($data);

            return response()->json([
                'message' => 'Message queued successfully',
                'mode' => 'queue',
            ], 202);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Failed to send message',
                'mode' => $mode,
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

}
