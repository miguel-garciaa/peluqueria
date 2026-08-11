<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantAdminAccess extends Command
{
    protected $signature = 'admin:grant {email : Correo de un usuario registrado}';

    protected $description = 'Concede acceso al panel Filament a un usuario existente';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error('No existe un usuario registrado con ese correo. Inicia sesión con Google primero.');

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info("Acceso de administrador concedido a {$user->email}.");

        return self::SUCCESS;
    }
}
