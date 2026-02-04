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
