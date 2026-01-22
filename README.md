# API Documentation

This document provides documentation for all the API endpoints in the application.

## Authentication

### POST /auth/login

Logs in a user and returns a sanctum token.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| email | string | The user's email address. (Required) |
| password | string | The user's password. (min: 8 characters) (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Login successful",
        "data": {
            "token": "your-auth-token"
        }
    }
    ```
*   **401 Unauthorized:**
    ```json
    {
        "status": "error",
        "code": 401,
        "message": "Invalid credentials"
    }
    ```
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "email": [
                "The email field is required."
            ],
            "password": [
                "The password field is required."
            ]
        }
    }
    ```

### POST /auth/register

Registers a new user.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| name | string | The user's name. (Required) |
| email | string | The user's email address. (Required, must be unique) |
| password | string | The user's password. (min: 8 characters) (Required) |
| pin | string | The user's PIN. (min: 6 characters) (Optional) |
| role_id | integer | The ID of the user's role. (Required, must exist in roles table) |

**Responses:**

*   **201 Created:**
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
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "email": [
                "The email has already been taken."
            ]
        }
    }
    ```

### POST /auth/logout

Logs out the authenticated user.

**Authentication:** Requires a valid sanctum token.

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Logout successful"
    }
    ```
*   **401 Unauthorized:**
    ```json
    {
        "status": "error",
        "code": 401,
        "message": "Unauthenticated."
    }
    ```

### GET /auth/me

Gets the data of the currently authenticated user.

**Authentication:** Requires a valid sanctum token.

**Responses:**

*   **200 OK:**
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
*   **401 Unauthorized:**
    ```json
    {
        "status": "error",
        "code": 401,
        "message": "Unauthenticated."
    }
    ```
## Device Type

### GET /device-type

Retrieves a list of device types. Supports search and pagination.

**Query Parameters:**

| Field | Type | Description |
|---|---|---|
| search | string | A search term to filter device types by name. (Optional) |
| page | integer | The page number for pagination. (Optional) |
| per_page | integer | The number of items per page. (Optional) |

**Responses:**

*   **200 OK:**
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

### GET /device-type/{id}

Retrieves a specific device type by its ID.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device type. (Required) |

**Responses:**

*   **200 OK:**
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
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Type not found"
    }
    ```

### POST /device-type

Creates a new device type.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| name | string | The name of the device type. (Required, must be unique) |

**Responses:**

*   **201 Created:**
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
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "name": [
                "The name has already been taken."
            ]
        }
    }
    ```

### PUT /device-type/{id}

Updates an existing device type.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device type. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| name | string | The new name of the device type. (Required, must be unique) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Type Updated",
        "data": {
            "id": 1,
            "name": "New Laptop Name"
        }
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Type not found"
    }
    ```
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "name": [
                "The name has already been taken."
            ]
        }
    }
    ```

### DELETE /device-type/{id}

Deletes a device type.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device type. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Type Deleted"
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Type not found"
    }
    ```
## Device Model

### GET /device-model

Retrieves a list of device models. Supports search and pagination.

**Query Parameters:**

| Field | Type | Description |
|---|---|---|
| keyword | string | A search term to filter device models by brand or model. (Optional) |
| page | integer | The page number for pagination. (Optional) |
| per_page | integer | The number of items per page. (Optional) |

**Responses:**

*   **200 OK:**
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

### GET /device-model/{id}

Retrieves a specific device model by its ID.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device model. (Required) |

**Responses:**

*   **200 OK:**
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
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Model not found"
    }
    ```

### POST /device-model

Creates a new device model.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_type_id | integer | The ID of the device type. (Required) |
| brand | string | The brand of the device model. (Required) |
| model | string | The model of the device. (Required) |

**Responses:**

*   **201 Created:**
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
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "device_type_id": [
                "The selected device type id is invalid."
            ]
        }
    }
    ```

### PUT /device-model/{id}

Updates an existing device model.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device model. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_type_id | integer | The ID of the device type. (Optional) |
| brand | string | The brand of the device model. (Optional) |
| model | string | The model of the device. (Optional) |

**Responses:**

*   **200 OK:**
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
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Model not found"
    }
    ```

### PATCH /device-model/{id}

Partially updates an existing device model.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device model. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_type_id | integer | The ID of the device type. (Optional) |
| brand | string | The brand of the device model. (Optional) |
| model | string | The model of the device. (Optional) |

**Responses:**

*   **200 OK:**
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
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Model not found"
    }
    ```

### DELETE /device-model/{id}

Deletes a device model.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device model. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Model Delete Successfully"
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device Model not found"
    }
    ```
## Device

### GET /devices

Retrieves a list of devices. Supports search and pagination.

**Query Parameters:**

| Field | Type | Description |
|---|---|---|
| search | string | A search term to filter devices by serial number. (Optional) |
| page | integer | The page number for pagination. (Optional) |
| per_page | integer | The number of items per page. (Optional) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Found",
        "data": [
            {
                "id": 1,
                "device_model_id": 1,
                "serial_number": "C02G8426J1G5",
                "created_at": "2026-01-22T12:00:00.000000Z",
                "updated_at": "2026-01-22T12:00:00.000000Z"
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

### GET /devices/{id}

Retrieves a specific device by its ID.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Found",
        "data": {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "C02G8426J1G5",
            "created_at": "2026-01-22T12:00:00.000000Z",
            "updated_at": "2026-01-22T12:00:00.000000Z"
        }
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device not found"
    }
    ```

### POST /devices

Creates a new device.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_model_id | integer | The ID of the device model. (Required) |
| serial_number | string | The serial number of the device. (Required, must be unique) |

**Responses:**

*   **201 Created:**
    ```json
    {
        "status": "success",
        "code": 201,
        "message": "Device Created",
        "data": {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "C02G8426J1G5"
        }
    }
    ```
*   **422 Unprocessable Entity:**
    ```json
    {
        "status": "error",
        "code": 422,
        "message": "The given data was invalid.",
        "errors": {
            "serial_number": [
                "The serial number has already been taken."
            ]
        }
    }
    ```

### PUT /devices/{id}

Updates an existing device.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_model_id | integer | The ID of the device model. (Optional) |
| serial_number | string | The serial number of the device. (Optional, must be unique) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Updated",
        "data": {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "NEWSERIALNUMBER"
        }
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device not found"
    }
    ```

### PATCH /devices/{id}

Partially updates an existing device.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| device_model_id | integer | The ID of the device model. (Optional) |
| serial_number | string | The serial number of the device. (Optional, must be unique) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Updated",
        "data": {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "NEWSERIALNUMBER"
        }
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device not found"
    }
    ```

### DELETE /devices/{id}

Deletes a device.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the device. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Device Deleted"
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Device not found"
    }
    ```
## Service Request

### GET /service-requests

Retrieves a list of service requests.

**Query Parameters:**

| Field | Type | Description |
|---|---|---|
| search | string | Search by user name or admin name. (Optional) |
| status_id | integer | Filter by status ID. (Optional) |
| page | integer | The page number for pagination. (Optional) |
| per_page | integer | The number of items per page. (Optional) |

**Responses:**

*   **200 OK:**
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
                "status_id": 1,
                "details": [
                    {
                        "id": 1,
                        "device_id": 1,
                        "complaint": "Screen is broken"
                    }
                ]
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

### GET /service-requests/stats

Retrieves statistics about service requests.

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Success",
        "data": {
            "total": 100,
            "pending": 20,
            "in_progress": 30,
            "completed": 50
        }
    }
    ```

### GET /service-requests/{id}

Retrieves a specific service request by its ID.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the service request. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Success",
        "data": {
            "id": 1,
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2026-01-22",
            "status_id": 1,
            "details": [
                {
                    "id": 1,
                    "device_id": 1,
                    "complaint": "Screen is broken"
                }
            ]
        }
    }
    ```
*   **404 Not Found:**
    ```json
    {
        "status": "error",
        "code": 404,
        "message": "Service Request not found"
    }
    ```

### POST /service-requests

Creates a new service request.

**Request Body:**

| Field | Type | Description |
|---|---|---|
| admin_id | integer | The ID of the admin user. (Required) |
| user_id | integer | The ID of the user. (Optional) |
| service_type_id | integer | The ID of the service type. (Optional) |
| request_date | date | The date of the request. (Required) |
| status_id | integer | The ID of the status. (Required) |
| details | array | An array of service request details. (Required) |
| details.*.device_id | integer | The ID of the device. (Required) |
| details.*.complaint | string | The complaint description. (Required) |
| details.*.complaint_images | array | An array of complaint images. (Optional) |
| details.*.complaint_images.* | file | An image file. (Optional, max: 2MB, mimes: jpeg,png,jpg,gif,svg) |

**Responses:**

*   **201 Created:**
    ```json
    {
        "status": "success",
        "code": 201,
        "message": "Service Request Created",
        "data": {
            "id": 1,
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2026-01-22",
            "status_id": 1
        }
    }
    ```

### PUT /service-requests/{id}

Updates an existing service request.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the service request. (Required) |

**Request Body:**

| Field | Type | Description |
|---|---|---|
| admin_id | integer | The ID of the admin user. (Optional) |
| user_id | integer | The ID of the user. (Optional) |
| service_type_id | integer | The ID of the service type. (Optional) |
| request_date | date | The date of the request. (Optional) |
| estimated_date | date | The estimated date of completion. (Optional) |
| status_id | integer | The ID of the status. (Optional) |
| details | array | An array of service request details to update. (Optional) |
| details.*.id | integer | The ID of the service request detail to update. (Optional) |
| details.*.device_id | integer | The ID of the device. (Optional) |
| details.*.complaint | string | The complaint description. (Optional) |
| service_location | object | Service location details. (Optional) |
| service_costs | array | An array of service costs. (Optional) |
| service_cancellation | object | Service cancellation details. (Optional) |
| vendor_approvals | array | An array of vendor approvals. (Optional) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Service Request Updated"
    }
    ```

### DELETE /service-requests/{id}

Deletes a service request.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the service request. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Service Request Deleted"
    }
    ```

### GET /service-requests/{id}/allowed-transitions

Retrieves the allowed status transitions for a service request.

**URL Parameters:**

| Field | Type | Description |
|---|---|---|
| id | integer | The ID of the service request. (Required) |

**Responses:**

*   **200 OK:**
    ```json
    {
        "status": "success",
        "code": 200,
        "message": "Success",
        "data": [
            {
                "id": 2,
                "name": "In Progress"
            }
        ]
    }
    ```
