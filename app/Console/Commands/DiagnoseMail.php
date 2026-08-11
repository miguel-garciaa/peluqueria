<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Throwable;

class DiagnoseMail extends Command
{
    protected $signature = 'mail:diagnose {--send-to= : Envía una prueba directa a esta dirección}';

    protected $description = 'Comprueba de forma segura el proveedor de correo y la cola Redis';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $from = (string) config('mail.from.address');

        $this->components->twoColumnDetail('Mailer activo', $mailer);
        $this->components->twoColumnDetail('Remitente', $from);
        $this->components->twoColumnDetail('Conexión de cola', (string) config('queue.default'));

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->error("MAIL_MAILER={$mailer} no entrega correos: configura Resend u otro proveedor real.");

            return self::FAILURE;
        }

        if ($mailer === 'smtp') {
            $host = (string) config('mail.mailers.smtp.host');
            $this->components->twoColumnDetail('Servidor SMTP', $host.':'.config('mail.mailers.smtp.port'));

            if ($host === '' || str_contains($host, 'example.com')) {
                $this->error('MAIL_HOST no contiene un servidor SMTP real.');

                return self::FAILURE;
            }
        }

        if ($mailer === 'resend') {
            $apiKey = config('services.resend.key');

            if (! is_string($apiKey) || ! str_starts_with($apiKey, 're_')) {
                $this->error('RESEND_API_KEY no está configurada correctamente.');

                return self::FAILURE;
            }

            $this->components->twoColumnDetail('API de Resend', 'configurada');
        }

        if ($from === '' || str_contains($from, 'example.com')) {
            $this->error('MAIL_FROM_ADDRESS no contiene un remitente real.');

            return self::FAILURE;
        }

        try {
            $connection = (string) config('queue.connections.redis.connection', 'default');
            $redis = Redis::connection($connection);
            $redis->ping();
            $pending = (int) $redis->llen('queues:emails');
            $this->components->twoColumnDetail('Redis', 'conectado');
            $this->components->twoColumnDetail('Correos pendientes', (string) $pending);
        } catch (Throwable $exception) {
            $this->error('Redis no está disponible: '.$exception->getMessage());

            return self::FAILURE;
        }

        try {
            $failedTable = (string) config('queue.failed.table', 'failed_jobs');
            $this->components->twoColumnDetail('Trabajos fallidos', (string) DB::table($failedTable)->count());
        } catch (Throwable) {
            $this->warn('No se pudo consultar la tabla de trabajos fallidos.');
        }

        $recipient = $this->option('send-to');
        if ($recipient !== null) {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $this->error('La dirección indicada en --send-to no es válida.');

                return self::FAILURE;
            }

            try {
                Mail::raw('El envío de correo de Baskuñana Peluqueros funciona correctamente.', function (Message $message) use ($recipient): void {
                    $message->to($recipient)->subject('Prueba de correo de reservas');
                });
                $this->info("Prueba enviada directamente a {$recipient} mediante {$mailer}.");
            } catch (Throwable $exception) {
                $this->error('El proveedor de correo ha rechazado el envío: '.$exception->getMessage());

                return self::FAILURE;
            }
        }

        $this->info('La configuración de correo y Redis es utilizable.');

        return self::SUCCESS;
    }
}
