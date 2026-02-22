# ECI IT Service API - Complete Implementation Documentation

Version: code-accurate snapshot (generated from current source)
Date: 2026-02-22
Framework: Laravel 12 + Sanctum

This document is intentionally written as a full implementation reference so frontend/backend integrators can work without opening the codebase.

---

## 1. Scope and Important Notes

This document covers:

1. Full API route catalog (all `/api/*` endpoints).
2. Request/response contracts based on current code.
3. Authentication and error envelope behavior.
4. Current database schema (migrations as source of truth).
5. All seeders, seeded values, and default test users.
6. Business workflows (service request lifecycle, approval flow, invoice flow).
7. Known implementation gaps/bugs that affect integration.

Important:

- This is a **code-accurate** spec, not idealized behavior.
- Some endpoints have implementation defects; those are listed explicitly in `Known Gaps` so you can avoid integration surprises.

---

## 2. Quick Start (Local)

### 2.1 Prerequisites

- PHP 8.2+
- Composer
- MySQL or SQLite (project defaults to SQLite in `.env.example`)
- Node.js (for Vite assets)

### 2.2 Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

If you use queue mode for email:

```bash
php artisan queue:work
```

### 2.3 Required env variables

Core:

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_DEBUG`
- `APP_URL`
- `FRONTEND_URL`

Database:

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Mail:

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `ADMIN_MAIL` (critical for `/api/contact-admin` and admin notification email)

Queue:

- `QUEUE_CONNECTION`

---

## 3. API Conventions

### 3.1 Base URL

All business endpoints are under:

```text
{BASE_URL}/api
```

Example local base URL:

```text
http://localhost:8000/api
```

### 3.2 Authentication

- API uses Laravel Sanctum token (Bearer token).
- Most endpoints use `auth:sanctum` middleware.

Header:

```http
Authorization: Bearer {token}
Accept: application/json
```

### 3.3 Response envelope types

There are multiple response styles in this codebase:

1. Standard helper (`APIResponse::success/error`):

```json
{
  "success": true,
  "data": {},
  "message": "...",
  "meta": {}
}
```

2. Raw JSON from controller (not wrapped), especially in `VendorController`.

3. Custom JSON for contact-admin endpoint:

```json
{
  "message": "Message queued successfully",
  "mode": "queue"
}
```

4. Binary/file response (invoice PDF, attachment file).

### 3.4 Global error handling (bootstrap/app.php)

Central exception rendering maps to:

- `404`: `{"success":false,"message":"Data Not Found"}`
- `405`: `Method Not Allowed`
- `401`: `Unauthorized`
- `403`: `Forbidden`
- `422`: `Validation Error` + `errors`
- `500`: database/internal server errors with exception message appended

### 3.5 Pagination

For endpoints using `APIResponse::formatPagination`:

```json
"meta": {
  "current_page": 1,
  "last_page": 10,
  "per_page": 15,
  "total": 150,
  "from": 1,
  "to": 15
}
```

### 3.6 Timezone

- Application timezone: `UTC` (`config/app.php`).

---

## 4. Full API Route Catalog

### 4.1 Public routes (no `auth:sanctum` middleware)

- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout` (public route, but meaningful only with auth token)
- `POST /api/contact-admin`
- `GET /api/export-invoice/{id}`

### 4.2 Authenticated routes

Auth:

- `GET /api/auth/me`

Device types:

- `GET /api/device-type`
- `GET /api/device-type/{id}`
- `POST /api/device-type`
- `PUT /api/device-type/{id}`
- `DELETE /api/device-type/{id}`

Device models:

- `GET /api/device-model`
- `GET /api/device-model/{id}`
- `POST /api/device-model`
- `PUT /api/device-model/{id}`
- `PATCH /api/device-model/{id}`
- `DELETE /api/device-model/{id}`

Devices:

- `GET /api/devices`
- `GET /api/devices/{id}`
- `POST /api/devices`
- `PUT /api/devices/{id}`
- `PATCH /api/devices/{id}`
- `DELETE /api/devices/{id}`

Service requests core:

- `GET /api/service-requests`
- `GET /api/service-requests/stats`
- `GET /api/service-requests/{id}`
- `POST /api/service-requests`
- `PUT /api/service-requests/{id}`
- `DELETE /api/service-requests/{id}`
- `GET /api/service-requests/{id}/allowed-transitions`
- `GET /api/service-requests/{id}/download-invoice`
- `GET /api/service-requests/{id}/preview-invoice`
- `GET /api/service-requests/{id}/can-print-invoice`

Service request approvals/actions:

- `POST /api/service-requests/{serviceRequestId}/approvals`
- `GET /api/service-requests/{serviceRequestId}/approvals`
- `PUT /api/service-requests/{serviceRequestId}/approvals`
- `DELETE /api/service-requests/{serviceRequestId}/approvals/{approvalId}`
- `GET /api/service-requests/{serviceRequestId}/approvers`
- `POST /api/service-requests/approved/{approvalId}`
- `POST /api/service-requests/rejected/{approvalId}`
- `POST /api/service-requests/need-repair/{serviceRequestId}`
- `POST /api/service-requests/no-need-repair/{serviceRequestId}`

Service request costs:

- `GET /api/service-requests/{serviceRequestId}/costs`
- `POST /api/service-requests/{serviceRequestId}/costs`
- `PUT /api/service-requests/{serviceRequestId}/costs/{costId}`
- `DELETE /api/service-requests/{serviceRequestId}/costs/{costId}`
- `GET /api/service-requests/{serviceRequestId}/costs/{costId}/attachment`

Service request locations:

- `GET /api/service-requests/{serviceRequestId}/locations`
- `POST /api/service-requests/{serviceRequestId}/locations`
- `GET /api/service-requests/{serviceRequestId}/locations/{locationId}`
- `PUT /api/service-requests/{serviceRequestId}/locations/{locationId}`
- `DELETE /api/service-requests/{serviceRequestId}/locations/{locationId}`

Service request cancellation:

- `GET /api/service-requests/{serviceRequestId}/cancellation`
- `POST /api/service-requests/{serviceRequestId}/cancellation`
- `PUT /api/service-requests/{serviceRequestId}/cancellation/{cancellationId}`
- `DELETE /api/service-requests/{serviceRequestId}/cancellation/{cancellationId}`

Reference:

- `GET /api/references/service-types`
- `POST /api/references/service-types`
- `GET /api/references/statuses`
- `GET /api/references/vendors`
- `GET /api/references/roles`
- `GET /api/references/departments`
- `GET /api/references/cost-types`

Master data CRUD:

- Departments: `GET/POST/GET{id}/PUT{id}/DELETE{id} /api/departments`
- Users: `GET/POST/GET{id}/PUT{id}/DELETE{id} /api/users`
- Vendors: `GET/POST/GET{id}/PUT{id}/DELETE{id} /api/vendors`
- Cost Types: `GET/POST/GET{id}/PUT{id}/DELETE{id} /api/cost-types`

Invoices:

- `GET /api/invoices`
- `GET /api/invoices/{id}`
- `GET /api/invoices/{id}/print`
- `GET /api/invoices/{id}/download` (route exists; implementation gap, see Known Gaps)

Notifications:

- `GET /api/notifications`
- `PUT /api/notifications/{id}/read`

Inbox approvals:

- `GET /api/inbox-approvals/{statusId}`
- `PUT /api/inbox-approvals/{id}/read`

Misc:

- `GET /api/user` (default closure: returns authenticated user object)

---

## 5. Data Model (Current Schema)

This section describes current schema based on migration files.

### 5.1 Core/auth/framework tables

#### `users`

- `id` (PK)
- `name` (string)
- `email` (string, unique)
- `email_verified_at` (timestamp nullable)
- `password` (string)
- `pin` (string nullable)
- `remember_token` (nullable)
- `is_active` (boolean, default true)
- `created_at`, `updated_at`

#### `password_reset_tokens`

- `email` (PK)
- `token`
- `created_at`

#### `sessions`

- `id` (PK)
- `user_id` (indexed, nullable)
- `ip_address`
- `user_agent`
- `payload`
- `last_activity` (indexed)

#### `personal_access_tokens`

- Standard Sanctum table (`tokenable_type`, `tokenable_id`, `token`, abilities, expiry fields)

#### Queue/cache infra tables

- `jobs`, `job_batches`, `failed_jobs`
- `cache`, `cache_locks`

### 5.2 Master data and workflow tables

#### `roles`

- `id` (PK)
- `name`
- timestamps

#### `user_roles`

- `id` (PK)
- `user_id` (FK -> `users.id`)
- `role_id` (FK -> `roles.id`)
- timestamps

#### `departments`

- `id` (PK)
- `name`
- `code` (nullable)
- timestamps

#### `user_departments`

- `id` (PK)
- `user_id` (FK, cascade delete)
- `department_id` (FK, cascade delete)
- timestamps

#### `entity_types`

- `id` (PK)
- `code` (unique)
- `name`

#### `statuses`

- `id` (PK)
- `entity_type_id` (FK -> `entity_types.id`)
- `code`
- `name`
- timestamps
- unique composite: `(entity_type_id, code)`

#### `status_transitions`

- `id`
- `code`
- `from_status_id` (FK -> statuses)
- `to_status_id` (FK -> statuses)
- `description`
- timestamps

#### `status_transition_roles`

- `id`
- `status_transition_id` (FK)
- `role_id` (FK)
- timestamps

#### `audit_logs`

- `id`
- `actor_id` (FK -> users)
- `entity_id` (unsigned bigint)
- `entity_type_id` (FK -> entity_types)
- `action` (string)
- `notes` (nullable text)
- `old_status_id` (nullable FK -> statuses, `nullOnDelete`)
- `new_status_id` (nullable FK -> statuses, `nullOnDelete`)
- `created_at` (timestamp)
- no `updated_at`

#### `device_types`

- `id`
- `name`
- timestamps

#### `device_models`

- `id`
- `device_type_id` (FK -> `device_types.id`)
- `brand`
- `model`
- timestamps

#### `devices`

- `id`
- `device_model_id` (FK)
- `serial_number` (unique)
- `bad_asset` (boolean default false)
- timestamps

#### `service_types`

- `id`
- `name`
- timestamps

#### `service_requests`

- `id`
- `user_id` (nullable FK -> users)
- `admin_id` (nullable FK -> users)
- `service_number` (unique)
- `status_id` (FK -> statuses)
- timestamps

Important:

- Column `service_type_id` was removed from this table.
- Columns `request_date` and `estimated_date` are not present in migration schema.

#### `service_request_details`

- `id`
- `service_request_id` (FK)
- `device_id` (FK)
- `complaint` (text)
- `solution` (nullable string length 8000)
- timestamps

#### `complaint_images`

- `id`
- `service_request_detail_id` (FK, cascade delete)
- `image_path` (string)
- timestamps

#### `vendors`

- `id`
- `name`
- `maps_url` nullable
- `description` nullable
- timestamps

#### `service_locations`

- `id`
- `service_request_id` (FK)
- `vendor_id` (nullable FK)
- `location_type` (`internal|external` expected by validation)
- `address` nullable
- `phone_number` (required at DB level in latest migration)
- `is_active` boolean default true
- timestamps

Note: older columns (`city`, `province`, `postal_code`, `maps_url`) were dropped by migration.

#### `service_cancellations`

- `id`
- `service_request_id` (FK)
- `cancelled_by` (FK -> users)
- `reason` (text)
- timestamps

#### `cost_types`

- `id`
- `code` (unique)
- `name`
- timestamps

#### `service_costs`

- `id`
- `service_request_id` (FK)
- `cost_type_id` (FK)
- `amount` decimal(15,2)
- `description` nullable
- `image_path` string(1000) (added later; migration defines non-null)
- timestamps

#### `condition_type_data`

- `id`
- `type_data`
- timestamps

#### `condition_types`

- `id`
- `condition_type_data_id` (FK)
- `code` unique
- `name`
- timestamps

#### `approval_policies`

- `id`
- `entity_type_id` (FK)
- `condition_type_id` (FK)
- `condition_value`
- `is_active` boolean default true
- timestamps

#### `approval_policy_steps`

- `id`
- `approval_policy_id` (FK)
- `step_order` int
- `role_id` (FK)
- `is_mandatory` boolean default true
- timestamps

#### `vendor_approvals`

- `id`
- `approval_policy_id` (FK)
- `approval_policy_step_id` (FK)
- `service_request_id` (FK)
- `approver_id` (FK -> users)
- `approved_at` nullable timestamp
- `assigned_by` (FK -> users)
- `assigned_at` timestamp
- `status_id` (FK -> statuses)
- `notes` text (required)
- `read_at` nullable datetime
- timestamps

#### `invoices`

- `id`
- `invoice_number` unique
- `service_request_id` (FK)
- `issue_date` (date)
- `due_date` (date)
- `total_amount` decimal(15,2)
- `status_id` (FK -> statuses)
- timestamps

#### `notifications`

- `id`
- `user_id` (FK -> users, cascade delete)
- `service_request_id` nullable (FK -> service_requests, cascade delete)
- `title`
- `message`
- `read_at` nullable timestamp
- timestamps

---

## 6. Seeder Reference (All Seeders)

Seeder run order from `DatabaseSeeder`:

1. `RoleSeeder`
2. `EntityTypeSeeder`
3. `StatusSeeder`
4. `StatusTransitionSeeder`
5. `DeviceTypeSeeder`
6. `DeviceModelSeeder`
7. `ServiceTypeSeeder`
8. `CostTypeSeeder`
9. `ConditionTypeDataSeeder`
10. `ConditionTypeSeeder`
11. `ApprovalPolicySeeder`
12. `VendorSeeder`
13. `DepartmentSeeder`
14. `UserSeeder`

### 6.1 `RoleSeeder`

Seeded roles (fresh DB typical IDs):

1. `admin`
2. `operator`
3. `user`
4. `technician`
5. `supervisor`
6. `manager`
7. `senior manager`
8. `ceo`

### 6.2 `EntityTypeSeeder`

1. `SERVICE_REQUEST`
2. `VENDOR_APPROVAL`
3. `INVOICE`

### 6.3 `StatusSeeder`

Service request statuses:

1. `REVIEW_IN_WORKSHOP` -> Requested
2. `REPAIR_IN_WORKSHOP` -> Repair in Workshop
3. `REPAIR_IN_VENDOR` -> Repair in Vendor
4. `WAITING_APPROVAL_ABOVE` -> Waiting Approval Above
5. `COMPLETED` -> Completed
6. `BAD_ASSET` -> Bad Asset
7. `CANCELLED` -> Cancelled

Vendor approval statuses:

8. `PENDING`
9. `APPROVED`
10. `REJECTED`

Invoice statuses:

11. `DRAFT`
12. `SENT`
13. `PAID`
14. `OVERDUE`

### 6.4 `StatusTransitionSeeder`

Transitions seeded:

- `REVIEW_IN_WORKSHOP -> REPAIR_IN_WORKSHOP` (`START_REPAIR_WORKSHOP`) roles: admin, technician, operator
- `REVIEW_IN_WORKSHOP -> COMPLETED` (`COMPLETE_AFTER_REVIEW`) roles: admin, technician, operator
- `REVIEW_IN_WORKSHOP -> BAD_ASSET` (`MARK_BAD_ASSET`) roles: admin, technician, operator
- `REVIEW_IN_WORKSHOP -> CANCELLED` (`CANCEL_REQUEST`) roles: user, admin, operator
- `WAITING_APPROVAL_ABOVE -> REPAIR_IN_VENDOR` (`APPROVE_QUOTE_ABOVE`) role: first available superior (`supervisor/manager/director/ceo`; in current seeds typically `supervisor`)
- `WAITING_APPROVAL_ABOVE -> REVIEW_IN_WORKSHOP` (`REJECT_QUOTE_ABOVE`) same superior role
- `REPAIR_IN_WORKSHOP -> REPAIR_IN_VENDOR` (`MOVE_TO_VENDOR`) roles: admin, technician, operator
- `REPAIR_IN_WORKSHOP -> COMPLETED` (`COMPLETE_WORK`) roles: admin, technician, operator
- `REPAIR_IN_WORKSHOP -> BAD_ASSET` (`MARK_BAD_ASSET`) roles: admin, technician, operator
- `REPAIR_IN_VENDOR -> WAITING_APPROVAL_ABOVE` (`REQUEST_APPROVAL_ABOVE`) roles: admin, operator
- `REPAIR_IN_VENDOR -> COMPLETED` (`COMPLETE_VENDOR_WORK`) roles: admin, operator
- `REPAIR_IN_VENDOR -> BAD_ASSET` (`MARK_BAD_ASSET`) roles: admin, operator

### 6.5 `DeviceTypeSeeder`

Seeded names:

- Laptop, Desktop, Printer, Scanner, Monitor, Server, Router, Switch, Mobile Phone, Tablet

### 6.6 `DeviceModelSeeder`

Seeded combinations include:

- Laptop: Dell Latitude 5420, HP EliteBook 840 G8, Lenovo ThinkPad T14, Apple MacBook Pro 14"
- Desktop: Dell OptiPlex 7090, HP EliteDesk 800 G6, Lenovo ThinkCentre M70q
- Printer: HP LaserJet Pro M404n, Canon imageCLASS MF244dw, Brother HL-L2350DW
- Monitor: Dell UltraSharp U2422H, HP EliteDisplay E243, LG 27UL850-W

### 6.7 `ServiceTypeSeeder`

- Hardware Repair
- Software Installation
- Network Setup
- Data Recovery
- System Maintenance
- Equipment Installation
- Troubleshooting
- Upgrade Service
- Security Audit
- Backup Setup

### 6.8 `CostTypeSeeder`

- `SPAREPART` -> Sparepart
- `SERVICE_FEE` -> Service Fee
- `CANCELLATION` -> Cancellation
- `TRANSPORT` -> Transport
- `OTHER` -> Other

### 6.9 `ConditionTypeDataSeeder`

- Device Type
- Service Type
- Cost Range

### 6.10 `ConditionTypeSeeder`

- `DEVICE_TYPE`
- `SERVICE_TYPE`
- `COST_RANGE`

### 6.11 `ApprovalPolicySeeder`

Policies seeded:

1. Condition: Device Type = `Laptop`
   - Step 1: supervisor (mandatory)
   - Step 2: manager (mandatory)

2. Condition: Service Type = `Software Installation`
   - Step 1: supervisor (mandatory)

3. Condition: Cost Range = `>1000000`
   - Step 1: manager (mandatory)
   - Step 2: senior manager (mandatory)

4. Condition: Cost Range = `<1000000`
   - manager (non-mandatory, step_order 1)
   - senior manager (non-mandatory, step_order 1)

### 6.12 `VendorSeeder`

Seeded vendors:

- Tech Solutions Inc.
- Hardware Pro Services
- Network Experts LLC
- Software Masters
- IT Support Plus

### 6.13 `DepartmentSeeder`

- IT, HR, FIN, GA, OPS

### 6.14 `UserSeeder` (default local credentials)

Important:

- Passwords are hashed in seeder from plaintext shown below.
- `pin` in seeder is inserted as plaintext string.

| Name | Email | Plain Password | Role | Department |
|---|---|---|---|---|
| Administrator | admin@gmail.com | admin123 | admin | IT |
| ALI | it.ali@gmail.com | ali123 | operator | IT |
| John Doe | john.doe@gmail.com | user123 | user | HR |
| Jane Smith | jane.smith@gmail.com | user123 | user | FIN |
| Tech Wilson | tech.wilson@gmail.com | tech123 | technician | IT |
| Service Brown | service.brown@gmail.com | tech123 | technician | IT |
| Supervisor | supervisor@gmail.com | atasan123 | supervisor | GA |
| Manager | manager@gmail.com | manager123 | manager | IT |
| Senior Manager | senior.manager@gmail.com | manager123 | senior manager | IT |
| CEO | ceo@gmail.com | ceo123 | ceo | IT |

---

## 7. Business Workflow Details

### 7.1 Service request creation

Source: `ServiceRequestService::createServiceRequest`.

Flow:

1. Validate payload (`StoreServiceRequest`).
2. Enforce device idempotency (`ServiceRequestIdempotencyHandler`).
3. Create service request header:
   - `service_number`: auto format `SR{YYYYMMDD}{0001..}`
   - `status_id`: forced to status `REVIEW_IN_WORKSHOP`
   - Admin role handling:
     - admin user can set `user_id`
     - normal user only for self
4. Create detail rows and optional complaint images.
5. Write audit log `CREATE_REQUEST`.
6. Queue admin email notification.

### 7.2 Device idempotency rules

`ServiceRequestIdempotencyHandler` prevents:

- duplicate same device in one request (`device_id` or same `serial_number`)
- using a device that is still attached to another active service request
  - active means status is not `COMPLETED` and not `CANCELLED`

Validation errors are returned under `details` key.

### 7.3 Status transitions by role

Allowed transitions endpoint reads `status_transitions` + `status_transition_roles` and current authenticated user roles.

### 7.4 Approval flow

`ServiceRequestApprovalService`:

- Selects approval policy by total service cost using `COST_RANGE` condition:
  - `>1000000` or `<1000000`
- Creates `vendor_approvals` for selected approvers with status `PENDING`.
- Approve/reject handled in `ApprovalService` updates `vendor_approvals.status_id` and service request status.
- Audit logs for service request detail now include vendor approval actions (entity type `VENDOR_APPROVAL`), so timeline includes approval activity.

### 7.5 Notification generation

`NotificationService::createNotificationForServiceRequest` sends user notification when status code is:

- `COMPLETED`
- `BAD_ASSET`
- `CANCELLED`

### 7.6 Invoice behavior

Invoice-related logic exists in:

- `InvoiceService`
- `ExportInvoiceService`

PDF export endpoints return generated PDF from Blade templates in `resources/views/invoice/*`.

### 7.7 File storage behavior

- Complaint images: saved to `public/images`, path stored as `images/{filename}`.
- Service cost attachments: saved to `public/images`, `image_path` stores filename.

---

## 8. Endpoint Contract Details

## 8.1 Authentication

### `POST /api/auth/login`

- Auth: no
- Body:
  - `email` required email
  - `password` required string
- Success: `200`
  - returns user profile (`id,name,email,role,department,created_at,updated_at`) + token
- Invalid credentials: `401`
  - message `Invalid credentials`
- Validation error: `422`

Note:

- Due controller condition bug, auth errors are returned via `APIResponse::success` envelope (`success: true`) but with HTTP 4xx.

### `POST /api/auth/register`

- Auth: no
- Body:
  - `name` required max 255
  - `email` required unique
  - `password` required min 8
  - `pin` optional min 6
  - `role_id` required exists roles
- Success: `201`
  - user + token

### `POST /api/auth/logout`

- Auth middleware: no (public route)
- Behavior:
  - if token valid user found -> delete all user tokens
  - otherwise -> 401 `Unauthenticated`

### `GET /api/auth/me`

- Auth: yes
- Returns authenticated user profile with first role and first department.

## 8.2 Contact Admin

### `POST /api/contact-admin`

- Auth: no
- Body:
  - `name` required
  - `email` required
  - `message` required
  - `attachmentPath` optional string (not file upload)
  - `mode` optional: `queue` (default) or `sync`
  - optional context: `device`, `device_model`/`deviceModel`, `damages[]`, `service_request_id`, `service_request_url`
- Responses:
  - `202` queue mode
  - `200` sync mode
  - `500` if send fails

## 8.3 Device Types

### `GET /api/device-type`

- Auth: yes
- Query: `search`, `page`, `per_page`
- Returns paginated array + `meta`.

### `GET /api/device-type/{id}`

- Auth: yes
- Returns single device type.

### `POST /api/device-type`

- Auth: yes
- Body: `name` required unique
- Returns `201`.

### `PUT /api/device-type/{id}`

- Auth: yes
- Body: `name` required unique
- Returns `200`.

### `DELETE /api/device-type/{id}`

- Auth: yes
- Returns `200`, `data: null`.

## 8.4 Device Models

### `GET /api/device-model`

- Auth: yes
- Query: `keyword` (search by model only), `page`, `per_page`

### `GET /api/device-model/{id}`

- Auth: yes

### `POST /api/device-model`

- Auth: yes
- Body:
  - `device_type_id` required
  - `brand` required
  - `model` required

### `PUT /api/device-model/{id}`

- Auth: yes
- Body all optional (`device_type_id`, `brand`, `model`)

### `PATCH /api/device-model/{id}`

- Auth: yes
- Body same optional fields.

Known behavior:

- Patch implementation updates in-memory attributes but does not `save()`, so changes are not persisted.

### `DELETE /api/device-model/{id}`

- Auth: yes

## 8.5 Devices

### `GET /api/devices`

- Auth: yes
- Query:
  - `serial-number` exact match
  - `brand` exact match via relation
  - `model` partial match via relation
  - `bad_asset` boolean (`true/false/1/0`)
  - `per_page` (default 15)
- Returns paginated list with `device_model` summary.

### `GET /api/devices/{id}`

- Auth: yes

### `POST /api/devices`

- Auth: yes
- Body:
  - `device_model_id` required
  - `serial_number` required unique
  - `bad_asset` optional boolean

### `PUT /api/devices/{id}`

- Auth: yes
- Body optional:
  - `device_model_id`
  - `serial_number` (unique)
  - `bad_asset`

### `PATCH /api/devices/{id}`

- Auth: yes
- Partial update equivalent.

### `DELETE /api/devices/{id}`

- Auth: yes

## 8.6 Service Requests (Core)

### `GET /api/service-requests`

- Auth: yes
- Query filters:
  - `user_id`
  - `admin_id`
  - `status_id`
  - `request_date` (applied against `created_at` date)
  - `search` (keyword against user name, service number, device model, serial number, vendor name, location phone)
  - `per_page`
- Returns paginated with relations:
  - user/admin/status
  - detail + device + device model

### `GET /api/service-requests/stats`

- Auth: yes
- Returns:
  - `total`
  - `by_status[]` with status name/code/count
  - `recent` top 5 by `created_at`

### `GET /api/service-requests/{id}`

- Auth: yes
- Returns full detail with:
  - user + department
  - admin + department
  - status
  - details + device + model + complaint_images
- vendor approvals
- audit logs
- computed `timeline`

Notes:

- `audit_logs` and `timeline` include vendor approval actions, not only service request status changes.

### `POST /api/service-requests`

- Auth: yes
- Body:
  - `admin_id` optional
  - `user_id` optional for admin; prohibited for non-admin
  - `request_date` optional (validated, not persisted in current schema)
  - `status_id` optional
  - `status_code` optional (mapped to `status_id`)
  - `details` required array
  - `details.*.device_id` optional existing device
  - if no `device_id`, require:
    - `details.*.device_type_id`
    - `details.*.brand`
    - `details.*.model`
    - `details.*.serial_number`
  - `details.*.complaint` required
  - `details.*.complaint_images[]` optional files (`jpeg/png/jpg/gif/svg`, max 2MB each)

Behavior:

- Service number auto-generated.
- Status forced to `REVIEW_IN_WORKSHOP`.
- Device idempotency checks executed.

### `PUT /api/service-requests/{id}`

- Auth: yes
- Body optional:
  - `admin_id`, `user_id`, `request_date`, `estimated_date`
  - `status_id` or `status_code`
  - `details[]` with optional `id`, `device_id`, `device_type_id`, `brand`, `model`, `serial_number`, `complaint`, `solution`, `complaint_images[]`
  - `log_notes`

### `DELETE /api/service-requests/{id}`

- Auth: yes
- Rule: cannot delete if current status is `COMPLETED`.

### `GET /api/service-requests/{id}/allowed-transitions`

- Auth: yes
- Returns statuses reachable from current status based on current user roles.

### `GET /api/service-requests/{id}/can-print-invoice`

- Auth: yes
- Returns:

```json
{
  "success": true,
  "data": {
    "can_print": true
  },
  "message": "Success"
}
```

### `GET /api/service-requests/{id}/download-invoice`

- Auth: yes
- Returns binary PDF download.

### `GET /api/service-requests/{id}/preview-invoice`

- Auth: yes
- Returns preview PDF.

### `GET /api/export-invoice/{id}`

- Auth: no
- Public invoice download route (same export service).

## 8.7 Service Request Costs

### `GET /api/service-requests/{serviceRequestId}/costs`

- Auth: yes
- Returns all costs with `cost_type` relation.

### `POST /api/service-requests/{serviceRequestId}/costs`

- Auth: yes
- Body:
  - `cost_type_id` required
  - `amount` required numeric min 0
  - `description` nullable
  - `image` nullable file (`jpeg/png/jpg/gif/svg/pdf`, max 10MB)

### `PUT /api/service-requests/{serviceRequestId}/costs/{costId}`

- Auth: yes
- Body same as store but optional.

### `DELETE /api/service-requests/{serviceRequestId}/costs/{costId}`

- Auth: yes

### `GET /api/service-requests/{serviceRequestId}/costs/{costId}/attachment`

- Auth: yes
- Returns attached image/pdf file.

## 8.8 Service Request Locations

### `GET /api/service-requests/{serviceRequestId}/locations`

- Auth: yes
- Returns all locations for that request.

### `GET /api/service-requests/{serviceRequestId}/locations/{locationId}`

- Auth: yes

### `POST /api/service-requests/{serviceRequestId}/locations`

- Auth: yes
- Behavior:
  - if active location already exists, endpoint updates it (200)
  - else creates new location (201)
- Body rules:
  - `location_type` required `internal|external`
  - `vendor_id` required if external
  - `is_active` required boolean
  - `address` required if external
  - `phone_number` required if external

### `PUT /api/service-requests/{serviceRequestId}/locations/{locationId}`

- Auth: yes
- Body similar to store; `is_active` required in request validation.

### `DELETE /api/service-requests/{serviceRequestId}/locations/{locationId}`

- Auth: yes

## 8.9 Service Request Approvals and Approval Actions

### `GET /api/service-requests/{serviceRequestId}/approvers`

- Auth: yes
- Returns:
  - `approvers`: users filtered by approval policy roles + IT department
  - `approvalPolicy`: selected policy object

### `GET /api/service-requests/{serviceRequestId}/approvals`

- Auth: yes
- Returns all `vendor_approvals` for request.
- Each approval includes `notes` (text), `status`, `approver`, `assigned_by`, `assigned_at`, `approved_at`, and `read_at` when present.

### `POST /api/service-requests/{serviceRequestId}/approvals`

- Auth: yes
- Body:
  - `approvers` required array of user IDs

### `PUT /api/service-requests/{serviceRequestId}/approvals`

- Auth: yes
- Body same as POST.
- Behavior: delete existing approvals and recreate.

### `DELETE /api/service-requests/{serviceRequestId}/approvals/{approvalId}`

- Auth: yes
- Intended to delete one approval.

### `POST /api/service-requests/approved/{approvalId}`

- Auth: yes
- Optional body: `notes`
- Marks vendor approval approved and may advance service request status.

### `POST /api/service-requests/rejected/{approvalId}`

- Auth: yes
- Optional body: `notes`
- Marks vendor approval rejected and may move request to `REPAIR_IN_WORKSHOP`.

### `POST /api/service-requests/need-repair/{serviceRequestId}`

- Auth: yes
- Optional body: `notes`
- Sets service request status to `REPAIR_IN_WORKSHOP`.

### `POST /api/service-requests/no-need-repair/{serviceRequestId}`

- Auth: yes
- Optional body: `notes`
- Sets service request status to `COMPLETED`.

## 8.10 Service Request Cancellation

### `GET /api/service-requests/{serviceRequestId}/cancellation`

- Auth: yes
- Intended: list/get cancellation records for request.

### `POST /api/service-requests/{serviceRequestId}/cancellation`

- Auth: yes
- Body:
  - `reason` required string
- Creates cancellation and updates service request status to `CANCELLED`.

### `PUT /api/service-requests/{serviceRequestId}/cancellation/{cancellationId}`

- Auth: yes
- Intended body:
  - `reason` required string

### `DELETE /api/service-requests/{serviceRequestId}/cancellation/{cancellationId}`

- Auth: yes
- Intended: delete cancellation and set request back to pending.

## 8.11 Reference Data

### `GET /api/references/service-types`

- Auth: yes
- Returns `id,name` for service types.

### `POST /api/references/service-types`

- Auth: yes
- Body: `name` required unique.

### `GET /api/references/statuses`

- Auth: yes
- Query optional: `entity_type_id`

### `GET /api/references/vendors`

- Auth: yes
- Returns `id,name,description`.

### `GET /api/references/roles`

- Auth: yes

### `GET /api/references/departments`

- Auth: yes

### `GET /api/references/cost-types`

- Auth: yes

## 8.12 Departments

### `GET /api/departments`

- Auth: yes
- Query: `search`, `sort_by`, `sort_order`, `per_page`

### `POST /api/departments`

- Auth: yes
- Body:
  - `name` required unique
  - `code` required unique

### `GET /api/departments/{id}` / `PUT /api/departments/{id}` / `DELETE /api/departments/{id}`

- Auth: yes
- Update body optional `name`, `code`.

## 8.13 Users

### `GET /api/users`

- Auth: yes
- Query:
  - `search`
  - `role_id`
  - `department_id`
  - `is_active` or `status` (boolean parse)
  - `sort_by`, `sort_order`, `per_page`

### `POST /api/users`

- Auth: yes
- Body:
  - `name`, `email`, `password`
  - `role_id` exists
  - `department_id` exists
  - `is_active` optional boolean

### `GET /api/users/{id}`

- Auth: yes

### `PUT /api/users/{id}`

- Auth: yes
- Body optional:
  - `name`
  - `email` (unique ignore current id)
  - `password` min 8
  - `role_id`
  - `department_id`
  - `is_active`

### `DELETE /api/users/{id}`

- Auth: yes

### `GET /api/user`

- Auth: yes
- Returns default Laravel user object (closure), not APIResponse wrapper.

## 8.14 Vendors

### `GET /api/vendors`

- Auth: yes
- Query:
  - `search`
  - `per_page`
- Response is raw Laravel paginator JSON (not APIResponse wrapper).

### `POST /api/vendors`

- Auth: yes
- Body required:
  - `name`
  - `maps_url` (valid URL)
  - `description`
- Returns raw vendor JSON with `201`.

### `GET /api/vendors/{id}` / `PUT /api/vendors/{id}` / `DELETE /api/vendors/{id}`

- Auth: yes
- Update body optional: `name`, `maps_url`, `description`.
- Delete returns `204` empty body.

## 8.15 Cost Types

### `GET /api/cost-types`

- Auth: yes
- Query optional: `code`, `name`
- Returns collection, not paginated.

### `POST /api/cost-types`

- Auth: yes
- Body required: `code`, `name`

### `GET /api/cost-types/{id}` / `PUT /api/cost-types/{id}` / `DELETE /api/cost-types/{id}`

- Auth: yes
- Update body required in validation (`code`, `name`).
- Delete returns APIResponse with HTTP `204` and message.

## 8.16 Invoices

### `GET /api/invoices`

- Auth: yes
- Query optional:
  - `service_request_id`
  - `status`
  - `vendor_id` (present in code filter but table has no `vendor_id`)
  - `start_date` + `end_date`
  - `search` (invoice_number)
  - `per_page` default 10

### `GET /api/invoices/{id}`

- Auth: yes

### `GET /api/invoices/{id}/print`

- Auth: yes
- Returns transformed print data object (not binary PDF).

### `GET /api/invoices/{id}/download`

- Auth: yes
- Route exists but no method implementation in `InvoiceController`.

## 8.17 Notifications

### `GET /api/notifications`

- Auth: yes
- Returns notifications for current user, ordered latest first.

### `PUT /api/notifications/{id}/read`

- Auth: yes
- Marks notification as read if current user owns it.

## 8.18 Inbox Approvals

### `GET /api/inbox-approvals/{statusId}`

- Auth: yes
- Returns `vendor_approvals` for current approver filtered by `status_id`.

### `PUT /api/inbox-approvals/{id}/read`

- Auth: yes
- Sets `read_at = now()`.

---

## 9. Known Gaps and Integration Caveats

These are code-level issues currently present and important for clients:

1. Auth controller error branch condition is incorrect (`if(!$result['code'] >= 200...)`), so auth failures are often returned through success envelope with HTTP 4xx.
2. `GET /api/invoices/{id}/download` route points to `InvoiceController@download`, but method does not exist.
3. Service cancellation controller/service mismatch:
   - `index()` calls non-existing service method `getCancellationByServiceRequest`.
   - `update()` and `destroy()` signatures do not match route params.
4. Service request approval delete endpoint calls service `destroy()` method that does not exist.
5. `PATCH /api/device-model/{id}` does not persist changes (`save()` missing).
6. `ServiceRequestStatusHandler` uses `$serviceRequest->code` instead of status code, and references undefined `$data` in one branch.
7. `ServiceRequestDetail` does not define `service_type` relation, but invoice preview loads `service_request_details.service_type`.
8. `service_requests` schema currently does not include `request_date` and `estimated_date`, while some logic/templates still reference those fields.
9. Service cost attachment storage check uses `Storage::disk('public')` while files are moved to `public/images`; attachment retrieval may fail depending on stored path.
10. Vendor endpoints return raw JSON while most endpoints use APIResponse wrapper (response shape inconsistency).

Recommendation for integrators:

- Treat this section as mandatory before implementing production client logic.
- Build defensive client parsing for inconsistent envelopes.

---

## 10. Test and Verification Commands

Run tests:

```bash
php artisan test
```

List routes:

```bash
php artisan route:list
php artisan route:list --json
```

Reset DB and reseed:

```bash
php artisan migrate:fresh --seed
```

---

## 11. Implementation Checklist (Frontend/Consumer)

Before go-live, make sure your client handles:

1. Bearer token management and retry/login on 401.
2. Mixed response envelopes (`APIResponse`, raw JSON, binary file).
3. Pagination meta parsing when present.
4. Multipart form upload for complaint images and service cost attachment.
5. Service request duplicate-device validation errors from `details` key.
6. Known gaps listed in section 9.

---

If you need this document converted into OpenAPI/Swagger format, it can be generated from this same source-of-truth structure.
