# Baskuñana Peluqueros

Landing page de Baskuñana Peluqueros integrada en Laravel con React, TypeScript, Vite, Tailwind CSS y Three.js. El formulario de reserva envía solicitudes a Laravel y las guarda en `appointment_requests`.

## Desarrollo local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run dev
```

En otra terminal, inicia Laravel u Octane. La configuración local incluida usa SQLite.

## Comprobaciones

```bash
php artisan test
npm run test:frontend
npm run lint
npm run build
```

## Despliegue en la máquina virtual

Después de actualizar el repositorio:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan octane:reload
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
- `POST /reservas` — registra una solicitud de cita y devuelve JSON.
