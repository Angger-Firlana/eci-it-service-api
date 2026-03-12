<?php

namespace Tests\Feature;

use App\Mail\UserContactAdmin;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactAdminTest extends TestCase
{
    public function test_contact_admin_queue_mode_returns_202_and_queues_mail(): void
    {
        Mail::fake();
        config()->set('mail.admin_email', 'admin@example.com');
        config()->set('mail.manager_email', 'manager@example.com');

        $response = $this->postJson('/api/contact-admin', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'message' => 'Hello admin',
            'mode' => 'queue',
        ]);

        $response->assertStatus(202);
        $response->assertJson([
            'mode' => 'queue',
        ]);

        Mail::assertQueued(UserContactAdmin::class, 2);
        Mail::assertQueued(UserContactAdmin::class, fn (UserContactAdmin $mail) => $mail->hasTo('admin@example.com'));
        Mail::assertQueued(UserContactAdmin::class, fn (UserContactAdmin $mail) => $mail->hasTo('manager@example.com'));
    }

    public function test_contact_admin_sync_mode_returns_200_and_sends_mail(): void
    {
        Mail::fake();
        config()->set('mail.admin_email', 'admin@example.com');
        config()->set('mail.manager_email', 'manager@example.com');

        $response = $this->postJson('/api/contact-admin', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'message' => 'Hello admin',
            'mode' => 'sync',
            'device' => 'Laptop',
            'device_model' => 'Dell XPS 13',
            'damages' => ['No power', 'Fan noise'],
            'service_request_id' => 123,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'mode' => 'sync',
        ]);

        Mail::assertSent(UserContactAdmin::class, 2);
        Mail::assertSent(UserContactAdmin::class, fn (UserContactAdmin $mail) => $mail->hasTo('admin@example.com'));
        Mail::assertSent(UserContactAdmin::class, fn (UserContactAdmin $mail) => $mail->hasTo('manager@example.com'));
    }

    public function test_contact_admin_mailable_renders_with_context_fields(): void
    {
        config()->set('app.frontend_url', 'https://app.example.com');

        $mailable = new UserContactAdmin(
            name: 'Test User',
            email: 'user@example.com',
            userMessage: 'Hello admin',
            device: 'Laptop',
            deviceModel: 'Dell XPS 13',
            damages: ['No power', 'Fan noise'],
            serviceRequestId: 123,
            serviceNumber: 'SR-000123',
        );

        $html = $mailable->render();

        $this->assertStringContainsString('Permintaan Servis Baru', $html);
        $this->assertStringContainsString('Dell XPS 13', $html);
        $this->assertStringContainsString('service-requests/123', $html);
        $this->assertStringContainsString('app.example.com/login', $html);
    }
}
