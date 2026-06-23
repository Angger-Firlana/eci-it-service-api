<?php

namespace App\Http\Controllers;

use App\Domains\MailSetting\Services\MailSettingService;
use App\Helpers\APIResponse;
use App\Http\Requests\MailSetting\TestMailSettingRequest;
use App\Http\Requests\MailSetting\UpdateMailSettingRequest;
use Throwable;

class MailSettingController extends Controller
{
    public function __construct(
        protected MailSettingService $mailSettingService
    ) {
    }

    /**
     * Return the current mail settings (password is never exposed; a flag tells
     * the client whether one is already stored).
     */
    public function show()
    {
        $setting = $this->mailSettingService->getOrCreate();

        return APIResponse::success($this->present($setting));
    }

    public function update(UpdateMailSettingRequest $request)
    {
        $setting = $this->mailSettingService->update($request->validated());

        return APIResponse::success($this->present($setting), 200, 'Pengaturan email disimpan');
    }

    public function test(TestMailSettingRequest $request)
    {
        try {
            $this->mailSettingService->sendTest($request->validated()['to']);
        } catch (Throwable $e) {
            return APIResponse::error(null, 422, 'Gagal mengirim email tes: ' . $e->getMessage());
        }

        return APIResponse::success(null, 200, 'Email tes berhasil dikirim');
    }

    private function present($setting): array
    {
        return [
            'id' => $setting->id,
            'mailer' => $setting->mailer,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => $setting->username,
            'encryption' => $setting->encryption,
            'from_address' => $setting->from_address,
            'from_name' => $setting->from_name,
            'is_active' => (bool) $setting->is_active,
            'has_password' => filled($setting->getRawOriginal('password')),
        ];
    }
}
