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
