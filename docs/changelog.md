# Changelog & Dokumentasi Perubahan

Dokumen ini mencatat semua perubahan yang telah dilakukan pada project **eci-it-service** (backend Laravel) dan **eci-it-service-frontend** (React).

---

## Daftar Isi

1. [Rollback 1 Langkah (IT & Admin)](#1-rollback-1-langkah-it--admin)
2. [Edit Keterangan & Solusi (IT & Admin)](#2-edit-keterangan--solusi-it--admin)
3. [Perubahan Tombol Cari Vendor (IT)](#3-perubahan-tombol-cari-vendor-it)

---

## 1. Rollback 1 Langkah (IT & Admin)

### Deskripsi
Menambahkan tombol **"Kembali"** di setiap stage non-root untuk mundur 1 langkah ke status sebelumnya. Tersedia untuk role **IT (operator)** dan **Admin**.

### Backend

#### File: `database/seeders/StatusTransitionSeeder.php`
Menambahkan 7 transisi reverse setelah transisi `MARK_BAD_ASSET`:

| Kode Transisi | Dari | Ke | Roles |
|---|---|---|---|
| `ROLLBACK_FROM_WORKSHOP` | REPAIR_IN_WORKSHOP | REVIEW_IN_WORKSHOP | admin, operator |
| `ROLLBACK_FROM_VENDOR` | REPAIR_IN_VENDOR | REPAIR_IN_WORKSHOP | admin, operator |
| `ROLLBACK_FROM_VENDOR_QUOTE` | WAITING_VENDOR_QUOTE | REPAIR_IN_VENDOR | admin, operator |
| `ROLLBACK_FROM_APPROVAL_ABOVE` | WAITING_APPROVAL_ABOVE | WAITING_VENDOR_QUOTE | admin, operator |
| `ROLLBACK_FROM_COMPLETED` | COMPLETED | REPAIR_IN_WORKSHOP | admin, operator |
| `ROLLBACK_FROM_BAD_ASSET` | BAD_ASSET | REVIEW_IN_WORKSHOP | admin, operator |
| `ROLLBACK_FROM_CANCELLED` | CANCELLED | REVIEW_IN_WORKSHOP | admin, operator |

#### File: `routes/api.php`
- **Line 74**: Menambahkan `technician` ke middleware role route `GET /{id}/allowed-transitions` (sebelumnya: `user,admin,operator,manager`, sekarang: `user,admin,operator,manager,technician`)
- **Line 72**: Menambahkan `admin` ke middleware role route `PUT /{id}` (sebelumnya: `operator`, sekarang: `operator,admin`)

**Tidak ada perubahan backend API lain** — endpoint `PUT /service-requests/{id}` dan `GET /.../allowed-transitions` sudah mendukung flow ini.

### Frontend IT

#### File: `src/modules/inbox/it/components/StatusOnlyCard.tsx`
- Menambahkan props: `showRollback?: boolean`, `onRollback?: () => void`, `isBusy?: boolean`
- Merender tombol "Kembali" jika `showRollback && onRollback`

#### File: `src/modules/inbox/it/components/WorkshopActionCard.tsx`
- Menambahkan prop: `onRollback?: () => void`
- Merender tombol "Kembali" dengan icon `bi-arrow-return-left`

#### File: `src/modules/inbox/it/components/VendorSetupActionCard.tsx`
- Menambahkan prop: `onRollback?: () => void`
- Merender tombol "Kembali"

#### File: `src/modules/inbox/it/components/VendorQuoteActionCard.tsx`
- Menambahkan prop: `onRollback?: () => void`
- Merender tombol "Kembali"

#### File: `src/modules/inbox/it/components/WaitingApprovalActionCard.tsx`
- Menambahkan props: `onRollback?: () => void`, `isBusy?: boolean`
- Merender tombol "Kembali"

#### File: `src/modules/inbox/it/components/CompleteAndBadAssetActionCard.tsx`
- Menambahkan prop: `onRollback?: () => void`
- Merender tombol "Kembali"

#### File: `src/modules/inbox/it/components/ActionCardSwitch.tsx`
- Menambahkan props: `onRollback: () => void`, `isRollbackAvailable: boolean`
- **Tidak** melewatkan `onRollback` ke `ReviewActionCard` (root stage)
- Melewatkan ke semua card lain, termasuk `StatusOnlyCard` (stage terminal)

#### File: `src/modules/inbox/it/InboxDetail.tsx`
- Menambahkan state: `rollbackStatusId`
- Menambahkan `useEffect` untuk fetch `GET /{id}/allowed-transitions` dan filter transisi dengan prefix `ROLLBACK_FROM`
- Menambahkan handler: `handleRollback` — confirm dialog → `runMutation` → PUT status
- Binding: `onRollback={handleRollback}`, `isRollbackAvailable={rollbackStatusId !== null}`

### Frontend Admin

#### File: `src/modules/inbox/admin/components/ActionSidebar.tsx`
- Menambahkan props: `canRollback?: boolean`, `onRollback?: () => void`
- Menambahkan section "Mundur 1 Langkah" sebelum `ServiceTimeline`

#### File: `src/modules/inbox/admin/InboxDetail.tsx`
- Menambahkan state: `rollbackStatusId`
- Menambahkan `useEffect` untuk fetch allowed-transitions (sama dengan IT)
- Menambahkan handler: `handleRollback` — `setIsSaving` → `authenticatedRequest` → PUT status → `refresh`

#### File: `src/modules/inbox/admin/InboxDetail.css`
- Menambahkan style `.admin-action-rollback` (background `#F59E0B`, hover `#D97706`)

### Cara Menjalankan Seeder
```bash
cd eci-it-service
php artisan db:seed --class=StatusTransitionSeeder
```

---

## 2. Edit Keterangan & Solusi (IT & Admin)

### Deskripsi
Menambahkan fitur inline-edit pada field **Keterangan** (complaint) dan **Solusi** (solution) di halaman detail request. Tersedia untuk role **IT (operator)** dan **Admin**.

### Backend

#### File: `routes/api.php`
Menambahkan 2 route baru di grup `service-requests`:
```php
Route::put('/{id}/update-note', [ServiceRequestController::class, 'updateNote'])->middleware('role:operator,admin');
Route::put('/{id}/update-solution', [ServiceRequestController::class, 'updateSolution'])->middleware('role:operator,admin');
```

#### File: `app/Http/Requests/ServiceRequest/UpdateServiceRequestNote.php`
Form request baru:
- `note` → required|string
- `detail_id` → required|exists:service_request_details,id

#### File: `app/Http/Requests/ServiceRequest/UpdateServiceRequestSolution.php`
Form request baru:
- `solution` → required|string
- `detail_id` → required|exists:service_request_details,id

#### File: `app/Http/Controllers/ServiceRequestController.php`
Menambahkan import:
```php
use App\Http\Requests\ServiceRequest\UpdateServiceRequestNote;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestSolution;
use App\Models\ServiceRequestDetail;
```

Menambahkan 2 method:
- `updateNote(UpdateServiceRequestNote $request, $id)` — update `complaint` di `ServiceRequestDetail`
- `updateSolution(UpdateServiceRequestSolution $request, $id)` — update `solution` di `ServiceRequestDetail`

### Frontend — Shared Component

#### File: `src/shared/components/service-request/detail/DetailNotes.tsx`
Diubah menjadi inline-edit component:
- Menambahkan props: `editable?: boolean`, `onSave?: (text: string) => Promise<void>`
- Mode display: teks readonly + tombol pencil icon
- Mode edit: `<textarea>` + tombol Simpan/Batal
- Loading state saat menyimpan

### Frontend IT

#### File: `src/modules/inbox/it/components/RequestDetailColumn.tsx`
- Menambahkan props: `onUpdateNote?: (text: string) => Promise<void>`, `onUpdateSolution?: (text: string) => Promise<void>`
- `DetailNotes` untuk Keterangan: `editable={!!onUpdateNote}`
- `DetailNotes` untuk Solusi: `editable={!!onUpdateSolution}`
- Solusi selalu dirender (tidak hanya saat `completionNote` non-empty)

#### File: `src/modules/inbox/it/InboxDetail.tsx`
- Menambahkan handler: `handleUpdateNote` — `runMutation` → PUT `/update-note`
- Menambahkan handler: `handleUpdateSolution` — `runMutation` → PUT `/update-solution`

### Frontend Admin

#### File: `src/modules/inbox/admin/components/RequestDetailColumn.tsx`
- Menambahkan props: `complaintText: string`, `onUpdateNote?: (text: string) => Promise<void>`, `onUpdateSolution?: (text: string) => Promise<void>`
- Menambahkan `DetailNotes` untuk Keterangan (sebelumnya tidak ada display keterangan di admin)
- `DetailNotes` untuk Solusi: `editable={!!onUpdateSolution}`

#### File: `src/modules/inbox/admin/InboxDetail.tsx`
- Menambahkan import: `getServiceRequestComplaint`, `getFirstServiceDetail`
- Menambahkan memo: `const complaintText = useMemo(...)`, `const firstDetail = useMemo(...)`
- Menambahkan handler: `handleUpdateNote` — `authenticatedRequest` → PUT `/update-note`
- Menambahkan handler: `handleUpdateSolution` — `authenticatedRequest` → PUT `/update-solution`

---

## 3. Perubahan Tombol Cari Vendor (IT)

### Deskripsi
Mengganti tombol "Pindah ke Vendor" menjadi "Cari Vendor" pada stage Workshop agar tidak ambigu.

### File: `src/modules/inbox/it/components/WorkshopActionCard.tsx`
- Prop `onMoveToVendor` → `onSearchVendor`
- Teks tombol: "Pindah ke Vendor" → "Cari Vendor"
- Icon: `bi-arrow-left-right` → `bi-search`

### File: `src/modules/inbox/it/components/ActionCardSwitch.tsx`
- Prop `onMoveToVendor` → `onSearchVendor` (type definition + passing)

### File: `src/modules/inbox/it/InboxDetail.tsx`
- Binding `onMoveToVendor={handleMoveToVendorSearch}` → `onSearchVendor={handleMoveToVendorSearch}`

---

## 4. Service List & Detail Admin Disamakan dengan IT

### Deskripsi
Admin service list dan detail view disamakan dengan IT (enrichment lokasi, stage badge, layout detail).

### File: `src/modules/service-request/admin/ServiceDetail.tsx`
- Ganti import dari `AdminInboxDetail` → `ItInboxDetail`

### File: `src/modules/service-request/admin/ServiceList.tsx`
- Enrichment: fetch detail + locations per item via `mapWithConcurrency`
- Derive workflow stage via `deriveItWorkflowStage`
- Kolom: `location`, `phone`, `stageLabel` (Badge warna sesuai stage)
- Column preset: `getItServiceListColumns`

### File: `src/modules/service-request/admin/ServiceList.css`
- Selector untuk class `.it-code`, `.it-actions`, `.it-detail-btn`, `.it-date-input`, `.it-status`

---

## 5. Setting Penerima Email

### Deskripsi
Menambahkan UI untuk mengelola daftar penerima email notifikasi (IT staff) di halaman Email Settings.

### File: `src/modules/settings/EmailSettings.tsx`
- State: `recipients`, `users`, `selectedUserId`, `addingRecipient`
- Fetch data: `GET /mail-settings/it-emails` + `GET /users`
- Section "Penerima Email": daftar recipient, toggle aktif/non-aktif (PUT), hapus (DELETE), tambah user (POST)

### File: `src/modules/settings/EmailSettings.css`
- Style: `.email-recipient-row`, `.email-recipient-toggle`, `.email-recipient-delete`, `.email-recipient-add`

---

## 6. Bugfix Atasan (Navigasi & Data Detail)

### Deskripsi
Atasan tidak bisa klik detail pending approval (redirect ke dashboard) dan data device/description tidak terbaca di dashboard.

### File: `src/modules/inbox/atasan/Inbox.tsx`
- `navigate('/inbox/${row.id}')` → `navigate('/services/${row.id}')`

### File: `src/modules/dashboard/atasan/Dashboard.tsx`
- `handleViewAllInbox`: `navigate('/inbox')` → `navigate('/services')`
- `handleViewDetails`: hapus conditional routePrefix, langsung `/services/${request.id}`
- `include` parameter: `service_request.status,service_request.details...` → `serviceRequest(user,status,details.device.deviceModel.deviceType)` (backend hanya parse format `serviceRequest(...)`)

### File: `src/modules/dashboard/atasan/Dashboard.tsx`
- `transformApprovalToRequest`: ganti akses `service?.service_request_details?.[0]` → `getServiceRequestComplaint(service)` (handle berbagai format data)

---

## Ringkasan File yang Diubah

### Backend (6 file)
| File | Perubahan |
|---|---|
| `routes/api.php` | +3 route (rollback middleware, update-note, update-solution) |
| `database/seeders/StatusTransitionSeeder.php` | +7 reverse transitions |
| `app/Http/Controllers/ServiceRequestController.php` | +2 method (updateNote, updateSolution) |
| `app/Http/Requests/ServiceRequest/UpdateServiceRequestNote.php` | **Baru** |
| `app/Http/Requests/ServiceRequest/UpdateServiceRequestSolution.php` | **Baru** |

### Frontend (16 file)
| File | Perubahan |
|---|---|
| `shared/components/.../DetailNotes.tsx` | Inline-edit mode |
| `modules/inbox/it/components/StatusOnlyCard.tsx` | +rollback |
| `modules/inbox/it/components/WorkshopActionCard.tsx` | +rollback, rename vendor button |
| `modules/inbox/it/components/VendorSetupActionCard.tsx` | +rollback |
| `modules/inbox/it/components/VendorQuoteActionCard.tsx` | +rollback |
| `modules/inbox/it/components/WaitingApprovalActionCard.tsx` | +rollback |
| `modules/inbox/it/components/CompleteAndBadAssetActionCard.tsx` | +rollback |
| `modules/inbox/it/components/ActionCardSwitch.tsx` | +rollback + rename vendor prop |
| `modules/inbox/it/components/RequestDetailColumn.tsx` | +edit note/solution |
| `modules/inbox/it/InboxDetail.tsx` | +rollback + edit note/solution |
| `modules/inbox/admin/components/ActionSidebar.tsx` | +rollback |
| `modules/inbox/admin/components/RequestDetailColumn.tsx` | +complaint display +edit |
| `modules/inbox/admin/InboxDetail.tsx` | +rollback + edit note/solution |
| `modules/inbox/admin/InboxDetail.css` | +.admin-action-rollback style |
