# Backend Reports

## Session: 2026-01-31 - Atasan Approval Flow Fix

### Issue
When all atasan approved a service request, the status was changing directly to `IN_PROGRESS` (7), skipping `APPROVED_BY_ABOVE` (5). Also, no audit log was being created for this transition.

### Fix Applied
Modified `checkAndUpdateServiceRequestStatus()` in `app/Services/ServiceRequest/ServiceRequestApprovalService.php`:

**Before:**
```php
} elseif ($pendingApprovals === 0) {
    $serviceRequest->update(['status_id' => 7]); // In Progress - no audit log!
}
```

**After:**
```php
} elseif ($pendingApprovals === 0) {
    // All approved -> APPROVED_BY_ABOVE (5) -> IN_PROGRESS (7)
    $oldStatusId = $serviceRequest->status_id;
    
    // First transition to APPROVED_BY_ABOVE
    $serviceRequest->update(['status_id' => 5]);
    $this->auditLogService->createAuditLog([...]);
    
    // Auto-transition to IN_PROGRESS
    $serviceRequest->update(['status_id' => 7]);
    $this->auditLogService->createAuditLog([...]);
}
```

### Changes
1. Status now properly transitions: `IN_REVIEW_ABOVE` (4) → `APPROVED_BY_ABOVE` (5) → `IN_PROGRESS` (7)
2. Audit logs are created for both transitions
3. Fixed audit log for rejection case to use `createAuditLog` instead of `createStatusAuditLog`

### Files Modified
- `app/Services/ServiceRequest/ServiceRequestApprovalService.php`
  - `checkAndUpdateServiceRequestStatus()` method (lines 207-256)

### Notes
- The `update()` method in the same service already handles resetting approvals when admin changes approvers (deletes all existing approvals and recreates with status PENDING)
- Status flow is now:
  - Any rejection → `REJECTED_BY_ABOVE` (6)
  - All approve → `APPROVED_BY_ABOVE` (5) → auto → `IN_PROGRESS` (7)
  - Admin marks → `COMPLETED` (8)

## Session: 2026-02-04 - Replace Hardcoded Status IDs With Status Codes

### Goal
Ensure all service store/update flows resolve `status_id` by status `code` from `StatusSeeder` (entity-aware), instead of hardcoded numeric IDs.

### Changes
1. Added helpers in `Status` model to fetch a status ID by entity type code + status code.
2. Updated service request create/update/delete flows to use status codes (`PENDING`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`) and validate entity type by code.
3. Updated vendor approval flows to use status codes (`PENDING`, `APPROVED`, `REJECTED`) and service request transitions by code.
4. Updated invoice creation flow to use invoice status code (`SENT`).
5. Added enums for service request, vendor approval, and invoice status codes to remove magic strings.
6. Added `status_code` request support (validated per entity type and mapped to `status_id`) for service request store/update.
7. Added unique constraint on `statuses` (`entity_type_id`, `code`) to enforce stable business keys.
8. Updated seeders and invoice export checks to use enums and expose status `code` in service request show payload.

### Files Modified
- `app/Enums/ServiceRequestStatusCode.php`
- `app/Enums/VendorApprovalStatusCode.php`
- `app/Enums/InvoiceStatusCode.php`
- `app/Models/Status.php`
- `app/Services/ExportInvoiceService.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `app/Services/ServiceRequest/ServiceRequestCancellationService.php`
- `app/Services/ServiceRequest/ServiceRequestApprovalService.php`
- `app/Services/InvoiceService.php`
- `app/Http/Requests/ServiceRequest/StoreServiceRequest.php`
- `app/Http/Requests/ServiceRequest/UpdateServiceRequest.php`
- `database/seeders/StatusSeeder.php`
- `database/seeders/StatusTransitionSeeder.php`
- `database/migrations/2026_02_04_000001_add_unique_status_code_per_entity.php`

## Session: 2026-02-05 - Service Request Filters + Timeline Refactor

### Goal
Improve service request filtering (including a keyword search across related data) and centralize timeline formatting logic.

### Changes
1. Qualified `service_requests` columns in filters to avoid ambiguity when joins are applied.
2. Added a keyword search that matches user name, device model, serial number, vendor name, and service location phone number.
3. Ensured the base query selects only `service_requests.*` and uses `distinct()` when keyword joins are applied.
4. Moved timeline mapping logic into `AuditLogService::getTimeLineForServiceRequest()` and reused it in service request detail response.

### Files Modified
- `app/Models/ServiceRequest.php`
- `app/Services/AuditLogService.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`

## Session: 2026-02-07 - Bad Asset Flag + Auto Marking + Docs/Tests

### Goal
Add a `bad_asset` boolean flag to devices, allow it in device APIs, and automatically mark devices as bad assets when a service request transitions to `BAD_ASSET`.

### Changes
1. Added `bad_asset` boolean column (default `false`) to `devices`.
2. Updated `Device` model casts/fillable, and device create/update/patch flows to accept `bad_asset`.
3. Included `bad_asset` in device list and service request detail responses.
4. Added auto-marking: when a service request status becomes `BAD_ASSET`, all related devices are updated to `bad_asset = true` (including vendor rejection path).
5. Added `bad_asset` filter to `GET /api/devices` (`true/false` or `1/0`).
6. Updated API docs for device endpoints to include `bad_asset`.
7. Added a feature test to verify the `BAD_ASSET` status auto-flags devices, and updated device tests to include `bad_asset`.
8. Updated PHPUnit config to use MySQL test DB `eci-service-it_test`.

### Files Modified
- `database/migrations/2026_02_07_000001_add_bad_asset_to_devices_table.php`
- `app/Models/Device.php`
- `app/Services/DeviceService.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `app/Services/ApprovalService.php`
- `app/Http/Requests/Device/StoreDeviceRequest.php`
- `app/Http/Requests/Device/UpdateDeviceRequest.php`
- `documentation.md`
- `tests/Feature/ApiEndpointTest.php`
- `phpunit.xml`

### Notes
- Automated tests could not run here because the MySQL service was not reachable from the environment.

## Session: 2026-02-07 - Service Cost Attachments (Images + PDF)

### Goal
Allow teams to upload vendor receipts as attachments when adding/updating service costs, supporting common image formats and PDF.

### Changes
1. Updated service cost validation to accept `pdf` in addition to image formats and raised the size limit to 10MB.
2. Added custom attachment filenames for service cost uploads (includes service request ID and timestamp).
3. Added an endpoint to fetch cost attachments.
4. Updated documentation for service cost endpoints to include attachment field, size limits, and the new attachment endpoint.

### Files Modified
- `app/Http/Requests/ServiceCost/StoreServiceCostRequest.php`
- `app/Http/Requests/ServiceCost/UpdateServiceCostRequest.php`
- `app/Services/ServiceRequest/ServiceRequestCostService.php`
- `app/Http/Controllers/ServiceRequestCostController.php`
- `routes/api.php`
- `documentation.md`

## Session: 2026-02-07 - User Active Flag

### Goal
Add an `is_active` flag to users to support active/inactive status.

### Changes
1. Added `is_active` column to `users` (default `true`).
2. Updated `User` model casts/fillable.
3. Allowed `is_active` in user create/update requests.
4. Added filtering by `is_active` (with `status` query param fallback).
5. Updated user API documentation.

### Files Modified
- `database/migrations/2026_02_07_000002_add_is_active_to_users_table.php`
- `app/Models/User.php`
- `app/Http/Requests/User/StoreUserRequest.php`
- `app/Http/Requests/User/UpdateUserRequest.php`
- `app/Services/UserService.php`
- `documentation.md`

## Session: 2026-02-07 - Approval Services Cleanup

### Goal
Refactor approval-related services for clarity, consistent status codes, and safer flows.

### Changes
1. Added proper namespace/DI in `ApprovalService` and removed unused variables.
2. Normalized status transitions to existing codes (`WAITING_APPROVAL_ABOVE`, `REPAIR_IN_VENDOR`, `BAD_ASSET`).
3. Refactored `ServiceRequestApprovalService` with shared helpers, consistent transactions, and a `destroy()` method to match controller routes.
4. Renamed workshop endpoints to `need-repair` / `no-need-repair` and removed the unused approvals POST route.
5. Standardized approver lookup to filter by IT department when available.

### Files Modified
- `app/Services/ApprovalService.php`
- `app/Services/ServiceRequest/ServiceRequestApprovalService.php`

## Session: 2026-02-09 - Service Request Admin Email + Creator Validation

### Goal
When a user creates a new service request, automatically notify the admin by email with device/model + complaint details and direct links. Prevent users from spoofing `user_id` / creating requests on behalf of others.

### Changes
1. Added role-aware validation for `POST /api/service-requests`:
   - Non-admin users cannot submit `user_id` or `admin_id` (both are prohibited).
   - `request_date` is now optional (backend defaults to `now()` if omitted).
2. Enforced creator identity in the service layer:
   - Users can only create requests for themselves.
   - Admins can create on behalf of another user by providing `user_id`.
3. Queued an admin notification email after the transaction commits (so emails are not queued for rolled-back requests).
4. Enriched the admin email content:
   - Includes service request ID / service number (when available), device/device model, complaints, and links (`FRONTEND_URL` login + service request detail).

### Configuration
- `ADMIN_MAIL`: recipient admin email (required to actually send).
- `FRONTEND_URL`: frontend base URL used for links in the email (`/login` and `/service-requests/{id}`).

### Files Modified
- `app/Http/Requests/ServiceRequest/StoreServiceRequest.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `app/Services/ContactAdmin/ContactAdminMailservice.php`
- `app/Mail/UserContactAdmin.php`
- `resources/views/mail/user-contact-admin.blade.php`
- `.env.example`

## Session: 2026-02-11 - Approval Flow Fatal Fix + Need/No-Need Repair Endpoints

### Goal
Fix fatal errors in approval flow and restore workshop action endpoints with proper audit logging.

### Changes
1. Corrected `ApprovalService` namespace and dependencies to prevent class redeclare/fatal.
2. Updated vendor approval approve/reject flows to persist notes and write audit logs with status IDs.
3. Restored `deviceNeedRepair()` and `deviceNoNeedRepair()` to update service request status and write audit logs.
4. Standardized status ID resolution helpers and approver retrieval response shape.

### Files Modified
- `app/Services/Approval/ApprovalService.php`
- `changes.md`

## Session: 2026-02-12 - Service Location Request Validation Simplify

### Goal
Simplify required fields for external service locations.

### Changes
1. Store request now requires `phone_number` for external locations and removes `city`, `province`, `postal_code`, and `maps_url` as required fields.
2. Update request mirrors the same requirement while keeping `address` required for external updates.

### Files Modified
- `app/Http/Requests/ServiceLocation/StoreServiceLocationRequest.php`
- `app/Http/Requests/ServiceLocation/UpdateServiceLocationRequest.php`

## Session: 2026-02-12 - Approver Endpoint Refactor

### Goal
Rename approver endpoint and return a cleaner approver list filtered to IT department.

### Changes
1. Renamed route to `GET /api/service-requests/{serviceRequestId}/approvers` and controller method to `getApproversByServiceRequestId`.
2. Updated `ApprovalService::getApproverByServiceRequestId()` to fetch approvers by role IDs, filter to IT department, and return name/email only.
3. Simplified response payload to `approvers` and `approvalPolicy`.

### Files Modified
- `app/Http/Controllers/ApprovalController.php`
- `app/Services/Approval/ApprovalService.php`
- `routes/api.php`

## Session: 2026-02-12 - Invoice Export Status Rules

### Goal
Allow invoice export for most statuses and only block cancelled requests.

### Changes
1. Reduced `ExportInvoiceService::BLOCKED_STATUS_CODES` to only `CANCELLED`.

### Files Modified
- `app/Services/Invoice/ExportInvoiceService.php`

## Session: 2026-02-12 - Remove Auto Vendor Approvals on WAITING_APPROVAL_ABOVE

### Goal
Stop creating vendor approvals when status becomes `WAITING_APPROVAL_ABOVE`.

### Changes
1. Removed `createVendorApprovals()` call from service request update when status transitions to `WAITING_APPROVAL_ABOVE`.

### Files Modified
- `app/Services/ServiceRequest/ServiceRequestService.php`

## Session: 2026-02-14 - Shared Hosting Upload Storage + Filename Fix

### Goal
Store complaint images and service cost attachments in `public/images` for shared hosting and fix filename generation.

### Changes
1. Complaint images now saved via `move()` to `public/images` with `image_path` as `images/{filename}`, and deletions use `unlink()` on the public path.
2. Service cost attachments now saved to `public/images`, served via `response()->file(...)`, and return only the filename.
3. Replaced `random()` filename generation with `time() . '_' . Str::random(10)`.

### Files Modified
- `app/Services/ServiceRequest/DetailServiceRequestService.php`
- `app/Services/ServiceRequest/ServiceRequestCostService.php`
- `public/images/sr5_receipt_20260214_153358_xxk3pj.svg`
- `public/images/vite.svg`

## Session: 2026-02-17 - Service Location Update 500 Fix

### Goal
Prevent 500 errors when updating service locations without `phone_number`.

### Changes
1. `createServiceLocation()` now defaults `phone_number` to null when absent.
2. Fixed `array_key_exists` usage to check `phone_number` in `$data` before reading.

### Files Modified
- `app/Services/ServiceRequest/ServiceLocationService.php`

## Session: 2026-02-18 - User Notifications for Service Requests

### Goal
Add notification feature for frontend and notify users on key status changes.

### Changes
1. Added `Notification` model, service, and controller with endpoints to list notifications and mark as read.
2. Added `service_request_id` to notifications and made `read_at` nullable.
3. Created notifications when service request status becomes `COMPLETED`, `BAD_ASSET`, or `CANCELLED`.
4. Updated service request search param to `search` and included `service_number` in search criteria.
5. Added `service_request` relation on notifications.

### Files Modified
- `app/Http/Controllers/NotificationController.php`
- `app/Services/Notification/NotificationService.php`
- `app/Models/Notification.php`
- `app/Models/ServiceRequest.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `database/migrations/2026_01_19_074729_create_notifications_table.php`
- `database/migrations/2026_02_17_205219_add_service_request_id_on_notifications_table.php`
- `routes/api.php`
- `public/images/sr20_receipt_20260217_202446_onjgmx.png`

## Session: 2026-02-19 - Rejection by Above -> Repair In Workshop

### Goal
When vendor approval is rejected, move the service request to `REPAIR_IN_WORKSHOP` instead of `BAD_ASSET`.

### Changes
1. Rejection now updates status to `REPAIR_IN_WORKSHOP` and stops auto bad-asset marking.
2. Audit log now records the correct new status.
3. Fixed enum typo in status lookup.

### Files Modified
- `app/Services/Approval/ApprovalService.php`

## Session: 2026-02-20 - Service Request Query Cleanup + Field Removal

### Goal
Reduce heavy relation loads and remove unused request and estimated date fields.

### Changes
1. `ShowRelationsHandler` now selects minimal columns for relations and limits complaint image fields to `image_path`.
2. `ServiceRequest` filter uses `created_at` for `request_date`, removes `estimated_date` filtering, and selects minimal columns.
3. Removed `request_date` and `estimated_date` from model casts/fillable and from create/update flows.
4. Updated the base migration for `service_requests` to remove `request_date` and `estimated_date`.
5. `sendAdminNotification()` is called directly on create (no `DB::afterCommit`), and `getServiceRequestById` no longer hides timestamps.

### Files Modified
- `app/Helpers/ServiceRequest/ShowRelationsHandler.php`
- `app/Models/ServiceRequest.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `database/migrations/2026_01_19_081314_create_service_requests_table.php`

## Session: 2026-02-20 - User Seeder Gmail Emails

### Goal
Switch seeded user emails to Gmail addresses.

### Changes
1. Updated `UserSeeder` default emails to use `@gmail.com`.

### Files Modified
- `database/seeders/UserSeeder.php`

## Session: 2026-02-20 - Service Request Detail Solution + Status Handler Refactor

### Goal
Melengkapi alur update service request detail dengan field `solution`, lalu merapikan alur side-effect status (`BAD_ASSET`, invoice, notification) agar dipusatkan ke handler.

### Changes
1. Menambahkan migration kolom `solution` (nullable, `string(8000)`) pada tabel `service_request_details`.
2. Menambahkan validasi `details.*.solution` pada request update service request.
3. Menambahkan cast dan `$fillable` untuk field `solution` pada model `ServiceRequestDetail`.
4. Memperbarui logic `updateDetailServiceRequest()` agar field `solution` ikut di-update.
5. Menambahkan `solution` ke relasi detail pada `ShowRelationsHandler` agar ikut tampil di response detail service request.
6. Menambahkan helper `createServiceRequestAuditLog()` di `AuditLogService` lalu dipakai saat pembuatan service request.
7. Menambahkan helper `sendAdminNotification()` di `ContactAdminMailservice` lalu dipanggil melalui `DB::afterCommit` saat create service request.
8. Menambahkan helper baru `StatusHandler` dan memindahkan proses side-effect status di `ServiceRequestService` ke handler tersebut.
9. Mengubah akses `markDevicesAsBadAsset()` dari `private` ke `public` agar bisa dipanggil dari status handler.
10. Menambahkan method `addSolution()` pada `ServiceRequestService` untuk update field `solution` pada detail service request.

### Files Modified
- `database/migrations/2026_02_20_025431_add_solution_to_service_request_table.php`
- `app/Http/Requests/ServiceRequest/UpdateServiceRequest.php`
- `app/Models/ServiceRequestDetail.php`
- `app/Services/ServiceRequest/DetailServiceRequestService.php`
- `app/Helpers/ServiceRequest/ShowRelationsHandler.php`
- `app/Helpers/ServiceRequest/StatusHandler.php`
- `app/Services/AuditLog/AuditLogService.php`
- `app/Services/ContactAdmin/ContactAdminMailservice.php`
- `app/Services/ServiceRequest/ServiceRequestService.php`
- `reports.md`

## Session: 2026-02-20 - Admin Mail Service Fix

### Goal
Memperbaiki pemanggilan internal mail queue pada helper notifikasi admin agar tidak error saat kirim email setelah pembuatan service request.

### Changes
1. Mengganti pemanggilan dari properti yang tidak ada (`$this->contactAdminMailService->queue`) menjadi pemanggilan method service itu sendiri (`$this->queue`) di `sendAdminNotification()`.

### Files Modified
- `app/Services/ContactAdmin/ContactAdminMailservice.php`

## Session: 2026-02-20 - Approver Update (Manager + Senior Manager)

### Goal
Menyesuaikan alur approver untuk level manajemen dengan mengganti role `director` menjadi `senior manager` dan menyederhanakan langkah approval policy.

### Changes
1. Mengubah seed role dari `director` menjadi `senior manager`.
2. Memperbarui `UserSeeder` untuk akun `Senior Manager` beserta email, role mapping, dan validasi role dependency.
3. Memperbarui `ApprovalPolicySeeder`:
   - Mengganti referensi role `director` menjadi `senior manager`.
   - Menyesuaikan validasi role wajib.
   - Menyederhanakan step policy 3 (menggunakan `senior manager` di step akhir tanpa step tambahan CEO).
   - Menyesuaikan superior roles pada policy 4 menjadi `manager` dan `senior manager`.
4. Menyesuaikan relasi detail pada `ShowRelationsHandler` dengan menghapus pemilihan kolom `service_type_id` di response detail service request.
5. Menambahkan aset gambar hasil upload pada folder public images.

### Files Modified
- `app/Helpers/ServiceRequest/ShowRelationsHandler.php`
- `database/seeders/ApprovalPolicySeeder.php`
- `database/seeders/RoleSeeder.php`
- `database/seeders/UserSeeder.php`
- `public/images/1771560266_0KQRCaOfpU.svg`

## Session: 2026-02-22 - Vendor Approval Notes + Audit Logs Include Approvals

### Goal
Expose vendor approval notes consistently and ensure audit logs/timeline include approval actions (not only service request status changes).

### Changes
1. Added `notes` and `read_at` handling on `VendorApproval` model (casts/fillable) so notes are readable/writable via API responses.
2. Included `notes` in service request detail payload by expanding `ShowRelationsHandler` vendor approvals fields.
3. Updated `AuditLogService::getAuditLogsForServiceRequest()` to include audit logs for `vendor_approvals` (entity type `VENDOR_APPROVAL`) so timeline reflects approval actions.
4. Ensured vendor approval IDs can be resolved for audit log lookup.
5. Updated vendor approvals migration to reflect `notes` field in schema.

### Files Modified
- `app/Helpers/ServiceRequest/ShowRelationsHandler.php`
- `app/Models/VendorApproval.php`
- `app/Services/AuditLog/AuditLogService.php`
- `app/Services/ServiceRequest/ServiceRequestApprovalService.php`
- `database/migrations/2026_01_19_081515_create_vendor_approvals_table.php`

## Session: 2026-02-23 - Vendor Approval Notes Nullable (Migration)

### Goal
Make vendor approval notes optional at database level so approval records are not blocked when notes are omitted.

### Changes
1. Updated `vendor_approvals` base migration column `notes` from required text to nullable text.
2. Keeps compatibility with existing approval actions that still accept/store notes when provided.

### Files Modified
- `database/migrations/2026_01_19_081515_create_vendor_approvals_table.php`

## Session: 2026-03-02 - Service Request Domain Refactor Merge

### Goal
Finalize refactor branch for service request module so business logic is split into domain actions/workflows and remove dependence on legacy service structure.

### Changes
1. Merged `refactor/service-request` into `main` and moved service request logic into `app/Domains/*` modular structure.
2. Added/updated workflow orchestration for create/get/update/delete service requests after refactor merge.
3. Moved related modules into domain packages:
   - `ServiceRequestApproval`
   - `ServiceRequestCancellation`
   - `ServiceRequestCost`
   - `ServiceRequestDetail`
   - `ServiceRequestLocation`
4. Updated controller-service wiring so HTTP layer calls domain workflows/actions.
5. Applied post-merge bug fixes for service request retrieval, status resolution, and service number/status helper usage.

### Files Modified
- `app/Domains/ServiceRequest/*`
- `app/Domains/ServiceRequestApproval/*`
- `app/Domains/ServiceRequestCancellation/*`
- `app/Domains/ServiceRequestCost/*`
- `app/Domains/ServiceRequestDetail/*`
- `app/Domains/ServiceRequestLocation/*`
- `app/Http/Controllers/ServiceRequestController.php`
- `app/Http/Controllers/ServiceRequestApprovalController.php`
- `app/Http/Controllers/ServiceRequestCancellationController.php`
- `app/Http/Controllers/ServiceRequestCostController.php`

## Session: 2026-03-02 - Audit Log Ordering Fix (Service Request Status Update)

### Goal
Fix incorrect audit-log capture during service request status update flow after domain refactor.

### Changes
1. Changed status update action to write audit log before persisting `status_id` so transition logs keep the expected old/new status context.
2. Injected `WriteAuditLogs` into update workflow and removed unused status lookup in workflow layer.

### Files Modified
- `app/Domains/ServiceRequest/Actions/UpdateServiceRequestStatus.php`
- `app/Domains/ServiceRequest/Services/UpdateServiceRequestWorkflow.php`

## Session: 2026-03-03 - Operator Flow: Create Device After Service Request Creation

### Goal
Allow operator flow to create/link device after service request is created (during detail update), instead of forcing device creation at initial detail creation.

### Changes
1. Added `UpdateServiceRequestDetails` action and integrated it into `UpdateServiceRequestWorkflow` when `details` payload is provided.
2. Updated detail update workflow:
   - Finds current detail first.
   - Creates/fetches device when `brand`, `model`, and `serial_number` are provided.
   - Uses existing `device_type_id` from current detail for device resolver.
3. Removed auto device creation from detail create workflow so initial detail creation no longer forces full device identity payload.
4. Updated store validation to make `details.*.brand`, `details.*.model`, and `details.*.serial_number` optional.
5. Updated `service_request_details` schema/model contract:
   - `device_id` nullable.
   - Added `device_type_id` column and relation/fillable/cast support.

### Files Modified
- `app/Domains/ServiceRequest/Actions/UpdateServiceRequestDetails.php`
- `app/Domains/ServiceRequest/Services/UpdateServiceRequestWorkflow.php`
- `app/Domains/ServiceRequestDetail/Actions/CheckOrCreateDevice.php`
- `app/Domains/ServiceRequestDetail/Actions/CreateServiceRequestDetail.php`
- `app/Domains/ServiceRequestDetail/Actions/UpdateServiceRequestDetail.php`
- `app/Domains/ServiceRequestDetail/Services/CreateServiceRequestDetailWorkflow.php`
- `app/Domains/ServiceRequestDetail/Services/UpdateServiceRequestDetailWorkflow.php`
- `app/Http/Requests/ServiceRequest/StoreServiceRequest.php`
- `app/Models/ServiceRequestDetail.php`
- `database/migrations/2026_01_19_081325_create_service_request_details_table.php`

## Session: 2026-03-04 - Include Parsing + Summary Endpoints + Throttle

### Goal
Menambahkan dukungan query `include` yang bisa memakai argumen (contoh `locations(active)` dan `approvals(status,approver)`), memastikan eager loading aman untuk list utama, serta menyediakan endpoint list ringan untuk UI dengan batas pagination dan throttle.

### Changes
1. Menambahkan parser `include` yang memahami tanda kurung dan argumen nested.
2. Menambahkan include `details.device.deviceModel.deviceType` serta `locations(active)` untuk filter lokasi aktif.
3. Menambahkan endpoint ringan:
   - `GET /api/service-requests/summary`
   - `GET /api/inbox-approvals/{statusId}/summary`
4. Membatasi `per_page` maksimal 100 untuk list utama dan summary.
5. Menambahkan throttle `60/min` pada endpoint summary.
6. Menambahkan eager loading untuk inbox approvals berdasarkan `include=serviceRequest(...)`.
7. Menambahkan relasi summary default untuk service request list ringan.

### Files Modified
- `app/Domains/ServiceRequest/Support/ShowRelationsHandler.php`
- `app/Models/ServiceRequest.php`
- `app/Domains/ServiceRequest/Actions/GetServiceRequest.php`
- `app/Domains/Inbox/Actions/ListInboxApprovals.php`
- `app/Domains/Inbox/Actions/ListInboxApprovalsSummary.php`
- `app/Http/Controllers/ServiceRequestController.php`
- `app/Http/Controllers/InboxApprovalController.php`
- `routes/api.php`
- `documentation.md`
