# ECI IT Service API Documentation

This document provides comprehensive documentation for all API endpoints in the ECI IT Service application.

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Devices](#devices)
- [Device Types](#device-types)
- [Device Models](#device-models)
- [Service Requests](#service-requests)
- [Service Request Costs](#service-request-costs)
- [Service Request Locations](#service-request-locations)
- [Service Request Approvals](#service-request-approvals)
- [Service Request Cancellation](#service-request-cancellation)
- [Departments](#departments)
- [Users](#users)
- [Vendors](#vendors)
- [Cost Types](#cost-types)
- [Invoices](#invoices)
- [Inbox Approvals](#inbox-approvals)
- [Reference Data](#reference-data)

---

## Overview

### Base URL

All API endpoints are prefixed with `/api`.

### Authentication

Most endpoints require authentication using Laravel Sanctum. Include the token in the `Authorization` header:

```
Authorization: Bearer <your-token>
```

### Response Format

All API responses follow a consistent format:

**Success Response:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Operation successful",
    "data": { ... }
}
```

**Success Response with Pagination:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Operation successful",
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "path": "http://localhost/api/endpoint",
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```

**Error Response:**
```json
{
    "status": "error",
    "code": 400,
    "message": "Error message",
    "errors": { ... }
}
```

---

## Authentication

### POST /api/auth/login

Authenticates a user and returns a Sanctum token.

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | User's email address (must be valid email format) |
| password | string | Yes | User's password (min: 8 characters) |

**Example Request:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Login successful",
    "data": {
        "token": "1|abc123xyz..."
    }
}
```

- **401 Unauthorized:**
```json
{
    "status": "error",
    "code": 401,
    "message": "Invalid credentials"
}
```

- **422 Unprocessable Entity:**
```json
{
    "status": "error",
    "code": 422,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

---

### POST /api/auth/register

Registers a new user account.

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | User's full name (max: 255 characters) |
| email | string | Yes | User's email address (must be unique) |
| password | string | Yes | User's password (min: 8 characters) |
| pin | string | No | User's PIN (min: 6 characters) |
| role_id | integer | Yes | ID of the user's role (must exist in roles table) |

**Example Request:**
```json
{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "pin": "123456",
    "role_id": 1
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "User registered successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

- **422 Unprocessable Entity:**
```json
{
    "status": "error",
    "code": 422,
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### POST /api/auth/logout

Logs out the authenticated user by invalidating their token.

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Logout successful"
}
```

- **401 Unauthorized:**
```json
{
    "status": "error",
    "code": 401,
    "message": "Unauthenticated."
}
```

---

### GET /api/auth/me

Retrieves the authenticated user's profile data.

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "User data retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "email_verified_at": null,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

- **401 Unauthorized:**
```json
{
    "status": "error",
    "code": 401,
    "message": "Unauthenticated."
}
```

---

## Devices

All device endpoints require authentication.

### GET /api/devices

Retrieves a paginated list of devices.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serial-number | string | Filter by exact serial number |
| brand | string | Filter by device brand |
| model | string | Filter by device model (partial match) |
| bad_asset | boolean | Filter by bad asset status (`true`/`false` or `1`/`0`) |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "SN123456789",
            "bad_asset": false,
            "device_model": {
                "id": 1,
                "brand": "Apple",
                "model": "MacBook Pro 16-inch"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost/api/devices",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

---

### GET /api/devices/{id}

Retrieves a specific device by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "device_model_id": 1,
        "serial_number": "SN123456789",
        "bad_asset": false,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z",
        "device_model": {
            "id": 1,
            "brand": "Apple",
            "model": "MacBook Pro 16-inch"
        }
    }
}
```

- **404 Not Found:**
```json
{
    "status": "error",
    "code": 404,
    "message": "Device not found"
}
```

---

### POST /api/devices

Creates a new device.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_model_id | integer | Yes | ID of the device model (must exist in device_models table) |
| serial_number | string | Yes | Device serial number (must be unique) |
| bad_asset | boolean | No | Mark device as bad asset |

**Example Request:**
```json
{
    "device_model_id": 1,
    "serial_number": "SN123456789",
    "bad_asset": false
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "",
    "data": {
        "id": 1,
        "device_model_id": 1,
        "serial_number": "SN123456789",
        "bad_asset": false,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

- **422 Unprocessable Entity:**
```json
{
    "status": "error",
    "code": 422,
    "message": "The given data was invalid.",
    "errors": {
        "serial_number": ["The serial number has already been taken."]
    }
}
```

---

### PUT /api/devices/{id}

Updates an existing device.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_model_id | integer | No | ID of the device model |
| serial_number | string | No | Device serial number (must be unique) |
| bad_asset | boolean | No | Mark device as bad asset |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "device_model_id": 1,
        "serial_number": "SN987654321",
        "bad_asset": true,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T13:00:00.000000Z"
    }
}
```

---

### PATCH /api/devices/{id}

Partially updates an existing device.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_model_id | integer | No | ID of the device model |
| serial_number | string | No | Device serial number (must be unique) |
| bad_asset | boolean | No | Mark device as bad asset |

**Responses:**

- **200 OK:** Same as PUT response

---

### DELETE /api/devices/{id}

Deletes a device.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": ""
}
```

---

## Device Types

All device type endpoints require authentication.

### GET /api/device-type

Retrieves a paginated list of device types.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter by name |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Type Found",
    "data": [
        {
            "id": 1,
            "name": "Laptop",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost/api/device-type",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

---

### GET /api/device-type/{id}

Retrieves a specific device type by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device type ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Type Found",
    "data": {
        "id": 1,
        "name": "Laptop",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

- **404 Not Found:**
```json
{
    "status": "error",
    "code": 404,
    "message": "Device Type not found"
}
```

---

### POST /api/device-type

Creates a new device type.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Device type name (must be unique) |

**Example Request:**
```json
{
    "name": "Laptop"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Device Type Created",
    "data": {
        "id": 1,
        "name": "Laptop"
    }
}
```

---

### PUT /api/device-type/{id}

Updates an existing device type.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device type ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Device type name (must be unique) |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Type Updated",
    "data": {
        "id": 1,
        "name": "Desktop Computer"
    }
}
```

---

### DELETE /api/device-type/{id}

Deletes a device type.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device type ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Type Deleted"
}
```

---

## Device Models

All device model endpoints require authentication.

### GET /api/device-model

Retrieves a paginated list of device models.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| keyword | string | Search term to filter by brand or model |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Model Found",
    "data": [
        {
            "id": 1,
            "device_type_id": 1,
            "brand": "Apple",
            "model": "MacBook Pro 16-inch",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost/api/device-model",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

---

### GET /api/device-model/{id}

Retrieves a specific device model by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device model ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Model Found",
    "data": {
        "id": 1,
        "device_type_id": 1,
        "brand": "Apple",
        "model": "MacBook Pro 16-inch",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

- **404 Not Found:**
```json
{
    "status": "error",
    "code": 404,
    "message": "Device Model not found"
}
```

---

### POST /api/device-model

Creates a new device model.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_type_id | integer | Yes | ID of the device type |
| brand | string | Yes | Brand name |
| model | string | Yes | Model name |

**Example Request:**
```json
{
    "device_type_id": 1,
    "brand": "Apple",
    "model": "MacBook Pro 16-inch"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Device Model Create Successfully",
    "data": {
        "id": 1,
        "device_type_id": 1,
        "brand": "Apple",
        "model": "MacBook Pro 16-inch"
    }
}
```

---

### PUT /api/device-model/{id}

Updates an existing device model.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device model ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_type_id | integer | No | ID of the device type |
| brand | string | No | Brand name |
| model | string | No | Model name |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Model Update Successfully",
    "data": {
        "id": 1,
        "device_type_id": 1,
        "brand": "Apple",
        "model": "MacBook Pro 16-inch M1"
    }
}
```

---

### PATCH /api/device-model/{id}

Partially updates an existing device model.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device model ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| device_type_id | integer | No | ID of the device type |
| brand | string | No | Brand name |
| model | string | No | Model name |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Model Patch Successfully",
    "data": {
        "id": 1,
        "device_type_id": 1,
        "brand": "Apple",
        "model": "MacBook Pro 16-inch M1"
    }
}
```

---

### DELETE /api/device-model/{id}

Deletes a device model.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The device model ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Device Model Delete Successfully"
}
```

---

## Service Requests

All service request endpoints require authentication.

### GET /api/service-requests

Retrieves a paginated list of service requests.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter requests |
| status_id | integer | Filter by status ID |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Success",
    "data": [
        {
            "id": 1,
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2026-01-22",
            "estimated_date": "2026-01-25",
            "status_id": 1,
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z",
            "details": [...],
            "status": {...},
            "admin": {...},
            "user": {...}
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost/api/service-requests",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

---

### GET /api/service-requests/stats

Retrieves statistics about service requests.

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "total": 100,
        "pending": 25,
        "in_progress": 30,
        "completed": 45
    }
}
```

---

### GET /api/service-requests/{id}

Retrieves a specific service request by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "admin_id": 1,
        "user_id": 2,
        "service_type_id": 1,
        "request_date": "2026-01-22",
        "estimated_date": "2026-01-25",
        "status_id": 1,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z",
        "details": [
            {
                "id": 1,
                "device_id": 1,
                "complaint": "Screen flickering issue",
                "complaint_images": [...]
            }
        ],
        "status": {...},
        "admin": {...},
        "user": {...},
        "costs": [...],
        "locations": [...],
        "approvals": [...]
    }
}
```

---

### POST /api/service-requests

Creates a new service request.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| admin_id | integer | Yes | ID of the admin handling the request |
| user_id | integer | No | ID of the user submitting the request |
| service_type_id | integer | No | ID of the service type |
| request_date | date | Yes | Date of the request (YYYY-MM-DD) |
| status_id | integer | Yes | Initial status ID |
| details | array | Yes | Array of service request details |
| details.*.device_id | integer | Yes | ID of the device |
| details.*.complaint | string | Yes | Description of the issue |
| details.*.complaint_images | array | No | Array of image files (jpeg, png, jpg, gif, svg; max 2MB each) |

**Example Request:**
```json
{
    "admin_id": 1,
    "user_id": 2,
    "service_type_id": 1,
    "request_date": "2026-01-22",
    "status_id": 1,
    "details": [
        {
            "device_id": 1,
            "complaint": "Screen flickering issue"
        }
    ]
}
```

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "admin_id": 1,
        "user_id": 2,
        "service_type_id": 1,
        "request_date": "2026-01-22",
        "status_id": 1,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

---

### PUT /api/service-requests/{id}

Updates an existing service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| admin_id | integer | No | ID of the admin |
| user_id | integer | No | ID of the user |
| service_type_id | integer | No | ID of the service type |
| request_date | date | No | Date of the request |
| estimated_date | date | No | Estimated completion date |
| status_id | integer | No | Status ID |
| details | array | No | Array of service request details |
| details.*.id | integer | No | ID of existing detail to update |
| details.*.device_id | integer | No | ID of the device |
| details.*.complaint | string | No | Description of the issue |
| details.*.complaint_images | array | No | Array of image files |
| log_notes | string | No | Notes for the audit log |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "admin_id": 1,
        "user_id": 2,
        "service_type_id": 1,
        "request_date": "2026-01-22",
        "estimated_date": "2026-01-25",
        "status_id": 2,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T14:00:00.000000Z"
    }
}
```

---

### DELETE /api/service-requests/{id}

Deletes a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": ""
}
```

---

### GET /api/service-requests/{id}/allowed-transitions

Retrieves the allowed status transitions for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 2,
            "name": "In Progress",
            "code": "in_progress"
        },
        {
            "id": 3,
            "name": "Cancelled",
            "code": "cancelled"
        }
    ]
}
```

---

### GET /api/service-requests/{id}/download-invoice

Downloads the invoice PDF for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Responses:**

- **200 OK:** Returns PDF file download

---

## Service Request Costs

Endpoints for managing costs associated with service requests.

### GET /api/service-requests/{serviceRequestId}/costs

Retrieves all costs for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "service_request_id": 1,
            "cost_type_id": 1,
            "amount": 150000,
            "description": "Spare part replacement",
            "image_path": "service_costs/receipt-001.pdf",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z",
            "cost_type": {
                "id": 1,
                "name": "Parts",
                "code": "PARTS"
            }
        }
    ]
}
```

---

### POST /api/service-requests/{serviceRequestId}/costs

Adds a cost to a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| cost_type_id | integer | Yes | ID of the cost type |
| amount | numeric | Yes | Cost amount (min: 0) |
| description | string | No | Description of the cost |
| image | file | No | Receipt attachment (`jpg`, `jpeg`, `png`, `gif`, `svg`, `pdf`, max 10MB) |

**Example Request:**
```json
{
    "cost_type_id": 1,
    "amount": 150000,
    "description": "Screen replacement part"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Cost added successfully",
    "data": {
        "id": 1,
        "service_request_id": 1,
        "cost_type_id": 1,
        "amount": 150000,
        "description": "Screen replacement part",
        "image_path": "service_costs/receipt-001.jpg"
    }
}
```

---

### PUT /api/service-requests/{serviceRequestId}/costs/{costId}

Updates a cost entry.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| costId | integer | The cost ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| cost_type_id | integer | No | ID of the cost type |
| amount | numeric | No | Cost amount (min: 0) |
| description | string | No | Description of the cost |
| image | file | No | Receipt attachment (`jpg`, `jpeg`, `png`, `gif`, `svg`, `pdf`, max 10MB) |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Cost updated successfully",
    "data": {
        "id": 1,
        "service_request_id": 1,
        "cost_type_id": 1,
        "amount": 175000,
        "description": "Screen replacement part (updated)",
        "image_path": "service_costs/receipt-002.pdf"
    }
}
```

---

### DELETE /api/service-requests/{serviceRequestId}/costs/{costId}

Removes a cost from a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| costId | integer | The cost ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Cost removed successfully"
}
```

---

### GET /api/service-requests/{serviceRequestId}/costs/{costId}/attachment

Downloads or previews the cost receipt attachment.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| costId | integer | The cost ID |

**Responses:**

- **200 OK:** Returns the file (image or PDF)
- **404 Not Found:** Attachment not found

---

## Service Request Locations

Endpoints for managing service locations (internal or external vendors).

### GET /api/service-requests/{serviceRequestId}/locations

Retrieves all locations for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service locations retrieved successfully",
    "data": [
        {
            "id": 1,
            "service_request_id": 1,
            "location_type": "external",
            "vendor_id": 1,
            "is_active": true,
            "address": "123 Main Street",
            "city": "Jakarta",
            "province": "DKI Jakarta",
            "postal_code": "12345",
            "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
            "vendor": {
                "id": 1,
                "name": "Tech Repair Co"
            }
        }
    ]
}
```

---

### GET /api/service-requests/{serviceRequestId}/locations/{locationId}

Retrieves a specific location.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| locationId | integer | The location ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service location retrieved successfully",
    "data": {
        "id": 1,
        "service_request_id": 1,
        "location_type": "external",
        "vendor_id": 1,
        "is_active": true,
        "address": "123 Main Street",
        "city": "Jakarta",
        "province": "DKI Jakarta",
        "postal_code": "12345",
        "maps_url": "https://maps.google.com/?q=-6.2088,106.8456"
    }
}
```

---

### POST /api/service-requests/{serviceRequestId}/locations

Creates or updates the active location for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| location_type | string | Yes | Either "internal" or "external" |
| vendor_id | integer | Conditional | Required if location_type is "external" |
| is_active | boolean | Yes | Whether this location is active |
| address | string | Conditional | Required if location_type is "external" |
| city | string | Conditional | Required if location_type is "external" |
| province | string | Conditional | Required if location_type is "external" |
| postal_code | string | Conditional | Required if location_type is "external" |
| maps_url | string (URL) | Conditional | Required if location_type is "external" |

**Example Request (External):**
```json
{
    "location_type": "external",
    "vendor_id": 1,
    "is_active": true,
    "address": "123 Main Street",
    "city": "Jakarta",
    "province": "DKI Jakarta",
    "postal_code": "12345",
    "maps_url": "https://maps.google.com/?q=-6.2088,106.8456"
}
```

**Example Request (Internal):**
```json
{
    "location_type": "internal",
    "is_active": true
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Service location set successfully",
    "data": { ... }
}
```

- **200 OK (if updating existing):**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service location updated successfully",
    "data": { ... }
}
```

---

### PUT /api/service-requests/{serviceRequestId}/locations/{locationId}

Updates an existing location.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| locationId | integer | The location ID |

**Request Body:** Same as POST

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service location updated successfully",
    "data": { ... }
}
```

- **400 Bad Request (if location doesn't belong to service request):**
```json
{
    "status": "error",
    "code": 400,
    "message": "Location does not belong to this service request"
}
```

---

### DELETE /api/service-requests/{serviceRequestId}/locations/{locationId}

Deletes a location.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| locationId | integer | The location ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service location deleted successfully"
}
```

---

## Service Request Approvals

Endpoints for managing approvals for service requests.

### GET /api/service-requests/{serviceRequestId}/approver

Retrieves the approvers for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Approvers retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Manager User",
            "email": "manager@example.com"
        }
    ]
}
```

---

### GET /api/service-requests/{serviceRequestId}/approvals

Retrieves all approvals for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service request approvals retrieved successfully",
    "data": [
        {
            "id": 1,
            "service_request_id": 1,
            "approver_id": 1,
            "status": "pending",
            "approved_at": null,
            "notes": null,
            "approver": {
                "id": 1,
                "name": "Manager User"
            }
        }
    ]
}
```

---

### POST /api/service-requests/{serviceRequestId}/approvals

Creates approval requests for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| approver_ids | array | Yes | Array of user IDs to be approvers |
| approver_ids.* | integer | Yes | User ID (must exist in users table) |

**Example Request:**
```json
{
    "approver_ids": [1, 2, 3]
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Service request approval created successfully",
    "data": [ ... ]
}
```

---

### PUT /api/service-requests/{serviceRequestId}/approvals

Updates the approvers for a service request (replaces existing approvers).

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| approvers | array | Yes | Array of user IDs |
| approvers.* | integer | Yes | User ID (must exist in users table) |

**Example Request:**
```json
{
    "approvers": [1, 4, 5]
}
```

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service request approval updated successfully",
    "data": [ ... ]
}
```

---

### DELETE /api/service-requests/{serviceRequestId}/approvals/{approvalId}

Deletes a specific approval.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |
| approvalId | integer | The approval ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Service request approval deleted successfully"
}
```

---

### POST /api/service-requests/approved/{approvalId}

Approves a vendor request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| approvalId | integer | The approval ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Vendor request approved successfully",
    "data": { ... }
}
```

---

### POST /api/service-requests/approved-by-admin/{serviceRequestId}

Approves a request by admin.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Request approved successfully",
    "data": { ... }
}
```

---

### POST /api/service-requests/rejected/{approvalId}

Rejects a vendor request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| approvalId | integer | The approval ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Vendor request rejected successfully",
    "data": { ... }
}
```

---

## Service Request Cancellation

Endpoints for managing service request cancellations.

### GET /api/service-requests/{serviceRequestId}/cancellation

Retrieves the cancellation details for a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "service_request_id": 1,
        "reason": "Customer requested cancellation",
        "canceled_by": 1,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z",
        "canceler": {
            "id": 1,
            "name": "Admin User"
        }
    }
}
```

---

### POST /api/service-requests/{serviceRequestId}/cancellation

Cancels a service request.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| reason | string | Yes | Reason for cancellation |

**Example Request:**
```json
{
    "reason": "Customer no longer needs the service"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Service request cancelled successfully",
    "data": {
        "id": 1,
        "service_request_id": 1,
        "reason": "Customer no longer needs the service",
        "canceled_by": 1
    }
}
```

---

### PUT /api/service-requests/{serviceRequestId}/cancellation

Updates the cancellation reason.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| serviceRequestId | integer | The service request ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| reason | string | Yes | Updated reason for cancellation |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": { ... }
}
```

---

## Departments

All department endpoints require authentication.

### GET /api/departments

Retrieves a paginated list of departments.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter departments |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "IT Department",
            "code": "IT",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "http://localhost/api/departments",
        "per_page": 15,
        "to": 1,
        "total": 1
    }
}
```

---

### GET /api/departments/{id}

Retrieves a specific department by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The department ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "name": "IT Department",
        "code": "IT",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

---

### POST /api/departments

Creates a new department.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Department name (max: 255, must be unique) |
| code | string | Yes | Department code (max: 255, must be unique) |

**Example Request:**
```json
{
    "name": "IT Department",
    "code": "IT"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "",
    "data": {
        "id": 1,
        "name": "IT Department",
        "code": "IT"
    }
}
```

---

### PUT /api/departments/{id}

Updates an existing department.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The department ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | No | Department name (max: 255) |
| code | string | No | Department code (max: 255) |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "name": "Information Technology",
        "code": "IT"
    }
}
```

---

### DELETE /api/departments/{id}

Deletes a department.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The department ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": ""
}
```

---

## Users

All user endpoints require authentication.

### GET /api/users

Retrieves a paginated list of users.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter users |
| role_id | integer | Filter by role ID |
| department_id | integer | Filter by department ID |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john.doe@example.com",
            "role_id": 1,
            "department_id": 1,
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z",
            "role": { ... },
            "department": { ... }
        }
    ],
    "meta": { ... }
}
```

---

### GET /api/users/{id}

Retrieves a specific user by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The user ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john.doe@example.com",
        "role_id": 1,
        "department_id": 1,
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

---

### POST /api/users

Creates a new user.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | User's full name (max: 255) |
| email | string | Yes | User's email (must be unique) |
| password | string | Yes | User's password (min: 8 characters) |
| role_id | integer | Yes | Role ID (must exist in roles table) |
| department_id | integer | Yes | Department ID (must exist in departments table) |

**Example Request:**
```json
{
    "name": "Jane Doe",
    "email": "jane.doe@example.com",
    "password": "password123",
    "role_id": 2,
    "department_id": 1
}
```

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane.doe@example.com",
        "role_id": 2,
        "department_id": 1
    }
}
```

---

### PUT /api/users/{id}

Updates an existing user.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The user ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | No | User's full name (max: 255) |
| email | string | No | User's email (must be unique, except current user) |
| password | string | No | User's password (min: 8 characters) |
| role_id | integer | No | Role ID |
| department_id | integer | No | Department ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": { ... }
}
```

---

### DELETE /api/users/{id}

Deletes a user.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The user ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": ""
}
```

---

## Vendors

All vendor endpoints require authentication.

### GET /api/vendors

Retrieves a list of vendors.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter vendors |

**Responses:**

- **200 OK:**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Tech Repair Co",
            "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
            "description": "Professional IT repair services",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    ]
}
```

---

### GET /api/vendors/{id}

Retrieves a specific vendor by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The vendor ID |

**Responses:**

- **200 OK:**
```json
{
    "id": 1,
    "name": "Tech Repair Co",
    "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
    "description": "Professional IT repair services",
    "created_at": "2026-01-22T12:00:00.000000Z",
    "updated_at": "2026-01-22T12:00:00.000000Z"
}
```

---

### POST /api/vendors

Creates a new vendor.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Vendor name (max: 255) |
| maps_url | string (URL) | Yes | Google Maps URL for vendor location |
| description | string | Yes | Vendor description |

**Example Request:**
```json
{
    "name": "Tech Repair Co",
    "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
    "description": "Professional IT repair services"
}
```

**Responses:**

- **201 Created:**
```json
{
    "id": 1,
    "name": "Tech Repair Co",
    "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
    "description": "Professional IT repair services"
}
```

---

### PUT /api/vendors/{id}

Updates an existing vendor.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The vendor ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | No | Vendor name (max: 255) |
| maps_url | string (URL) | No | Google Maps URL |
| description | string | No | Vendor description |

**Responses:**

- **200 OK:**
```json
{
    "id": 1,
    "name": "Tech Repair Services",
    "maps_url": "https://maps.google.com/?q=-6.2088,106.8456",
    "description": "Updated description"
}
```

---

### DELETE /api/vendors/{id}

Deletes a vendor.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The vendor ID |

**Responses:**

- **204 No Content:** (empty response)

---

## Cost Types

All cost type endpoints require authentication.

### GET /api/cost-types

Retrieves a list of cost types.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter cost types |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Cost types retrieved successfully",
    "data": [
        {
            "id": 1,
            "code": "PARTS",
            "name": "Parts & Components",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        },
        {
            "id": 2,
            "code": "LABOR",
            "name": "Labor Cost",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    ]
}
```

---

### GET /api/cost-types/{id}

Retrieves a specific cost type by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The cost type ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Cost type retrieved successfully",
    "data": {
        "id": 1,
        "code": "PARTS",
        "name": "Parts & Components",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z"
    }
}
```

---

### POST /api/cost-types

Creates a new cost type.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| code | string | Yes | Cost type code (max: 255) |
| name | string | Yes | Cost type name (max: 255) |

**Example Request:**
```json
{
    "code": "SHIPPING",
    "name": "Shipping & Handling"
}
```

**Responses:**

- **201 Created:**
```json
{
    "status": "success",
    "code": 201,
    "message": "Cost type created successfully",
    "data": {
        "id": 3,
        "code": "SHIPPING",
        "name": "Shipping & Handling"
    }
}
```

---

### PUT /api/cost-types/{id}

Updates an existing cost type.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The cost type ID |

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| code | string | Yes | Cost type code (max: 255) |
| name | string | Yes | Cost type name (max: 255) |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Cost type updated successfully",
    "data": {
        "id": 1,
        "code": "PARTS",
        "name": "Parts & Accessories"
    }
}
```

---

### DELETE /api/cost-types/{id}

Deletes a cost type.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The cost type ID |

**Responses:**

- **204 No Content:**
```json
{
    "status": "success",
    "code": 204,
    "message": "Cost type deleted successfully"
}
```

---

## Invoices

All invoice endpoints require authentication.

### GET /api/invoices

Retrieves a paginated list of invoices.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| search | string | Search term to filter invoices |
| page | integer | Page number for pagination |
| per_page | integer | Number of items per page |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "Success",
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-2026-0001",
            "service_request_id": 1,
            "total_amount": 500000,
            "status": "paid",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z",
            "service_request": { ... }
        }
    ],
    "meta": { ... }
}
```

---

### GET /api/invoices/{id}

Retrieves a specific invoice by ID.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The invoice ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "invoice_number": "INV-2026-0001",
        "service_request_id": 1,
        "total_amount": 500000,
        "status": "paid",
        "created_at": "2026-01-22T12:00:00.000000Z",
        "updated_at": "2026-01-22T12:00:00.000000Z",
        "service_request": { ... },
        "items": [ ... ]
    }
}
```

---

### GET /api/invoices/{id}/print

Retrieves invoice data formatted for printing.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The invoice ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "invoice": { ... },
        "company": { ... },
        "items": [ ... ],
        "totals": { ... }
    }
}
```

---

## Inbox Approvals

Endpoints for managing the approval inbox for the authenticated user.

### GET /api/inbox-approvals/{statusId}

Retrieves inbox approvals filtered by status.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| statusId | integer | Filter by approval status ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "service_request_id": 1,
            "approver_id": 1,
            "status": "pending",
            "is_read": false,
            "created_at": "2026-01-22T12:00:00.000000Z",
            "service_request": {
                "id": 1,
                "request_date": "2026-01-22",
                "status": { ... }
            }
        }
    ]
}
```

---

### PUT /api/inbox-approvals/{id}/read

Marks an inbox approval as read.

**Authentication:** Required

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The approval inbox ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": {
        "id": 1,
        "is_read": true,
        "updated_at": "2026-01-22T14:00:00.000000Z"
    }
}
```

---

## Reference Data

Endpoints for retrieving reference/lookup data used throughout the application.

### GET /api/references/service-types

Retrieves all service types.

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "Repair"
        },
        {
            "id": 2,
            "name": "Maintenance"
        },
        {
            "id": 3,
            "name": "Installation"
        }
    ]
}
```

---

### GET /api/references/statuses

Retrieves all statuses, optionally filtered by entity type.

**Authentication:** Required

**Query Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| entity_type_id | integer | Filter by entity type ID |

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "Pending",
            "code": "pending",
            "entity_type_id": 1
        },
        {
            "id": 2,
            "name": "In Progress",
            "code": "in_progress",
            "entity_type_id": 1
        },
        {
            "id": 3,
            "name": "Completed",
            "code": "completed",
            "entity_type_id": 1
        }
    ]
}
```

---

### GET /api/references/vendors

Retrieves all vendors (minimal data).

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "Tech Repair Co",
            "description": "Professional IT repair services"
        }
    ]
}
```

---

### GET /api/references/roles

Retrieves all roles.

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "Admin"
        },
        {
            "id": 2,
            "name": "Manager"
        },
        {
            "id": 3,
            "name": "Staff"
        }
    ]
}
```

---

### GET /api/references/departments

Retrieves all departments (minimal data).

**Authentication:** Required

**Responses:**

- **200 OK:**
```json
{
    "status": "success",
    "code": 200,
    "message": "",
    "data": [
        {
            "id": 1,
            "name": "IT Department",
            "code": "IT"
        },
        {
            "id": 2,
            "name": "Human Resources",
            "code": "HR"
        }
    ]
}
```

---

## Export Invoice

### GET /api/export-invoice/{id}

Downloads an invoice as a PDF file.

**URL Parameters:**

| Field | Type | Description |
|-------|------|-------------|
| id | integer | The service request ID |

**Responses:**

- **200 OK:** Returns PDF file download

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 204 | No content (successful deletion) |
| 400 | Bad request |
| 401 | Unauthenticated |
| 403 | Forbidden |
| 404 | Not found |
| 422 | Validation error |
| 500 | Server error |

---

## Notes

1. All dates should be in the format `YYYY-MM-DD`
2. All timestamps are returned in ISO 8601 format
3. Pagination is available on list endpoints via `page` and `per_page` query parameters
4. Most endpoints require Bearer token authentication
5. File uploads should use `multipart/form-data` content type
6. Image uploads are limited to 2MB per file and must be jpeg, png, jpg, gif, or svg format
