<?php

namespace App\Domains\MailSetting\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Manages the application's outgoing mail configuration.
 *
 * Settings are persisted in a single `mail_settings` row and, when active,
 * override the runtime mail config so every email the app sends uses the
 * admin-configured SMTP credentials. Receiving mail is out of scope — incoming
 * messages are handled by the mail server and forwarded to each user's inbox.
 */
class MailSettingService
{
    /**
     * Fetch the single settings row, creating an empty one if it does not exist.
     */
    public function getOrCreate(): MailSetting
    {
        return MailSetting::query()->firstOrCreate(
            ['id' => 1],
            ['mailer' => 'smtp', 'is_active' => false]
        );
    }

    /**
     * Update (and create if needed) the settings row.
     *
     * An empty `password` in the payload means "keep the existing password".
     */
    public function update(array $data): MailSetting
    {
        $setting = $this->getOrCreate();

        if (!array_key_exists('password', $data) || $data['password'] === null || $data['password'] === '') {
            unset($data['password']);
        }

        $setting->fill($data);
        $setting->save();

        return $setting;
    }

    /**
     * Override the runtime mail config with the active stored settings.
     * Safe to call on every request; a no-op when settings are inactive/empty.
     */
    public function apply(): void
    {
        $setting = MailSetting::query()->find(1);

        if (!$setting || !$setting->is_active || !$setting->host) {
            return;
        }

        $mailer = $setting->mailer ?: 'smtp';

        Config::set('mail.default', $mailer);
        Config::set("mail.mailers.{$mailer}.transport", 'smtp');
        Config::set("mail.mailers.{$mailer}.host", $setting->host);
        Config::set("mail.mailers.{$mailer}.port", $setting->port ?: 587);
        Config::set("mail.mailers.{$mailer}.username", $setting->username);
        Config::set("mail.mailers.{$mailer}.password", $setting->password);
        // Symfony mailer selects implicit TLS via the "smtps" scheme; STARTTLS
        // is negotiated automatically otherwise.
        Config::set("mail.mailers.{$mailer}.scheme", $setting->encryption === 'ssl' ? 'smtps' : null);
        Config::set("mail.mailers.{$mailer}.encryption", $setting->encryption);

        if ($setting->from_address) {
            Config::set('mail.from.address', $setting->from_address);
        }
        if ($setting->from_name) {
            Config::set('mail.from.name', $setting->from_name);
        }
    }

    /**
     * Send a plain test email to verify the configuration works.
     */
    public function sendTest(string $to): void
    {
        $this->apply();

        Mail::raw(
            'Ini email tes dari ECI IT Service. Jika kamu menerima pesan ini, konfigurasi SMTP sudah benar.',
            function ($message) use ($to) {
                $message->to($to)->subject('Tes Konfigurasi Email - ECI IT Service');
            }
        );
    }
}
