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
