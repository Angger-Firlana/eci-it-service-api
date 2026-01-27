# ECI IT Service Management API - Complete Internal Documentation

**Version:** 1.0.0  
**Last Updated:** January 27, 2026 (Refactored Service Request Structure)  
**Maintained By:** Backend Engineering Team  
**For:** Internal Frontend Applications

---

## 📋 Internal Overview

### Business Purpose
The ECI IT Service Management API provides a centralized system for managing IT service requests, device inventory, vendor approvals, and invoice generation across the organization.

### Internal Systems Integration
- **Primary Consumers:** Internal Web Portal, Mobile Service App
- **Backend Services:** Laravel 10.x + MySQL
- **Authentication:** Laravel Sanctum (Token-based)

### Key Business Workflows
1. Service request lifecycle management (creation → approval → completion)
2. Device inventory tracking
3. Multi-level approval workflows for vendor services
4. Automated invoice generation upon service completion
5. Cost tracking and location management
6. Inbox notifications for approvers

---

## 🔐 Access & Authentication

### Obtaining Credentials
**For Frontend Developers:**
1. Contact DevOps team for environment-specific API keys
2. Use company SSO credentials for user authentication
3. Tokens are issued per-user via `/auth/login` endpoint

### Token Lifecycle
- **Expiration:** 24 hours (configurable per environment)
- **Refresh:** Re-authenticate via `/auth/login`
- **Storage:** Store securely in `httpOnly` cookies or encrypted localStorage

### Required Permissions
| Role | Access Level |
|------|-------------|
| `Admin` | Full CRUD on all resources |
| `User` | Create requests, view own requests |
| `Vendor` | View assigned approvals, update approval status |

---

## 🌐 Environment & Configuration

### Base URLs

| Environment | Base URL | Purpose |
|------------|----------|---------|
| **Development** | `http://localhost:8000/api` | Local testing |
| **Staging** | `https://staging-api.eci-itservice.com/api` | Pre-production validation |
| **Production** | `https://api.eci-itservice.com/api` | Live system |

### Environment Differences
- **Dev:** Relaxed CORS, verbose error messages
- **Staging:** Production-like data, audit logging enabled
- **Production:** Strict security headers, minimal error exposure

---

## 📡 Standard Headers

All authenticated requests must include:

```javascript
{
  "Authorization": "Bearer {token}",
  "Accept": "application/json",
  "Content-Type": "application/json",
  "X-Request-ID": "{uuid}" // Optional, for tracing
}
```

---

## 📚 API Endpoints

### 1. Authentication

#### 1.1 Login
`POST /auth/login`

**Business Purpose:** Authenticate internal users and obtain access token.

**Required Role:** None (public endpoint)

**Request Body:**
```json
{
  "email": "user@eci.com",
  "password": "securePassword123"
}
```

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@eci.com',
    password: 'securePassword123'
  })
});

const data = await response.json();
localStorage.setItem('auth_token', data.data.token);
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@eci.com",
      "role": { "id": 1, "name": "Admin" },
      "department": { "id": 1, "name": "IT" }
    },
    "token": "1|abcdef123456..."
  },
  "message": "Login successful"
}
```

---

#### 1.2 Register
`POST /auth/register`

**Business Purpose:** Create new user account (Admin only in production).

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@eci.com",
  "password": "securePassword123",
  "pin": "123456",
  "role_id": 3
}
```

---

#### 1.3 Logout
`POST /auth/logout`

**Business Purpose:** Invalidate current access token.

**Frontend Example:**
```javascript
await fetch('https://api.eci-itservice.com/api/auth/logout', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
});
localStorage.removeItem('auth_token');
```

---

#### 1.4 Get Current User
`GET /auth/me`

**Business Purpose:** Retrieve authenticated user details.

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/auth/me', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();
// Use for user profile display
```

---

### 2. Service Requests (Core)

#### 2.1 List Service Requests
`GET /service-requests`

**Business Purpose:** Retrieve paginated list of service requests with filtering.

**Required Role:** `Admin`, `User` (users see only their own)

**Query Parameters:**
- `search`: Filter by service number
- `user_id`: Filter by requesting user
- `admin_id`: Filter by assigned admin
- `status_id`: Filter by status
- `per_page`: Items per page (default: 15)

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/service-requests?status_id=1&per_page=20', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_number": "SR202601270001",
      "request_date": "2026-01-27",
      "status": { "id": 1, "name": "Pending" },
      "user": { "id": 2, "name": "Jane Smith" },
      "service_request_details": [...]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

---

#### 2.2 Get Service Request Statistics
`GET /service-requests/stats`

**Business Purpose:** Dashboard metrics (total requests, by status, recent).

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/service-requests/stats', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();
// data.total, data.by_status, data.recent
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total": 150,
    "by_status": [
      { "status": "Pending", "code": "PENDING", "count": 25 },
      { "status": "In Progress", "code": "IN_PROGRESS", "count": 50 }
    ],
    "recent": [...]
  }
}
```

---

#### 2.3 Get Service Request Detail
`GET /service-requests/{id}`

**Business Purpose:** Fetch complete request details including audit logs.

**Frontend Example:**
```javascript
const response = await fetch(`https://api.eci-itservice.com/api/service-requests/10`, {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "service_number": "SR202601270010",
    "user": {...},
    "admin": {...},
    "service_request_details": [...],
    "audit_logs": [...]
  }
}
```

---

#### 2.4 Create Service Request
`POST /service-requests`

**Business Purpose:** Submit new IT service request with device complaints.

**Required Role:** `Admin`, `User`

**Request Body:**
```json
{
  "admin_id": 1,
  "request_date": "2026-01-27",
  "status_id": 1,
  "details": [
    {
      "service_type_id": 1,
      "device_id": 5, // Optional if auto-creating
      "complaint": "Laptop screen flickering",
      "complaint_images": []
    },
    {
      "service_type_id": 2,
      "device_type_id": 1, // Required if device_id is omitted
      "brand": "Apple",
      "model": "MacBook Pro M3",
      "serial_number": "SN-MAC-2026-999",
      "complaint": "Keyboard issues"
    }
  ]
}
```

**Frontend Example (with File Upload):**
```javascript
const formData = new FormData();
formData.append('admin_id', '1');
formData.append('request_date', '2026-01-27');
formData.append('status_id', '1');
formData.append('details[0][device_id]', '5');
formData.append('details[0][complaint]', 'Screen flickering');
formData.append('details[0][complaint_images][]', fileInput.files[0]);

const response = await fetch('https://api.eci-itservice.com/api/service-requests', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData
});
```

**Internal Notes:**
- **Auto-Device Creation:** If `device_id` is omitted in details, you MUST provide `device_type_id`, `brand`, `model`, and `serial_number`. The system will automatically find or create the corresponding Device Model and Device.
- Images limited to 2MB, formats: jpeg, png, jpg, gif, svg
- Service number auto-generated (format: SR{YYYYMMDD}{sequence})
- **Invoice Trigger:** An invoice is automatically generated when status is updated to `COMPLETED` (ID 8).

---

#### 2.5 Update Service Request
`PUT /service-requests/{id}`

**Business Purpose:** Update request status, reassign admin, add notes.

**Request Body:**
```json
{
  "status_id": 2,
  "admin_id": 1,
  "estimated_date": "2026-02-01",
  "log_notes": "Parts ordered, awaiting delivery"
}
```

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/10`, {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    status_id: 2,
    log_notes: 'Moving to in progress'
  })
});
```

---

#### 2.6 Delete Service Request
`DELETE /service-requests/{id}`

**Business Purpose:** Remove service request (only if not completed).

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/10`, {
  method: 'DELETE',
  headers: { 'Authorization': `Bearer ${token}` }
});
```

---

#### 2.7 Get Allowed Status Transitions
`GET /service-requests/{id}/allowed-transitions`

**Business Purpose:** Fetch valid next statuses based on current user role (for dynamic UI buttons).

**Frontend Example:**
```javascript
const response = await fetch(`https://api.eci-itservice.com/api/service-requests/10/allowed-transitions`, {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { data } = await response.json();
// Render status buttons dynamically
data.forEach(status => {
  createButton(status.id, status.name);
});
```

---

### 3. Service Request Sub-Resources

#### 3.1 Costs Management

##### List Costs
`GET /service-requests/{serviceRequestId}/costs`

**Frontend Example:**
```javascript
const response = await fetch(`https://api.eci-itservice.com/api/service-requests/10/costs`, {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

##### Add Cost
`POST /service-requests/{serviceRequestId}/costs`

**Request Body:**
```json
{
  "cost_type_id": 1,
  "amount": 250000,
  "description": "Battery replacement"
}
```

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/10/costs`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    cost_type_id: 1,
    amount: 250000,
    description: 'Battery replacement'
  })
});
```

##### Update Cost
`PUT /service-requests/{serviceRequestId}/costs/{costId}`

##### Delete Cost
`DELETE /service-requests/{serviceRequestId}/costs/{costId}`

---

#### 3.2 Locations Management

##### List Locations
`GET /service-requests/{serviceRequestId}/locations`

**Business Purpose:** View service location history (internal vs external vendor).

##### Set Active Location
`POST /service-requests/{serviceRequestId}/locations`

**Request Body:**
```json
{
  "location_type": "external",
  "vendor_id": 2,
  "is_active": true
}
```

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/10/locations`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    location_type: 'external',
    vendor_id: 2,
    is_active: true
  })
});
```

##### Get Location Detail
`GET /service-requests/{serviceRequestId}/locations/{locationId}`

##### Update Location
`PUT /service-requests/{serviceRequestId}/locations/{locationId}`

##### Delete Location
`DELETE /service-requests/{serviceRequestId}/locations/{locationId}`

---

#### 3.3 Approvals Management

##### Get Approver List
`GET /service-requests/{serviceRequestId}/approver`

**Business Purpose:** Fetch potential approvers for this request.

##### List Approvals
`GET /service-requests/{serviceRequestId}/approvals`

##### Create Approval Workflow
`POST /service-requests/{serviceRequestId}/approvals`

**Request Body:**
```json
{
  "approvers": [1, 5, 12]
}
```

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/10/approvals`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    approvers: [1, 5, 12]
  })
});
```

##### Update Approvals
`PUT /service-requests/{serviceRequestId}/approvals`

##### Delete Approval
`DELETE /service-requests/{serviceRequestId}/approvals/{approvalId}`

##### Approve Request (by Vendor/Approver)
`POST /service-requests/approved/{approvalId}`

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/service-requests/approved/5`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
});
```

##### Approve Request (by Admin - Force)
`POST /service-requests/approved-by-admin/{serviceRequestId}`

##### Reject Request
`POST /service-requests/rejected/{approvalId}`

---

#### 3.4 Cancellation Management

##### Get Cancellation Detail
`GET /service-requests/{serviceRequestId}/cancellation`

##### Create Cancellation
`POST /service-requests/{serviceRequestId}/cancellation`

**Request Body:**
```json
{
  "reason": "User requested cancellation - no longer needed"
}
```

##### Update Cancellation
`PUT /service-requests/{serviceRequestId}/cancellation/{cancellationId}`

##### Delete Cancellation
`DELETE /service-requests/{serviceRequestId}/cancellation/{cancellationId}`

---

### 4. Device Management

#### 4.1 Device Types

##### List Device Types
`GET /device-type`

**Query Parameters:**
- `search`: Search by name

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/device-type', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

##### Get Device Type
`GET /device-type/{id}`

##### Create Device Type
`POST /device-type`

**Request Body:**
```json
{
  "name": "Laptop"
}
```

##### Update Device Type
`PUT /device-type/{id}`

##### Delete Device Type
`DELETE /device-type/{id}`

---

#### 4.2 Device Models

##### List Device Models
`GET /device-model`

**Query Parameters:**
- `keyword`: Search by brand/model

##### Create Device Model
`POST /device-model`

**Request Body:**
```json
{
  "device_type_id": 1,
  "brand": "Dell",
  "model": "XPS 15"
}
```

##### Update Device Model (Full)
`PUT /device-model/{id}`

##### Update Device Model (Partial)
`PATCH /device-model/{id}`

##### Delete Device Model
`DELETE /device-model/{id}`

---

#### 4.3 Devices (Assets)

##### List Devices
`GET /devices`

**Query Parameters:**
- `serial-number`: Filter by serial
- `brand`: Filter by brand
- `model`: Filter by model

##### Create Device
`POST /devices`

**Request Body:**
```json
{
  "device_model_id": 1,
  "serial_number": "SN-ABC-12345"
}
```

**Frontend Example:**
```javascript
await fetch('https://api.eci-itservice.com/api/devices', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    device_model_id: 1,
    serial_number: 'SN-ABC-12345'
  })
});
```

##### Update Device (Full)
`PUT /devices/{id}`

##### Update Device (Partial)
`PATCH /devices/{id}`

##### Delete Device
`DELETE /devices/{id}`

---

### 5. Organization Management

#### 5.1 Departments

##### List Departments
`GET /departments`

**Query Parameters:**
- `search`: Search by name/code
- `sort_by`: Field to sort by
- `sort_order`: asc/desc
- `per_page`: Items per page

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/departments?search=IT', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

##### Get Department
`GET /departments/{id}`

##### Create Department
`POST /departments`

**Request Body:**
```json
{
  "name": "Information Technology",
  "code": "IT"
}
```

##### Update Department
`PUT /departments/{id}`

##### Delete Department
`DELETE /departments/{id}`

---

#### 5.2 Users

##### List Users
`GET /users`

**Query Parameters:**
- `search`: Search by name/email
- `role_id`: Filter by role
- `department_id`: Filter by department
- `status`: Filter by status
- `sort_by`, `sort_order`, `per_page`

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/users?role_id=1&per_page=50', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

##### Get User
`GET /users/{id}`

##### Create User
`POST /users`

**Request Body:**
```json
{
  "name": "John Smith",
  "email": "john.smith@eci.com",
  "password": "securePassword123",
  "role_id": 3,
  "department_id": 1
}
```

##### Update User
`PUT /users/{id}`

##### Delete User
`DELETE /users/{id}`

---

#### 5.3 Vendors

##### List Vendors
`GET /vendors`

**Query Parameters:**
- `search`: Search by name

##### Get Vendor
`GET /vendors/{id}`

##### Create Vendor
`POST /vendors`

**Request Body:**
```json
{
  "name": "Tech Solutions Inc.",
  "maps_url": "https://maps.google.com/?q=TechSolutions",
  "description": "Laptop and desktop repair specialist"
}
```

##### Update Vendor
`PUT /vendors/{id}`

##### Delete Vendor
`DELETE /vendors/{id}`

---

#### 5.4 Cost Types

##### List Cost Types
`GET /cost-types`

##### Get Cost Type
`GET /cost-types/{id}`

##### Create Cost Type
`POST /cost-types`

**Request Body:**
```json
{
  "code": "LABOR",
  "name": "Labor Cost"
}
```

##### Update Cost Type
`PUT /cost-types/{id}`

##### Delete Cost Type
`DELETE /cost-types/{id}`

---

### 6. Reference Data (Lookups)

**Business Purpose:** Populate dropdown menus and select options in UI.

#### Available Endpoints:

##### Get Service Types
`GET /references/service-types`

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/references/service-types', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();
// Populate <select> options
```

##### Get Statuses
`GET /references/statuses?entity_type_id={1|2}`

**Query Parameters:**
- `entity_type_id`: 1 for Service Requests, 2 for Invoices

##### Get Vendors
`GET /references/vendors`

##### Get Roles
`GET /references/roles`

##### Get Departments
`GET /references/departments`

##### Get Cost Types
`GET /references/cost-types`

**Success Response Format:**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Option 1" },
    { "id": 2, "name": "Option 2" }
  ]
}
```

---

### 7. Invoices

#### 7.1 List Invoices
`GET /invoices`

**Query Parameters:**
- `service_request_id`: Filter by service request
- `status`: Filter by invoice status
- `vendor_id`: Filter by vendor
- `start_date`, `end_date`: Date range
- `search`: Search by invoice number

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/invoices?status=paid', {
  headers: { 'Authorization': `Bearer ${token}` }
});
```

---

#### 7.2 Get Invoice Detail
`GET /invoices/{id}`

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV202601270001",
    "service_request_id": 10,
    "issue_date": "2026-01-27",
    "due_date": "2026-02-10",
    "total_amount": 1500000,
    "status": { "id": 13, "name": "Paid" }
  }
}
```

---

#### 7.3 Get Invoice Print Data
`GET /invoices/{id}/print`

**Business Purpose:** Fetch formatted data for print/PDF rendering.

**Frontend Example:**
```javascript
const response = await fetch(`https://api.eci-itservice.com/api/invoices/1/print`, {
  headers: { 'Authorization': `Bearer ${token}` }
});
const printData = await response.json();
// Use for print preview or PDF generation
```

---

#### 7.4 Download Invoice PDF
`GET /service-requests/{id}/download-invoice`

**Alternative:** `GET /export-invoice/{id}`

**Business Purpose:** Direct PDF download link.

**Frontend Example:**
```javascript
// Open in new tab
window.open(`https://api.eci-itservice.com/api/service-requests/10/download-invoice?token=${token}`, '_blank');
```

---

### 8. Inbox & Notifications

#### 8.1 Get Inbox Approvals
`GET /inbox-approvals/{statusId}`

**Business Purpose:** Fetch approval tasks assigned to current user.

**Frontend Example:**
```javascript
const response = await fetch('https://api.eci-itservice.com/api/inbox-approvals/4', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();
// Display notification count: data.length
```

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "service_request_id": 10,
      "approver_id": 2,
      "status_id": 4,
      "read_at": null,
      "service_request": {...}
    }
  ]
}
```

---

#### 8.2 Mark Inbox as Read
`PUT /inbox-approvals/{id}/read`

**Frontend Example:**
```javascript
await fetch(`https://api.eci-itservice.com/api/inbox-approvals/1/read`, {
  method: 'PUT',
  headers: { 'Authorization': `Bearer ${token}` }
});
```

---

## 📐 Response Conventions

### Standard Format
```json
{
  "success": true | false,
  "data": { ... } | [ ... ],
  "message": "Human-readable message",
  "meta": { ... } // Only for paginated responses
}
```

### Pagination
```json
"meta": {
  "current_page": 1,
  "last_page": 10,
  "per_page": 15,
  "total": 145,
  "from": 1,
  "to": 15
}
```

### Date & Time Format
- **ISO 8601:** `2026-01-27T14:30:00Z`
- **Date Only:** `2026-01-27`

---

## ⚠️ Error Handling

### Authentication Errors
| Code | Meaning | UI Action |
|------|---------|-----------|
| `401` | Token expired/invalid | Redirect to login |
| `403` | Insufficient permissions | Show "Access Denied" message |

### Business Rule Errors
| Code | Meaning | UI Action |
|------|---------|-----------|
| `422` | Validation failed | Display field-level errors |
| `404` | Resource not found | Show "Not Found" page |
| `500` | Server error | Show generic error, log details |

### Error Response Format
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

**Frontend Error Handling Example:**
```javascript
async function apiRequest(url, options) {
  try {
    const response = await fetch(url, options);
    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 401) {
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
      } else if (response.status === 422) {
        // Display validation errors
        Object.keys(data.errors).forEach(field => {
          showFieldError(field, data.errors[field][0]);
        });
      } else if (response.status === 403) {
        showToast('You do not have permission to perform this action', 'error');
      } else {
        showToast(data.message || 'An error occurred', 'error');
      }
      throw new Error(data.message);
    }
    
    return data;
  } catch (error) {
    console.error('API Error:', error);
    throw error;
  }
}
```

---

## 🔒 Security & Compliance

### Data Sensitivity Levels
| Field | Sensitivity | UI Handling |
|-------|------------|-------------|
| `password` | **Critical** | Never log or display |
| `token` | **High** | Store in httpOnly cookies |
| `email` | **Medium** | Mask in logs |
| `service_number` | **Low** | Safe to display |

### Fields Not to Expose
- User passwords (never returned in responses)
- Internal system IDs (use UUIDs for external references)
- Audit log actor details (for privacy)
- PIN codes (for additional security)

### Logging Requirements
- **DO Log:** Request IDs, timestamps, status codes, user actions
- **DO NOT Log:** Tokens, passwords, PII without masking

### File Upload Security
- **Max Size:** 2MB per file
- **Allowed Types:** jpeg, png, jpg, gif, svg
- **Validation:** Server-side MIME type checking
- **Storage:** Files stored with hashed names, not original filenames

---

## 🔄 Versioning & Change Management

### Backward Compatibility
- **Minor changes:** Additive only (new fields, endpoints)
- **Breaking changes:** Require new API version (v2, v3)

### Deprecation Policy
1. **Announcement:** 90 days notice via internal Slack channel
2. **Warning Period:** Deprecated endpoints return `X-Deprecated: true` header
3. **Sunset:** Endpoint removed after 180 days

### Change Notifications
- **Channel:** `#api-updates` on Slack
- **Format:** Migration guides provided for breaking changes
- **Testing:** Staging environment updated 2 weeks before production

---

## 💡 Best Practices for Frontend Teams

### Token Management
```javascript
// Store token securely
const setAuthToken = (token) => {
  localStorage.setItem('auth_token', token);
  // Also set in axios defaults if using axios
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
};

// Clear token on logout
const clearAuthToken = () => {
  localStorage.removeItem('auth_token');
  delete axios.defaults.headers.common['Authorization'];
};
```

### Request Wrapper
```javascript
const api = {
  baseURL: 'https://api.eci-itservice.com/api',
  
  async request(endpoint, options = {}) {
    const token = localStorage.getItem('auth_token');
    const headers = {
      'Accept': 'application/json',
      ...options.headers
    };
    
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }
    
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers
    });
    
    return this.handleResponse(response);
  },
  
  async handleResponse(response) {
    const data = await response.json();
    
    if (!response.ok) {
      if (response.status === 401) {
        clearAuthToken();
        window.location.href = '/login';
      }
      throw new Error(data.message || 'Request failed');
    }
    
    return data;
  },
  
  get(endpoint, params) {
    const query = new URLSearchParams(params).toString();
    return this.request(`${endpoint}${query ? '?' + query : ''}`);
  },
  
  post(endpoint, body) {
    return this.request(endpoint, { method: 'POST', body });
  },
  
  put(endpoint, body) {
    return this.request(endpoint, { method: 'PUT', body });
  },
  
  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  }
};

// Usage
const data = await api.get('/service-requests', { status_id: 1 });
await api.post('/service-requests', requestData);
```

### Pagination Helper
```javascript
const usePagination = (endpoint, params = {}) => {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(false);
  
  const fetchPage = async (page = 1) => {
    setLoading(true);
    try {
      const response = await api.get(endpoint, { ...params, page });
      setData(response.data);
      setMeta(response.meta);
    } catch (error) {
      console.error('Pagination error:', error);
    } finally {
      setLoading(false);
    }
  };
  
  return { data, meta, loading, fetchPage };
};
```

---

## 📞 Support & Contacts

- **API Issues:** backend-team@eci.com
- **Access Requests:** devops@eci.com
- **Documentation Updates:** Submit PR to internal GitLab repo
- **Emergency Hotline:** +62-xxx-xxxx-xxxx (24/7)

---

## 📝 Changelog

### Version 1.0.0 (January 27, 2026)
- Initial comprehensive documentation
- All endpoints documented with frontend examples
- Security guidelines established
- Best practices section added

### Version 1.1.0 (January 27, 2026)
- **Breaking Change:** Moved `service_type_id` from service request header to detail level.
- **New Feature:** Implemented automatic device and device model creation when `device_id` is omitted in request details.
- **Bug Fix:** Fixed storage cleanup bug for complaint images during service request deletion.
- **Bug Fix:** Corrected invoice generation trigger (now fires on `COMPLETED` status).
- **Update:** Relaxed password length validation on login.

---

*Last reviewed: January 27, 2026*  
*Next review: April 27, 2026*  
*Document Owner: Backend Engineering Team*
