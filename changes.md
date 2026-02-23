changes 1 =
- Fixed `GET /api/service-requests/{id}` internal server error caused by timeline return type mismatch in `AuditLogService`.
- Updated `getTimeLineForServiceRequest` to return a plain PHP array (`->values()->all()`) to match its `: array` signature.
- Effect: user service detail page can receive timeline/detail payload correctly (no fallback-only `-` fields from failed detail fetch).
-----------
changes 2 =
- Fixed fatal class redeclaration on approval flow by correcting `ApprovalService` namespace to `App\Services\Approval`.
- Rebuilt `ApprovalService` dependencies and methods used by:
  - `POST /api/service-requests/approved/{approvalId}`
  - `POST /api/service-requests/rejected/{approvalId}`
  - `POST /api/service-requests/need-repair/{serviceRequestId}`
  - `POST /api/service-requests/no-need-repair/{serviceRequestId}`
  - `GET /api/service-requests/{serviceRequestId}/approver`
- Added robust status resolution via `Status::idForEntityCode(...)` and audit log writes for status/approval transitions.
- Effect: IT action buttons (service / tidak service) no longer crash due backend fatal error and can proceed to normal API response handling.
-----------
changes 3 =
- Updated `vendor_approvals.notes` column in base migration to nullable (`$table->text('notes')->nullable();`).
- Effect: vendor approval records can be created/updated without mandatory notes while still supporting optional notes from approval actions.
-----------
