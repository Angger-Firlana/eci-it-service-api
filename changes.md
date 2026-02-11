changes 1 =
- Fixed `GET /api/service-requests/{id}` internal server error caused by timeline return type mismatch in `AuditLogService`.
- Updated `getTimeLineForServiceRequest` to return a plain PHP array (`->values()->all()`) to match its `: array` signature.
- Effect: user service detail page can receive timeline/detail payload correctly (no fallback-only `-` fields from failed detail fetch).
