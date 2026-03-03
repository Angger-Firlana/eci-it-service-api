# ECI IT Service API

Backend API untuk manajemen IT service request (device, approval, service cost, invoice, notification) berbasis Laravel 12.

## Documentation Index

- `documentation.md`: katalog endpoint, request/response, relasi data, dan alur bisnis.
- `reports.md`: changelog sesi implementasi per tanggal.
- `changes.md`: catatan perubahan tambahan/fix tertentu.

## Architecture Snapshot

Struktur saat ini menggunakan pendekatan domain modular:

- `app/Domains/*`: action/workflow/service per domain bisnis.
- `app/Http/Controllers`: endpoint layer (delegasi ke domain service/workflow).
- `app/Http/Requests`: validation layer per endpoint.
- `app/Models`: Eloquent model + relasi data.
- `routes/api.php`: registrasi route API.

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Untuk job queue (email/notifikasi async):

```bash
php artisan queue:work
```

## Development Commands

```bash
composer dev
php artisan test
```

## Notes

- Pastikan konfigurasi `.env` sudah sesuai (database, mail, queue, `FRONTEND_URL`, `ADMIN_MAIL`).
- Folder `public/images` dipakai untuk penyimpanan attachment/upload file.
