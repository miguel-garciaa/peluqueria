<?php

namespace Tests\Feature;

use Illuminate\Mail\Transport\ResendTransport;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailDiagnosticsTest extends TestCase
{
    public function test_it_reports_that_log_mailer_does_not_deliver_email(): void
    {
        config()->set('mail.default', 'log');

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('no entrega correos')
            ->assertFailed();
    }

    public function test_it_reports_a_missing_resend_api_key(): void
    {
        config()->set('mail.default', 'resend');
        config()->set('mail.from.address', 'reservas@peluqueria.es');
        config()->set('services.resend.key');

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('RESEND_API_KEY')
            ->assertFailed();
    }

    public function test_laravel_can_build_the_resend_transport(): void
    {
        config()->set('services.resend.key', 're_test_key_not_sent');

        $this->assertInstanceOf(
            ResendTransport::class,
            Mail::mailer('resend')->getSymfonyTransport(),
        );
    }
}
