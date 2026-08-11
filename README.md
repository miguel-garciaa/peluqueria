# Baskuñana Peluqueros

Web de Baskuñana Peluqueros integrada en Laravel 13 con React, TypeScript, Vite y Tailwind CSS. Incluye autenticación con Google, reserva de citas en tiempo real, correo de confirmación en cola y el área privada “Mis Citas”.

## Arquitectura de reservas

- PostgreSQL almacena `services`, `professionals`, `professional_service`, `professional_schedules`, `professional_calendar_entries` y `bookings`.
- `bookings` contiene cada reserva confirmada con cliente, servicio, profesional, inicio, fin, estado y referencia.
- `professional_schedules` define el horario semanal recurrente de cada profesional.
- `professional_calendar_entries` permite bloquear vacaciones, festivos o ausencias y crear aperturas especiales para una fecha concreta. Una apertura especial sustituye el horario semanal de ese día.
- La tabla antigua `appointment_requests` se conserva como `legacy_appointment_requests` para no perder datos, pero no participa en el flujo nuevo de reservas.
- Cada servicio define su duración; la disponibilidad solo ofrece huecos donde el servicio completo cabe dentro del horario.
- La creación bloquea las filas de profesionales dentro de una transacción y vuelve a comprobar solapamientos, evitando dobles reservas concurrentes.
- `AppointmentConfirmed` y `AppointmentCancelled` implementan `ShouldQueue`, se procesan mediante Redis en la cola `emails` y se entregan utilizando la API de Resend.
- Las reservas anuladas se conservan en PostgreSQL para auditoría, liberan el horario y dejan de aparecer en “Mis Citas”.
- La aplicación guarda timestamps en UTC y calcula la agenda con `BUSINESS_TIMEZONE=Europe/Madrid`.

## Desarrollo local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

Configura en `.env` las credenciales de PostgreSQL, Redis, Google y la API de Resend. `composer run dev` inicia Laravel, Vite, logs y el worker `emails,default` de Redis. Para usar SQLite durante desarrollo puedes sobrescribir `DB_CONNECTION=sqlite` y crear `database/database.sqlite`.

`MAIL_MAILER=log` no entrega correos: solo los escribe en `storage/logs/laravel.log`. En producción utiliza `MAIL_MAILER=resend`, una `RESEND_API_KEY` secreta y un remitente perteneciente al dominio verificado. Después de cambiar correo o Redis, limpia la configuración y reinicia tanto Octane como el worker de colas.

Si ejecutas los procesos por separado:

```bash
php artisan serve
php artisan queue:work redis --queue=emails,default --tries=3
npm run dev
```

Para volver a enviar una confirmación después de configurar Resend:

```bash
php artisan appointments:resend-confirmation REFERENCIA_DE_LA_CITA
```

Para comprobar sin mostrar contraseñas qué mailer está activo, si Redis responde, cuántos correos esperan y cuántos trabajos han fallado:

```bash
php artisan mail:diagnose
php artisan mail:diagnose --send-to=tu-correo@example.com
```

La segunda orden realiza primero las comprobaciones y después intenta un envío directo mediante el proveedor configurado. Si funciona pero las confirmaciones permanecen pendientes, el problema está en el worker de la cola `emails`.

## Comprobaciones

```bash
php artisan test
npm run test:frontend
npm run lint
npm run build
```

## Despliegue en la máquina virtual

Después de actualizar el repositorio:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
php artisan route:list --name=bookings
php artisan optimize
php artisan octane:reload
php artisan queue:restart
```

No arranques el worker de colas si alguno de los pasos anteriores falla. Con Octane/FrankenPHP, actualizar archivos o limpiar cachés no sustituye `octane:reload`: los workers mantienen en memoria la tabla de rutas con la que arrancaron. Si la recarga no está disponible en tu servicio, reinicia el proceso de FrankenPHP/Caddy mediante systemd o el supervisor configurado en el servidor.

El despliegue debe mantener un worker supervisado:

```bash
php artisan queue:work redis --queue=emails,default --sleep=1 --tries=3 --timeout=90
```

Si Octane todavía no está arrancado, usa el servidor que tengas configurado:

```bash
php artisan octane:start --host=127.0.0.1 --port=8000
```

Nginx debe usar como raíz el directorio `public`, servir directamente los archivos existentes y enviar el resto a Octane:

```nginx
root /ruta/al/proyecto/laravel/public;

location / {
    try_files $uri @octane;
}

location @octane {
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_pass http://127.0.0.1:8000;
}
```

La aplicación confía en el proxy únicamente cuando la conexión llega desde `127.0.0.1` o `::1`.

## Rutas

- `GET /` — landing page.
- `GET /login` — inicia el acceso con Google.
- `GET /reservas/disponibilidad` — devuelve huecos reales para fecha, servicio y profesional (autenticada).
- `POST /reservas` — crea y confirma una cita (autenticada).
- `GET /mis-citas` — área privada con las citas del usuario.
- `PATCH /mis-citas/{referencia}/anular` — anula una cita futura perteneciente al usuario autenticado.

## Catálogo y horarios

`php artisan db:seed` crea los siete servicios (incluido “Personalizado”), cuatro profesionales y sus horarios de lunes a sábado. Para adaptar turnos o especialidades, modifica `DatabaseSeeder` o gestiona las mismas tablas desde un panel administrativo. Los cambios semanales van en `professional_schedules`; las excepciones de fechas concretas van en `professional_calendar_entries`.
