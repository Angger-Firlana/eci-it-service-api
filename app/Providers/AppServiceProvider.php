<?php

namespace App\Providers;

use App\Domains\MailSetting\Services\MailSettingService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Apply admin-configured SMTP settings over the default mail config.
        // Guarded so it never breaks console commands run before migration.
        try {
            if (Schema::hasTable('mail_settings')) {
                app(MailSettingService::class)->apply();
            }
        } catch (Throwable $e) {
            // Ignore: fall back to .env mail config.
        }
    }
}
