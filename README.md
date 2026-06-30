# ECI IT Service API

Backend API untuk sistem manajemen servis/repair aset IT. Dibangun dengan **Laravel 12** (PHP ^8.2) dan **MySQL**.

---

## Setup

```bash
git clone <repo-url>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan queue:work
```

---

## Autentikasi

**Laravel Sanctum** token-based. Semua endpoint (kecuali login & contact-admin) butuh header:

```
Authorization: Bearer <token>
```

---

## Response Envelope

**Success:**
```json
{
  "success": true,
  "code": 200,
  "message": "Success",
  "data": { },
  "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 72 }
}
```

**Error:**
```json
{
  "success": false,
  "code": 500,
  "message": "Error message"
}
```

---

## Status Workflow

```
REVIEW_IN_WORKSHOP --> REPAIR_IN_WORKSHOP --> REPAIR_IN_VENDOR
                                                    |
                                          WAITING_VENDOR_QUOTE
                                                    |
                                        WAITING_APPROVAL_ABOVE
                                         /              \
                              (all approved)    REJECTED_BY_ABOVE
                                    |
                              REPAIR_IN_VENDOR

COMPLETED / BAD_ASSET / CANCELLED bisa dari status mana saja.
```

| Kode Status | Label |
|---|---|
| `REVIEW_IN_WORKSHOP` | Requested |
| `REPAIR_IN_WORKSHOP` | Repair in Workshop |
| `REPAIR_IN_VENDOR` | Repair in Vendor |
| `WAITING_VENDOR_QUOTE` | Waiting Vendor Quote |
| `WAITING_APPROVAL_ABOVE` | Waiting Approval Above |
| `COMPLETED` | Completed |
| `BAD_ASSET` | Bad Asset |
| `REJECTED_BY_ABOVE` | Rejected by Above |
| `CANCELLED` | Cancelled |

---

## API Endpoints

### Auth

#### `POST /api/auth/login` — Login (public)

**Request:**
```json
{
  "username": "string (required)",
  "password": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Angger",
      "username": "angger",
      "email": "angger@example.com",
      "role": { "id": 1, "name": "admin" },
      "department": { "id": 1, "name": "IT", "code": "IT" },
      "created_at": "2026-06-26T10:00:00.000000Z",
      "updated_at": "2026-06-26T10:00:00.000000Z"
    },
    "token": "1|abc123plainTextToken"
  }
}
```

#### `GET /api/auth/me` — Data user saat ini (auth)

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "message": "User found",
  "data": {
    "id": 1,
    "name": "Angger",
    "username": "angger",
    "email": "angger@example.com",
    "role": { "id": 1, "name": "admin" },
    "department": { "id": 1, "name": "IT", "code": "IT" },
    "created_at": "2026-06-26T10:00:00.000000Z",
    "updated_at": "2026-06-26T10:00:00.000000Z"
  }
}
```

#### `POST /api/auth/logout` — Logout (auth)

No body. Response standard envelope.

---

### Service Requests

Prefix: `/api/service-requests` | Auth required

#### `GET /` — List (paginated)

Role: `user, admin, operator, manager`

**Query Params:**

| Param | Type | Keterangan |
|---|---|---|
| `per_page` | int | default 15, max 100 |
| `include` | string | eager-load relasi (comma-sep) |
| `user_id` | int | filter by user |
| `operator_id` | int | filter by operator |
| `status_id` | int | filter by status |
| `request_date` | date | filter by tanggal |
| `search` | string | cari nama user, service number, device, vendor |

**Available `include`:** `user`, `operator`, `status`, `vendor_approvals`, `locations`, `locations(active)`, `details.device.deviceModel`, `details.device.deviceModel.deviceType`

> User role `user` (staff) otomatis hanya lihat service request miliknya sendiri.

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "operator_id": null,
      "status_id": 3,
      "service_number": "SRV-20260626-0001",
      "created_at": "2026-06-26T10:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

#### `GET /summary` — List ringan (rate-limited 60/min)

Role: `user, admin, operator, manager`

#### `GET /stats` — Statistik per status

Role: `admin, operator, manager`

#### `GET /{id}` — Detail service request

Role: `user, admin, operator, manager`

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 2,
    "operator_id": 3,
    "status_id": 4,
    "service_number": "SRV-20260626-0001",
    "created_at": "2026-06-26T10:00:00.000000Z",
    "updated_at": "2026-06-26T10:00:00.000000Z",
    "user": {
      "id": 2,
      "name": "John",
      "username": "john",
      "email": "john@example.com",
      "departments": [{ "id": 1, "name": "Finance" }]
    },
    "operator": {
      "id": 3,
      "name": "Op1",
      "username": "op1",
      "email": "op1@example.com",
      "departments": [{ "id": 1, "name": "IT" }]
    },
    "status": { "id": 4, "name": "Requested", "code": "REVIEW_IN_WORKSHOP" },
    "service_request_details": [
      {
        "id": 10,
        "service_request_id": 1,
        "device_id": 5,
        "device_type_id": 2,
        "complaint": "Layar berkedip",
        "solution": null,
        "device": {
          "id": 5,
          "device_model_id": 8,
          "serial_number": "SN12345",
          "bad_asset": "0",
          "device_model": {
            "id": 8,
            "device_type_id": 2,
            "brand": "Dell",
            "model": "Latitude 5400",
            "device_type": { "id": 2, "name": "Laptop" }
          }
        },
        "device_type": { "id": 2, "name": "Laptop" },
        "complaint_images": [
          { "id": 20, "service_request_detail_id": 10, "image_path": "images/img1.jpg" }
        ]
      }
    ],
    "vendor_approvals": [],
    "audit_logs": [
      {
        "id": 1,
        "actor_id": 1,
        "entity_id": 1,
        "entity_type_id": 1,
        "action": "CREATE_REQUEST",
        "notes": "Service request created",
        "old_status_id": null,
        "new_status_id": 4,
        "created_at": "2026-06-26T10:00:00.000000Z",
        "actor": { "id": 1, "name": "Angger", "username": "angger" }
      }
    ]
  }
}
```

#### `POST /` — Buat service request baru

Role: `user, operator, admin, manager`

**Request:**
```json
{
  "operator_id": 3,
  "status_id": 1,
  "details": [
    {
      "device_id": 5,
      "device_type_id": 2,
      "complaint": "Layar berkedip saat dinyalakan",
      "complaint_images": ["<file upload>"]
    }
  ]
}
```

- `details` array required, minimal 1 item
- Tiap detail: `complaint` required, `device_id` atau `device_type_id` salah satu required
- `complaint_images`: array file upload (jpeg/png/jpg/gif/svg, max 2MB)
- User biasa tidak bisa set `user_id` (di-set otomatis)

**Response (201):** Full service request object

#### `PUT /{id}` — Update service request

Role: `operator`

**Request:**
```json
{
  "operator_id": 3,
  "status_id": 4,
  "log_notes": "Catatan opsional",
  "details": [
    {
      "id": 10,
      "device_id": 5,
      "complaint": "Layar berkedip + baterai bocor",
      "solution": "Ganti layar + baterai"
    }
  ]
}
```

> Status **COMPLETED** akan trigger email otomatis ke user.

#### `DELETE /{id}` — Hapus service request

Role: `admin, operator, technician, supervisor, manager, senior manager, ceo`

#### `GET /{id}/allowed-transitions` — Status yang diizinkan

Role: `user, admin, operator, manager`

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "data": [
    {
      "to_status": { "id": 2, "name": "Repair in Workshop", "code": "REPAIR_IN_WORKSHOP" },
      "code": "START_REPAIR_WORKSHOP",
      "description": "Mulai perbaikan di workshop",
      "roles": ["admin", "technician", "operator"]
    }
  ]
}
```

---

### Costs (Nested)

Prefix: `/api/service-requests/{serviceRequestId}/costs`

| Method | Path | Role |
|---|---|---|
| GET | `/` | admin, operator, manager |
| POST | `/` | admin, operator, manager |
| PUT | `/{costId}` | admin, operator, manager |
| GET | `/{costId}/attachment` | user, admin, operator, manager |
| DELETE | `/{costId}` | admin, operator, manager |

**POST Request:**
```json
{
  "cost_type_id": 1,
  "amount": 150000.00,
  "description": "Ganti layar LCD 14 inch",
  "image": "<file upload (opsional)>"
}
```

---

### Locations (Nested)

Prefix: `/api/service-requests/{serviceRequestId}/locations`

| Method | Path | Role |
|---|---|---|
| GET | `/` | user, admin, operator, manager |
| POST | `/` | admin, operator, manager |
| GET | `/{locationId}` | user, admin, operator, manager |
| PUT | `/{locationId}` | admin, operator, manager |
| DELETE | `/{locationId}` | admin, operator, manager |

**POST Request:**
```json
{
  "vendor_id": 1,
  "location_type": "WORKSHOP",
  "address": "Jl. Contoh No. 123",
  "phone_number": "08123456789",
  "is_active": true
}
```

---

### Approvals (Nested)

Prefix: `/api/service-requests`

| Method | Path | Role |
|---|---|---|
| GET | `/{id}/approvals` | admin, operator, manager |
| POST | `/{id}/approvals` | admin, operator, manager |
| PUT | `/{id}/approvals` | admin, operator, manager |
| DELETE | `/{id}/approvals/{approvalId}` | admin, operator, manager |
| GET | `/{id}/approvers` | admin, operator, manager |
| POST | `/approved/{approvalId}` | admin, operator, manager |
| POST | `/rejected/{approvalId}` | admin, operator, manager |
| POST | `/need-repair/{id}` | admin, operator, manager |
| POST | `/no-need-repair/{id}` | admin, operator, manager |

**POST /approved/{approvalId}:**
```json
{ "notes": "Disetujui" }
```

**POST /rejected/{approvalId}:**
```json
{ "notes": "Harga terlalu mahal" }
```

> Trigger email "Servis Ditolak" ke user (REJECTED_BY_ABOVE).

**POST /no-need-repair/{id}:** Tidak perlu body.
> Trigger email "Servis Selesai" ke user (COMPLETED).

---

### Cancellation (Nested)

Prefix: `/api/service-requests/{serviceRequestId}/cancellation`

| Method | Path | Role |
|---|---|---|
| GET | `/` | user, admin, operator, manager |
| POST | `/` | user, admin, operator, manager |
| PUT | `/{cancellationId}` | user, admin, operator, manager |
| DELETE | `/{cancellationId}` | user, admin, operator, manager |

**POST Request:**
```json
{ "reason": "Kerusakan sudah diperbaiki sendiri" }
```

---

### Invoices (via service-requests)

| Method | Path |
|---|---|
| GET | `/api/service-requests/{id}/download-invoice` |
| GET | `/api/service-requests/{id}/preview-invoice` |
| GET | `/api/service-requests/{id}/can-print-invoice` |

---

### Device Type / Device Model / Device

#### Device Type: `/api/device-type`

| Method | GET Role | Mutasi Role |
|---|---|---|
| GET | user, admin, operator | — |
| POST/PUT/DELETE | — | admin |

**POST/PUT:**
```json
{ "name": "Laptop" }
```

#### Device Model: `/api/device-model`

| Method | GET Role | Mutasi Role |
|---|---|---|
| GET | user, admin, operator | — |
| POST/PUT/PATCH/DELETE | — | admin |

**POST/PUT:**
```json
{ "device_type_id": 2, "brand": "Dell", "model": "Latitude 5400" }
```

#### Device: `/api/devices`

| Method | GET Role | Mutasi Role |
|---|---|---|
| GET | user, admin, operator, manager | — |
| POST/PUT/PATCH/DELETE | — | admin |

**POST/PUT:**
```json
{ "device_model_id": 8, "serial_number": "SN12345", "bad_asset": false }
```

---

### Vendors

Prefix: `/api/vendors` | Mutasi: `admin, operator`

**POST/PUT:**
```json
{ "name": "Vendor Komputer Jaya", "maps_url": "https://maps.google.com/...", "description": "Spesialis laptop" }
```

---

### Users

Prefix: `/api/users` | Mutasi: `admin`

**POST/PUT:**
```json
{
  "name": "John Doe",
  "username": "john",
  "email": "john@example.com",
  "password": "password123",
  "role_ids": [3],
  "department_ids": [1],
  "is_active": true
}
```

---

### Departments

Prefix: `/api/departments` | Mutasi: `admin`

**POST/PUT:**
```json
{ "name": "Finance", "code": "FIN" }
```

---

### Cost Types

Prefix: `/api/cost-types` | Mutasi: `admin`

**POST/PUT:**
```json
{ "name": "Spare Part", "description": "Komponen pengganti" }
```

---

### Notifications

Prefix: `/api/notifications` | Auth required

| Method | Path |
|---|---|
| GET | `/` | List notifikasi user saat ini |
| PUT | `/{id}/read` | Tandai sudah dibaca |

---

### Inbox Approvals

Prefix: `/api/inbox-approvals` | Role: `admin, operator, manager`

| Method | Path |
|---|---|
| GET | `/{statusId}` |
| GET | `/{statusId}/summary` (throttle: 60/min) |
| PUT | `/{id}/read` |

---

### References

Prefix: `/api/references` | Auth required

| Method | Path |
|---|---|
| GET | `/service-types` |
| POST | `/service-types` |
| GET | `/statuses` |
| GET | `/vendors` |
| GET | `/roles` |
| GET | `/departments` |
| GET | `/cost-types` |

---

### Mail Settings

Prefix: `/api/mail-settings` | Role: `admin`

#### `GET /` — Konfigurasi SMTP saat ini

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "mailer": "smtp",
    "host": "smtp.gmail.com",
    "port": 587,
    "username": "eci.it.service27@gmail.com",
    "encryption": "tls",
    "from_address": "eci.it.service27@gmail.com",
    "from_name": "ECI ASET SERVICE",
    "is_active": true,
    "has_password": true
  }
}
```

> `has_password` boolean — password tidak pernah dikembalikan.

#### `PUT /` — Update SMTP

**Request:**
```json
{
  "host": "smtp.gmail.com",
  "port": 587,
  "username": "email@example.com",
  "password": "app-password-here",
  "encryption": "tls",
  "from_address": "email@example.com",
  "from_name": "ECI IT Service",
  "is_active": true
}
```

- `password` kosong = pertahankan lama
- `host` & `from_address` required jika `is_active = true`

**Response (200):**
```json
{ "success": true, "code": 200, "message": "Pengaturan email disimpan", "data": { "..." } }
```

#### `POST /test` — Kirim email tes

**Request:**
```json
{ "to": "recipient@example.com" }
```

**Response (200):**
```json
{ "success": true, "code": 200, "message": "Email tes berhasil dikirim", "data": null }
```

---

### IT Emails — Penerima Email Request User

Prefix: `/api/mail-settings/it-emails` | Role: `admin`

Mengelola user siapa saja yang menerima email saat ada `contact-admin`.
Email diambil dari record `users` via relasi `user_id`.

#### `GET /` — List semua penerima

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "data": [
    {
      "id": 1,
      "user_id": 3,
      "is_active": true,
      "created_at": "2026-06-26T10:00:00.000000Z",
      "updated_at": "2026-06-26T10:00:00.000000Z",
      "user": {
        "id": 3,
        "name": "Habibi Cholis",
        "username": "habibi",
        "email": "habibi@example.com"
      }
    }
  ]
}
```

#### `POST /` — Tambah penerima

**Request:**
```json
{
  "user_id": 3,
  "is_active": true
}
```

- `user_id`: required, valid user id, **tidak bisa duplikat** (unique constraint)
- `is_active`: opsional, default `true`

**Response (201):**
```json
{
  "success": true,
  "code": 201,
  "message": "Penerima email berhasil ditambahkan",
  "data": {
    "id": 1,
    "user_id": 3,
    "is_active": true,
    "created_at": "2026-06-26T10:00:00.000000Z",
    "updated_at": "2026-06-26T10:00:00.000000Z",
    "user": {
      "id": 3,
      "name": "Habibi Cholis",
      "username": "habibi",
      "email": "habibi@example.com"
    }
  }
}
```

**Error — user_id sudah terdaftar (422):**
```json
{
  "success": false,
  "code": 422,
  "message": "Validation error",
  "errors": {
    "user_id": ["The user id has already been taken."]
  }
}
```

#### `PUT /{id}` — Toggle status aktif/nonaktif

**Request:**
```json
{ "is_active": false }
```

**Response (200):**
```json
{
  "success": true,
  "code": 200,
  "message": "Status penerima email diperbarui",
  "data": {
    "id": 1,
    "user_id": 3,
    "is_active": false,
    "created_at": "2026-06-26T10:00:00.000000Z",
    "updated_at": "2026-06-26T10:00:00.000000Z",
    "user": {
      "id": 3,
      "name": "Habibi Cholis",
      "username": "habibi",
      "email": "habibi@example.com"
    }
  }
}
```

#### `DELETE /{id}` — Hapus penerima

**Response (200):**
```json
{ "success": true, "code": 200, "message": "Penerima email berhasil dihapus", "data": null }
```

> **Fallback:** Jika tabel `set_email_it` kosong (tidak ada penerima), otomatis fallback ke `ADMIN_MAIL` di `.env`.

---

### Contact Admin

`POST /api/contact-admin` — **Public (no auth)**

User mengirim laporan ke tim IT. Email akan dikirim ke semua penerima aktif di `it-emails`.

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "message": "Tolong cek laptop saya, layar berkedip",
  "mode": "queue",
  "device": "Laptop",
  "deviceModel": "Dell Latitude 5400",
  "damages": ["Layar berkedip", "Baterai cepat habis"],
  "service_request_id": 1,
  "service_request_url": "http://10.101.228.173:5173/service-requests/1"
}
```

- `name`, `email`, `message` **required**
- `mode`: `"queue"` (default, async) atau `"sync"` (kirim langsung)
- Bisa pakai snake_case atau camelCase (`deviceModel` / `device_model`)

**Response (queue — 202):**
```json
{ "message": "Message queued successfully", "mode": "queue" }
```

**Response (sync — 200):**
```json
{ "message": "Message sent successfully", "mode": "sync" }
```

---

## Fitur Baru (Juni 2026)

### 1. Feedback Email Status Selesai / Tereject

Email otomatis dikirim ke user saat status berubah ke terminal:

| Status | Trigger | Subject Email |
|---|---|---|
| **COMPLETED** | `DeviceNoNeedRepair` / `UpdateServiceRequestWorkflow` | "Servis SRV-XXX-001 Selesai" |
| **REJECTED_BY_ABOVE** | `RejectVendorRequest` | "Servis SRV-XXX-001 Ditolak" |

Email dikirim via **queue** (ShouldQueue) dan dicatat di `audit_logs` dengan action `EMAIL_SENT`.

**Template email:** `resources/views/mail/status-change-notification.blade.php` — HTML, bahasa Indonesia, menampilkan service number, status badge, perangkat, catatan, dan link ke halaman detail servis.

**File baru (3):**
- `app/Mail/StatusChangeNotification.php`
- `resources/views/mail/status-change-notification.blade.php`
- `app/Domains/Notification/Actions/QueueStatusChangeNotificationEmail.php`

**File dimodifikasi (4):**
- `app/Domains/Approval/Actions/DeviceNoNeedRepair.php`
- `app/Domains/Approval/Actions/RejectVendorRequest.php`
- `app/Domains/ServiceRequest/Services/UpdateServiceRequestWorkflow.php`
- `app/Domains/Notification/Actions/CreateNotificationForServiceRequest.php`

### 2. Penerima Email Contact Admin (set_email_it)

Tabel `set_email_it` menggantikan hardcode `ADMIN_MAIL` / `MANAGER_MAIL` di `.env`. Admin bisa mengelola daftar penerima via API endpoint `/api/mail-settings/it-emails`.

**Tabel `set_email_it`:** `id`, `user_id` (FK unique), `is_active` (bool, default true), timestamps.

**File baru (9):**
- `database/migrations/2026_06_26_000001_create_set_email_it_table.php`
- `app/Models/SetEmailIt.php`
- `app/Domains/SetEmailIt/Actions/` (4 action classes)
- `app/Http/Controllers/SetEmailItController.php`
- `app/Http/Requests/SetEmailIt/` (2 form request classes)

**File dimodifikasi (4):**
- `routes/api.php`
- `app/Domains/ContactAdmin/Support/ContactAdminContextResolver.php`
- `app/Domains/ContactAdmin/Actions/QueueContactAdminMail.php`
- `app/Domains/ContactAdmin/Actions/SendContactAdminMail.php`

---

## Error Codes

| Code | Keterangan |
|---|---|
| 200 | Success |
| 201 | Created |
| 202 | Queued (email) |
| 401 | Unauthenticated |
| 403 | Forbidden (role tidak diizinkan) |
| 404 | Not found |
| 422 | Validation error |
| 429 | Too many requests (throttle) |
| 500 | Server error |
