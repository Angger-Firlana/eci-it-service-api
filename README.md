# Dokumentasi API ECI IT Service

Dokumentasi ini menyediakan detail untuk semua endpoint API yang tersedia.

## Struktur Respons Standar

### Respons Sukses
```json
{
  "success": true,
  "data": {},
  "message": "Pesan sukses",
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0,
    "from": 1,
    "to": 0
  }
}
```

- `meta` hanya ada pada response yang melibatkan paginasi.

### Respons Error
```json
{
  "success": false,
  "message": "Pesan error",
  "errors": {}
}
```
- `errors` berisi detail validasi atau error lainnya.

---

## Otentikasi

Endpoint yang terkait dengan otentikasi pengguna.

### `POST /api/auth/login`

Mengotentikasi pengguna dan mengembalikan token akses.

**Request Body:**
- `email` (string, required, email): Alamat email pengguna.
- `password` (string, required, min:8): Kata sandi pengguna.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Nama Pengguna",
      "email": "user@example.com",
      "role": {
        "id": 3,
        "name": "User"
      },
      "department": null,
      "created_at": "...",
      "updated_at": "..."
    },
    "token": "1|token_akses_pengguna"
  },
  "message": "Login successful"
}
```

**Contoh Respons Error (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### `POST /api/auth/register`

Mendaftarkan pengguna baru.

**Request Body:**
- `name` (string, required): Nama lengkap pengguna.
- `email` (string, required, email, unique:users): Alamat email unik.
- `password` (string, required, min:8): Kata sandi pengguna.
- `pin` (string, optional, min:6): PIN untuk otorisasi tambahan.
- `role_id` (integer, required, exists:roles,id): ID dari peran pengguna.

**Contoh Respons Sukses (201 Created):**
```json
{
  "success": true,
  "data": {
    "user": { ... }, // Struktur sama seperti login
    "token": "2|token_akses_baru"
  },
  "message": "Registration successful"
}
```

### `POST /api/auth/logout`

Menghapus token akses pengguna yang sedang login. Membutuhkan otentikasi (Bearer Token).

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": null,
  "message": "Logout successful"
}
```

### `GET /api/auth/me`

Mendapatkan data pengguna yang sedang login. Membutuhkan otentikasi (Bearer Token).

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": { ... }, // Struktur user sama seperti login
  "message": "User found"
}
```

---

## Tipe Perangkat (Device Type)

CRUD untuk mengelola tipe perangkat (misal: Laptop, Printer).

### `GET /api/device-type`

Mendapatkan daftar tipe perangkat dengan paginasi.
- **Query Params:** `search` (opsional) untuk mencari berdasarkan nama.

### `GET /api/device-type/{id}`

Mendapatkan detail satu tipe perangkat.

### `POST /api/device-type`

Membuat tipe perangkat baru.
- **Request Body:** `name` (string, required, unique:device_types).

### `PUT /api/device-type/{id}`

Memperbarui nama tipe perangkat.
- **Request Body:** `name` (string, required, unique:device_types).

### `DELETE /api/device-type/{id}`

Menghapus tipe perangkat.

**Contoh Objek `DeviceType`:**
```json
{
  "id": 1,
  "name": "Laptop",
  "created_at": "...",
  "updated_at": "..."
}
```

---

## Model Perangkat (Device Model)

CRUD untuk mengelola model spesifik dari sebuah perangkat.

### `GET /api/device-model`

Mendapatkan daftar model perangkat dengan paginasi.
- **Query Params:** `keyword` (opsional) untuk mencari berdasarkan nama model.

### `GET /api/device-model/{id}`

Mendapatkan detail satu model perangkat.

### `POST /api/device-model`

Membuat model perangkat baru.
- **Request Body:**
  - `device_type_id` (integer, required, exists:device_types,id)
  - `brand` (string, required)
  - `model` (string, required)

### `PUT /api/device-model/{id}`

Memperbarui semua field model perangkat.
- **Request Body:**
  - `device_type_id` (integer, required)
  - `brand` (string, required)
  - `model` (string, required)

### `PATCH /api/device-model/{id}`

Memperbarui sebagian field model perangkat.
- **Request Body:**
  - `device_type_id` (integer, optional)
  - `brand` (string, optional)
  - `model` (string, optional)

### `DELETE /api/device-model/{id}`

Menghapus model perangkat.

**Contoh Objek `DeviceModel`:**
```json
{
    "id": 1,
    "device_type_id": 1,
    "brand": "Dell",
    "model": "Latitude 5420",
    "created_at": "...",
    "updated_at": "..."
}
```

---

## Perangkat (Device)



CRUD untuk mengelola perangkat individual yang dimiliki.



### `GET /api/devices`



Mendapatkan daftar perangkat dengan paginasi.

- **Query Params:** `serial-number`, `brand`, `model` (semua opsional) untuk filter.



### `GET /api/devices/{id}`



Mendapatkan detail satu perangkat.



### `POST /api/devices`



Membuat perangkat baru.

- **Request Body:**

  - `device_model_id` (integer, required, exists:device_models,id)

  - `serial_number` (string, required, unique:devices)



### `PUT /api/devices/{id}`



Memperbarui semua field perangkat.

- **Request Body:**

  - `device_model_id` (integer, required)

  - `serial_number` (string, required, unique:devices)



### `PATCH /api/devices/{id}`



Memperbarui sebagian field perangkat.

- **Request Body:**

  - `device_model_id` (integer, optional)

  - `serial_number` (string, optional, unique:devices)



### `DELETE /api/devices/{id}`



Menghapus perangkat.



**Contoh Objek `Device`:**

```json

{

    "id": 1,

    "device_model_id": 1,

    "serial_number": "SN12345XYZ",

    "created_at": "...",

    "updated_at": "..."

}

```



---



## Permintaan Layanan (Service Request)



Endpoint untuk mengelola seluruh alur permintaan layanan.



### `GET /api/service-requests`



Mendapatkan daftar semua permintaan layanan dengan paginasi.

- **Query Params:** `user_id`, `admin_id`, `service_type_id`, `status_id`, `request_date`, `estimated_date`, `search` (berdasarkan `service_number`). Semua opsional.



### `GET /api/service-requests/stats`



Mendapatkan statistik ringkas mengenai permintaan layanan (total, status, terbaru).



### `GET /api/service-requests/{id}`



Mendapatkan detail lengkap satu permintaan layanan, termasuk relasi seperti detail, perangkat, gambar, dan log audit.



### `POST /api/service-requests`



Membuat permintaan layanan baru.

- **Request Body:**

  - `admin_id` (integer, required)

  - `user_id` (integer, optional)

  - `service_type_id` (integer, optional)

  - `request_date` (date, required)

  - `status_id` (integer, required)

  - `details` (array, required): Daftar keluhan.

    - `details.*.device_id` (integer, required)

    - `details.*.complaint` (string, required)

    - `details.*.complaint_images` (array, optional): Array file gambar.



### `PUT /api/service-requests/{id}`



Memperbarui permintaan layanan. Digunakan untuk mengubah status, menambah/mengubah detail, biaya, lokasi, dll.

- **Request Body:** Berisi field-field dari `POST` dan juga field tambahan seperti `estimated_date`, `service_location`, `service_costs`, `service_cancellation`, dan `log_notes`. Semua field opsional.



### `DELETE /api/service-requests/{id}`



Menghapus permintaan layanan.



### `GET /api/service-requests/{id}/allowed-transitions`



Mendapatkan daftar status berikutnya yang valid untuk suatu permintaan layanan berdasarkan peran pengguna yang login.



### `GET /api/service-requests/{id}/download-invoice`



Mengunduh file PDF invoice untuk permintaan layanan yang sudah selesai.

**Catatan:** Selain endpoint di atas, tersedia juga endpoint alternatif:

### `GET /api/export-invoice/{id}`

Mengunduh file PDF invoice berdasarkan `service_request_id`.

**Response:** File download `application/pdf`.



**Contoh Objek `ServiceRequest` (Detail View):**

```json

{

    "id": 1,

    "user_id": 2,

    "admin_id": 1,

    "service_type_id": 1,

    "service_number": "SR202601230001",

    "request_date": "...",

    "estimated_date": null,

    "status_id": 1,

    "user": { "id": 2, "name": "User Name", "email": "user@example.com" },

    "admin": { "id": 1, "name": "Admin Name", "email": "admin@example.com" },

    "service_type": { "id": 1, "name": "Perbaikan" },

    "status": { "id": 1, "name": "Pending" },

    "service_request_details": [

        {

            "id": 1,

            "service_request_id": 1,

            "device_id": 1,

            "complaint": "Layar rusak",

            "device": {

                "id": 1,

                "device_model_id": 1,

                "serial_number": "SN12345XYZ",

                "device_model": { "id": 1, "brand": "Dell", "model": "Latitude 5420" }

            },

            "complaint_images": [

                {

                    "id": 1,

                    "service_request_detail_id": 1,

                    "image_path": "path/to/image.jpg"

                }

            ]

        }

    ],

    "audit_logs": [ ... ]

}

```



### Sub-resource: Biaya (`/costs`)

Endpoint biaya untuk suatu service request.

#### `GET /api/service-requests/{serviceRequestId}/costs`

Mengembalikan daftar biaya untuk service request.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_request_id": 10,
      "cost_type_id": 1,
      "amount": 150000,
      "description": "Sparepart",
      "created_at": "2026-01-23T10:00:00.000000Z",
      "updated_at": "2026-01-23T10:00:00.000000Z"
    }
  ],
  "message": "Success"
}
```

#### `POST /api/service-requests/{serviceRequestId}/costs`

Menambah biaya pada service request.

**Request Body:**
- `cost_type_id` (integer, required, exists:cost_types,id)
- `amount` (number, required, min:0)
- `description` (string, nullable)

**Contoh Request:**
```json
{
  "cost_type_id": 1,
  "amount": 150000,
  "description": "Sparepart"
}
```

**Contoh Respons Sukses (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_request_id": 10,
    "cost_type_id": 1,
    "amount": 150000,
    "description": "Sparepart",
    "created_at": "2026-01-23T10:00:00.000000Z",
    "updated_at": "2026-01-23T10:00:00.000000Z"
  },
  "message": "Cost added successfully"
}
```

#### `PUT /api/service-requests/{serviceRequestId}/costs/{costId}`

Update biaya.

**Request Body (semua opsional):**
- `cost_type_id` (integer, exists:cost_types,id)
- `amount` (number, min:0)
- `description` (string)

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_request_id": 10,
    "cost_type_id": 2,
    "amount": 200000,
    "description": "Service fee",
    "created_at": "2026-01-23T10:00:00.000000Z",
    "updated_at": "2026-01-23T10:10:00.000000Z"
  },
  "message": "Cost updated successfully"
}
```

#### `DELETE /api/service-requests/{serviceRequestId}/costs/{costId}`

Hapus biaya.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": null,
  "message": "Cost removed successfully"
}
```



### Sub-resource: Lokasi (`/locations`)

Endpoint lokasi untuk service request. Data lokasi tersimpan di tabel `service_locations`.

#### `GET /api/service-requests/{serviceRequestId}/locations`

Mengambil semua lokasi (historical) milik service request.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_request_id": 10,
      "location_type": "external",
      "vendor_id": 2,
      "is_active": true,
      "vendor": {
        "id": 2,
        "name": "Tech Solutions Inc.",
        "maps_url": "https://maps.google.com/...",
        "description": "..."
      },
      "created_at": "2026-01-23T10:00:00.000000Z",
      "updated_at": "2026-01-23T10:00:00.000000Z"
    }
  ],
  "message": "Service locations retrieved successfully"
}
```

#### `POST /api/service-requests/{serviceRequestId}/locations`

Set lokasi aktif. Jika sudah ada lokasi aktif, endpoint ini akan melakukan update lokasi aktif tersebut.

**Request Body:**
- `location_type` (string, required, in:internal,external)
- `vendor_id` (integer, required_if:location_type,external, exists:vendors,id)
- `is_active` (boolean, required)

**Contoh Request:**
```json
{
  "location_type": "external",
  "vendor_id": 2,
  "is_active": true
}
```

**Contoh Respons Sukses (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_request_id": 10,
    "location_type": "external",
    "vendor_id": 2,
    "is_active": true,
    "vendor": {
      "id": 2,
      "name": "Tech Solutions Inc.",
      "maps_url": "https://maps.google.com/...",
      "description": "..."
    },
    "created_at": "2026-01-23T10:00:00.000000Z",
    "updated_at": "2026-01-23T10:00:00.000000Z"
  },
  "message": "Service location set successfully"
}
```

#### `GET /api/service-requests/{serviceRequestId}/locations/{locationId}`

Mengambil detail satu lokasi.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_request_id": 10,
    "location_type": "external",
    "vendor_id": 2,
    "is_active": true,
    "vendor": {
      "id": 2,
      "name": "Tech Solutions Inc.",
      "maps_url": "https://maps.google.com/...",
      "description": "..."
    },
    "created_at": "2026-01-23T10:00:00.000000Z",
    "updated_at": "2026-01-23T10:00:00.000000Z"
  },
  "message": "Service location retrieved successfully"
}
```

#### `PUT /api/service-requests/{serviceRequestId}/locations/{locationId}`

Update lokasi.

**Request Body:**
- `location_type` (string, optional)
- `vendor_id` (integer, required_if:location_type,external)
- `is_active` (boolean, required)

**Contoh Respons Error (400 Bad Request):**
```json
{
  "success": false,
  "message": "Location does not belong to this service request",
  "errors": "Location does not belong to this service request"
}
```

#### `DELETE /api/service-requests/{serviceRequestId}/locations/{locationId}`

Hapus lokasi.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": null,
  "message": "Service location deleted successfully"
}
```



### Sub-resource: Persetujuan (`/approvals` & `/approved`, `/rejected`)

Endpoint persetujuan vendor untuk service request.

#### `GET /api/service-requests/{serviceRequestId}/approvals`

Mengambil daftar approval vendor berdasarkan service request.

**Contoh Respons Sukses (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_request_id": 10,
      "approval_policy_id": 1,
      "approval_policy_step_id": 1,
      "assigned_by": 1,
      "approver_id": 2,
      "approved_at": null,
      "status_id": 4,
      "created_at": "2026-01-23T10:00:00.000000Z",
      "updated_at": "2026-01-23T10:00:00.000000Z"
    }
  ],
  "message": "Service request approvals retrieved successfully"
}
```

#### `POST /api/service-requests/{serviceRequestId}/approvals`

Membuat approval vendor. Body berupa array item.

**Request Body (array):**
- `*.approval_policy_id` (required, exists:approval_policies,id)
- `*.approval_policy_step_id` (required, exists:approval_policy_steps,id)
- `*.assigned_by` (required, exists:users,id)
- `*.approver_id` (required, exists:users,id)
- `*.status_id` (required, exists:statuses,id)

**Contoh Request:**
```json
[
  {
    "approval_policy_id": 1,
    "approval_policy_step_id": 1,
    "assigned_by": 1,
    "approver_id": 2,
    "status_id": 4
  }
]
```

#### `PUT /api/service-requests/{serviceRequestId}/approvals`

Update approval vendor (body array, semua field opsional).

#### `DELETE /api/service-requests/{serviceRequestId}/approvals/{approvalId}`

Hapus approval vendor.

#### `POST /api/service-requests/approved/{approvalId}`

Approve vendor request.

#### `POST /api/service-requests/rejected/{approvalId}`

Reject vendor request.



### Sub-resource: Pembatalan (`/cancellation`)







- `GET /api/service-requests/{serviceRequestId}/cancellation`: Melihat detail pembatalan.



- `POST /api/service-requests/{serviceRequestId}/cancellation`: Membatalkan permintaan layanan.



- `PUT /api/service-requests/{serviceRequestId}/cancellation`: Memperbarui alasan pembatalan.

#### `POST /api/service-requests/{serviceRequestId}/cancellation`

**Request Body:**
- `reason` (string, required)

**Contoh Request:**
```json
{
  "reason": "User requested cancellation"
}
```

**Contoh Respons Sukses (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "service_request_id": 10,
    "reason": "User requested cancellation",
    "canceled_by": 2,
    "created_at": "2026-01-23T10:00:00.000000Z",
    "updated_at": "2026-01-23T10:00:00.000000Z"
  },
  "message": "Service request cancelled successfully"
}
```

#### `PUT /api/service-requests/{serviceRequestId}/cancellation`

Update cancellation.

**Contoh Request:**
```json
{
  "reason": "Updated reason"
}
```

#### `GET /api/service-requests/{serviceRequestId}/cancellation`

Get cancellation detail.







---







## Data Referensi







Endpoint untuk mendapatkan daftar data yang umumnya statis.







### `GET /api/references/service-types`



Mengembalikan daftar semua tipe layanan.






`
### `GET /api/references/statuses`


`
Mengembalikan daftar semua status.



- **Query Params:** `entity_type_id` (opsional) untuk filter berdasarkan tipe entitas.







### `GET /api/references/vendors`



Mengembalikan daftar semua vendor.







### `GET /api/references/roles`



Mengembalikan daftar semua peran pengguna.







### `GET /api/references/departments`



Mengembalikan daftar semua departemen.







### `GET /api/references/users`



Mengembalikan daftar semua pengguna (hanya ID, nama, email).







---







## Departemen







CRUD untuk mengelola departemen.







### `GET /api/departments`



Mendapatkan daftar departemen dengan paginasi.



- **Query Params:** `search`, `sort_by`, `sort_order`, `per_page`.







### `GET /api/departments/{id}`



Mendapatkan detail satu departemen.







### `POST /api/departments`



Membuat departemen baru.



- **Request Body:**



  - `name` (string, required, unique)



  - `code` (string, required, unique)







### `PUT /api/departments/{id}`



Memperbarui departemen.



- **Request Body:**



  - `name` (string, required)



  - `code` (string, required)







### `DELETE /api/departments/{id}`



Menghapus departemen.







**Contoh Objek `Department`:**



```json



{



    "id": 1,



    "name": "Teknologi Informasi",



    "code": "IT",



    "created_at": "...",



    "updated_at": "..."



}



```






## Pengguna (User)







CRUD untuk mengelola pengguna.







### `GET /api/users`



Mendapatkan daftar pengguna dengan paginasi.



- **Query Params:** `search`, `role_id`, `department_id`, `status`, `sort_by`, `sort_order`, `per_page`.







### `GET /api/users/{id}`



Mendapatkan detail satu pengguna.







### `POST /api/users`



Membuat pengguna baru.



- **Request Body:**
  - `name` (string, required)



  - `email` (string, required, email, unique)



  - `password` (string, required, min:8)



  - `role_id` (integer, required, exists:roles)



  - `department_id` (integer, required, exists:departments)







### `PUT /api/users/{id}`



Memperbarui pengguna.



- **Request Body:** Field sama seperti `POST`, namun semua opsional dan `email` harus unik untuk pengguna selain dirinya.







### `DELETE /api/users/{id}`



Menghapus pengguna.







**Contoh Objek `User`:**



```json



{



    "id": 1,



    "name": "John Doe",



    "email": "john@example.com",



    "email_verified_at": null,



    "created_at": "...",



    "updated_at": "...",



    "departments": [ { "id": 1, "name": "IT" } ],



    "roles": [ { "id": 1, "name": "Admin" } ]



}


```





## Invoice





Endpoint untuk melihat data invoice.







### `GET /api/invoices`



Mendapatkan daftar invoice dengan paginasi.



- **Query Params:** `service_request_id`, `status`, `vendor_id`, `start_date`, `end_date`, `search` (berdasarkan `invoice_number`).







### `GET /api/invoices/{id}`



Mendapatkan detail satu invoice.







### `GET /api/invoices/{id}/print`



Mendapatkan data yang diformat untuk keperluan cetak invoice.







**Contoh Objek `Invoice`:**



```json



{



    "id": 1,



    "invoice_number": "INV202601230001",



    "service_request_id": 1,



    "issue_date": "...",



    "due_date": "...",



    "total_amount": 500000.00,



    "status_id": 13,



    "created_at": "...",



    "updated_at": "...",



    "service_request": { ... }



}



```

## Export Invoice

Endpoint untuk mengunduh invoice dalam bentuk PDF.

### `GET /api/service-requests/{id}/download-invoice`

Mengunduh PDF invoice berdasarkan `service_request_id`.

**Response:** File download `application/pdf`.

### `GET /api/export-invoice/{id}`

Mengunduh PDF invoice berdasarkan `service_request_id`.

**Response:** File download `application/pdf`.







---







## Vendor







CRUD untuk mengelola vendor pihak ketiga.







### `GET /api/vendors`



Mendapatkan daftar vendor dengan paginasi.



- **Query Params:** `search` (berdasarkan nama).







### `GET /api/vendors/{id}`



Mendapatkan detail satu vendor.







### `POST /api/vendors`



Membuat vendor baru.



- **Request Body:**



  - `name` (string, required)



  - `maps_url` (string, required, url)



  - `description` (string, required)







### `PUT /api/vendors/{id}`



Memperbarui vendor.



- **Request Body:** `name`, `maps_url`, `description`. Semua opsional.







### `DELETE /api/vendors/{id}`



Menghapus vendor.







**Contoh Objek `Vendor`:**



```json



{



    "id": 1,



    "name": "Service Center Resmi",



    "maps_url": "https://maps.google.com/...",



    "description": "Pusat servis resmi untuk berbagai merk.",



    "created_at": "...",



    "updated_at": "..."



}



```




