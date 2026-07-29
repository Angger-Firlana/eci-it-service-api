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
changes 4 = Tombol Cari Vendor (IT)
- WorkshopActionCard.tsx: prop `onMoveToVendor` → `onSearchVendor`, teks "Pindah ke Vendor" → "Cari Vendor", icon `bi-arrow-left-right` → `bi-search`
- ActionCardSwitch.tsx: prop rename
- InboxDetail.tsx: binding rename
- Menghilangkan ambigu antara "pindah" dan "cari" vendor
-----------
changes 5 = Rollback 1 Langkah (IT & Admin)
- Backend StatusTransitionSeeder.php: +7 transisi `ROLLBACK_FROM_*` (admin, operator)
- Backend routes/api.php: +`technician` ke allowed-transitions route, +`admin` ke PUT route
- Frontend IT: 6 card components + ActionCardSwitch + InboxDetail — tombol "Kembali"
- Frontend Admin: ActionSidebar + InboxDetail + CSS — tombol "Kembali"
- Efek: setiap stage non-root bisa mundur 1 langkah via confirm dialog
-----------
changes 6 = Edit Keterangan & Solusi (IT & Admin)
- Backend: +2 route (`PUT /{id}/update-note`, `PUT /{id}/update-solution`)
- Backend: +2 FormRequest (UpdateServiceRequestNote, UpdateServiceRequestSolution)
- Backend Controller: +2 method (updateNote, updateSolution)
- Frontend shared: DetailNotes.tsx inline-edit mode (pencil icon → textarea → simpan/batal)
- Frontend IT: RequestDetailColumn + InboxDetail — wiring edit note/solution
- Frontend Admin: RequestDetailColumn + InboxDetail — wiring edit note/solution
- Efek: IT dan Admin bisa edit keterangan & solusi langsung dari halaman detail
-----------
changes 7 = Service List & Detail Admin disamakan dengan IT
- admin/ServiceDetail.tsx: ganti `AdminInboxDetail` → `ItInboxDetail`
- admin/ServiceList.tsx: enrichment lokasi + stage derivation + column `getItServiceListColumns`
- admin/ServiceList.css: selector untuk class `it-*`
- Efek: admin lihat service list dengan lokasi, phone, stage badge; detail pake layout IT
-----------
changes 8 = Setting Penerima Email
- EmailSettings.tsx: +section "Penerima Email" — daftar existing, toggle aktif, hapus, tambah user
- EmailSettings.css: style email-recipient-*
- Backend endpoint sudah ada (GET/POST/PUT/DELETE /mail-settings/it-emails)
-----------
changes 9 = Bugfix Atasan Navigasi
- inbox/atasan/Inbox.tsx: `navigate('/inbox/${row.id}')` → `navigate('/services/${row.id}')`
- dashboard/atasan/Dashboard.tsx: handleViewAllInbox & handleViewDetails pake `/services` saja
- dashboard/atasan/Dashboard.tsx: include parameter dari `service_request.status,...` → `serviceRequest(user,status,details.device.deviceModel.deviceType)`
- Efek: atasan bisa klik detail pending approval dan lihat device/description dengan benar
-----------
