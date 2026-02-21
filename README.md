# ECI IT Service API

Laravel-based API for IT service request management (devices, approvals, costs, invoices, notifications).

## Main Documentation

Read the complete implementation documentation in:

- `documentation.md`

That file contains:

- full endpoint catalog
- request/response contracts
- database schema
- all seeders and seeded data
- workflow details
- known implementation gaps

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

For queued email mode:

```bash
php artisan queue:work
```
