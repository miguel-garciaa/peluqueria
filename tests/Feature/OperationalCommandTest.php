<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class OperationalCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_grant_fails_cleanly_for_an_unknown_email(): void
    {
        $this->artisan('admin:grant', ['email' => 'missing@example.com'])
            ->assertFailed();
    }

    public function test_mail_diagnostics_rejects_placeholder_smtp_configuration(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.com',
            'mail.mailers.smtp.port' => 587,
            'mail.from.address' => 'reservas@salon.test',
        ]);

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('MAIL_HOST')
            ->assertFailed();
    }

    public function test_mail_diagnostics_rejects_placeholder_sender(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.valid.test',
            'mail.from.address' => 'sender@example.com',
        ]);

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('MAIL_FROM_ADDRESS')
            ->assertFailed();
    }

    public function test_mail_diagnostics_handles_redis_outages_without_throwing(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.valid.test',
            'mail.from.address' => 'sender@salon.test',
        ]);
        Redis::shouldReceive('connection')->once()->andThrow(new \RuntimeException('offline'));

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('Redis no está disponible')
            ->assertFailed();
    }

    public function test_mail_diagnostics_rejects_invalid_test_recipient_after_health_checks(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.valid.test',
            'mail.from.address' => 'sender@salon.test',
        ]);
        $redis = Mockery::mock();
        $redis->shouldReceive('ping')->once()->andReturn('PONG');
        $redis->shouldReceive('llen')->once()->with('queues:emails')->andReturn(0);
        Redis::shouldReceive('connection')->once()->andReturn($redis);

        $this->artisan('mail:diagnose', ['--send-to' => 'not-an-email'])
            ->expectsOutputToContain('no es válida')
            ->assertFailed();
    }

    public function test_resend_confirmation_fails_for_unknown_reference(): void
    {
        $this->artisan('appointments:resend-confirmation', ['reference' => '01KUNKNOWN'])
            ->expectsOutputToContain('No existe una cita')
            ->assertFailed();
    }
}
