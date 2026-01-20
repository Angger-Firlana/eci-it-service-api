# API Documentation for ECI IT Service

This document provides an overview of the API endpoints, their expected request formats, and typical response structures for the ECI IT Service application.

---

## Authentication Endpoints

Base URL: `/api`

### 1. User Login

Authenticates a user and returns an access token.

-   **Endpoint:** `/login`
-   **Method:** `POST`
-   **Request:** `application/json`
    ```json
    {
        "email": "user@example.com",
        "password": "password"
    }
    ```
-   **Validation Rules:**
    *   `email`: `required`, `email`
    *   `password`: `required`, `min:8`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "user@example.com",
                "role": {
                    "id": 1,
                    "name": "Admin"
                },
                "created_at": "2024-01-01T12:00:00.000000Z",
                "updated_at": "2024-01-01T12:00:00.000000Z"
            },
            "token": "YOUR_ACCESS_TOKEN_HERE"
        },
        "message": "Login successful"
    }
    ```

-   **Error Response (401 Unauthorized - Invalid Credentials):**
    ```json
    {
        "success": false,
        "message": "Invalid credentials"
    }
    ```
-   **Error Response (422 Unprocessable Entity - Validation Errors):**
    ```json
    {
        "success": false,
        "message": "The given data was invalid.",
        "errors": {
            "email": [
                "The email field is required."
            ],
            "password": [
                "The password field must be at least 8 characters."
            ]
        }
    }
    ```

### 2. User Registration

Registers a new user in the system.

-   **Endpoint:** `/register`
-   **Method:** `POST`
-   **Request:** `application/json`
    ```json
    {
        "name": "Jane Doe",
        "email": "jane.doe@example.com",
        "password": "password123",
        "pin": "123456",
        "role_id": 2
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `max:255`
    *   `email`: `required`, `email`, `unique:users,email`
    *   `password`: `required`, `min:8`
    *   `pin`: `sometimes`, `min:6`
    *   `role_id`: `required`, `exists:roles,id`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "user": {
                "id": 2,
                "name": "Jane Doe",
                "email": "jane.doe@example.com",
                "role": {
                    "id": 2,
                    "name": "User"
                },
                "created_at": "2024-01-01T12:05:00.000000Z",
                "updated_at": "2024-01-01T12:05:00.000000Z"
            },
            "token": "YOUR_NEW_ACCESS_TOKEN_HERE"
        },
        "message": "Registration successful"
    }
    ```
-   **Error Response (422 Unprocessable Entity - Validation Errors):**
    ```json
    {
        "success": false,
        "message": "The given data was invalid.",
        "errors": {
            "email": [
                "The email has already been taken."
            ]
        }
    }
    ```

### 3. User Logout

Logs out the currently authenticated user by revoking their access tokens.

-   **Endpoint:** `/logout`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** (None)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Logout successful"
    }
    ```
-   **Error Response (500 Internal Server Error - Logout Failed):**
    ```json
    {
        "success": false,
        "message": "Logout failed"
    }
    ```
-   **Error Response (401 Unauthorized - Unauthenticated):**
    ```json
    {
        "message": "Unauthenticated."
    }
    ```

### 4. Get Authenticated User Data

Retrieves the data of the currently authenticated user.

-   **Endpoint:** `/me`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Request:** (None)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "role": {
                "id": 1,
                "name": "Admin"
            },
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        },
        "message": "User found"
    }
    ```
-   **Error Response (404 Not Found - User Not Found):**
    ```json
    {
        "success": false,
        "message": "User not found"
    }
    ```
-   **Error Response (401 Unauthorized - Unauthenticated):**
    ```json
    {
        "message": "Unauthenticated."
    }
    ```

---

## Device Model Endpoints

Base URL: `/api/device-models`

### 1. Get All Device Models

Retrieves a list of all device models, optionally filtered by keyword.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Query Parameters:**
    *   `keyword` (optional): A string to filter device models by their `model` name.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "device_type_id": 1,
                "brand": "Apple",
                "model": "iPhone 13 Pro",
                "created_at": "2024-01-01T12:00:00.000000Z",
                "updated_at": "2024-01-01T12:00:00.000000Z"
            },
            {
                "id": 2,
                "device_type_id": 1,
                "brand": "Samsung",
                "model": "Galaxy S21",
                "created_at": "2024-01-01T12:05:00.000000Z",
                "updated_at": "2024-01-01T12:05:00.000000Z"
            }
        ],
        "message": "Device Model Found"
    }
    ```

### 2. Get Device Model by ID

Retrieves a single device model by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device model.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_type_id": 1,
            "brand": "Apple",
            "model": "iPhone 13 Pro",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        },
        "message": "Device Model Found"
    }
    ```
-   **Error Response (404 Not Found - Device Model Not Found):**
    ```json
    {
        "success": false,
        "message": "No query results for model [App\Models\DeviceModel] 100"
    }
    ```
    (Note: This is a Laravel default error message for `findOrFail` when not found)

### 3. Create New Device Model

Creates a new device model.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "device_type_id": 1,
        "brand": "Google",
        "model": "Pixel 7"
    }
    ```
-   **Validation Rules:**
    *   `device_type_id`: `required`, `exists:device_types,id`
    *   `brand`: `required`, `string`
    *   `model`: `required`, `string`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "device_type_id": 1,
            "brand": "Google",
            "model": "Pixel 7",
            "updated_at": "2024-01-01T12:10:00.000000Z",
            "created_at": "2024-01-01T12:10:00.000000Z",
            "id": 3
        },
        "message": "Device Model Create Successfully"
    }
    ```

### 4. Update Device Model (PUT)

Completely replaces an existing device model by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device model to update.
-   **Request:** `application/json`
    ```json
    {
        "device_type_id": 2,
        "brand": "Apple",
        "model": "iPhone 14 Pro Max"
    }
    ```
-   **Validation Rules:**
    *   `device_type_id`: `sometimes`, `exists:device_types,id`
    *   `brand`: `sometimes`, `string`
    *   `model`: `sometimes`, `string`
    *(Note: Although rules are 'sometimes', PUT usually implies all fields are sent for full replacement. The service methods handle partial updates)*

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_type_id": 2,
            "brand": "Apple",
            "model": "iPhone 14 Pro Max",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:15:00.000000Z"
        },
        "message": "Device Model Update Successfully"
    }
    ```

### 5. Update Device Model (PATCH)

Partially updates an existing device model by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PATCH`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device model to update.
-   **Request:** `application/json`
    ```json
    {
        "model": "iPhone 13 Pro Max"
    }
    ```
-   **Validation Rules:**
    *   `device_type_id`: `sometimes`, `exists:device_types,id`
    *   `brand`: `sometimes`, `string`
    *   `model`: `sometimes`, `string`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_type_id": 2,
            "brand": "Apple",
            "model": "iPhone 13 Pro Max",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:20:00.000000Z"
        },
        "message": "Device Model Patch Successfully"
    }
    ```

### 6. Delete Device Model

Deletes a device model by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device model to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Device Model Delete Successfully"
    }
    ```
-   **Error Response (404 Not Found - Device Model Not Found):**
    ```json
    {
        "success": false,
        "message": "No query results for model [App\Models\DeviceModel] 100"
    }
    ```

---

## Device Type Endpoints

Base URL: `/api/device-types`

### 1. Get All Device Types

Retrieves a list of all device types, optionally filtered by search keyword.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Query Parameters:**
    *   `search` (optional): A string to filter device types by their `name`.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "name": "Laptop",
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-01-01T10:00:00.000000Z"
            },
            {
                "id": 2,
                "name": "Monitor",
                "created_at": "2024-01-01T10:05:00.000000Z",
                "updated_at": "2024-01-01T10:05:00.000000Z"
            }
        ],
        "message": "Device Type Found"
    }
    ```

### 2. Get Device Type by ID

Retrieves a single device type by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device type.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "Laptop",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        },
        "message": "Device Type Found"
    }
    ```
-   **Error Response (404 Not Found - Device Type Not Found):**
    ```json
    {
        "success": false,
        "message": "No query results for model [App\\Models\\DeviceType] 100"
    }
    ```

### 3. Create New Device Type

Creates a new device type.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "name": "Keyboard"
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `unique:device_types,name`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "name": "Keyboard",
            "updated_at": "2024-01-01T10:10:00.000000Z",
            "created_at": "2024-01-01T10:10:00.000000Z",
            "id": 3
        },
        "message": "Device Type Created"
    }
    ```

### 4. Update Device Type

Updates an existing device type by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device type to update.
-   **Request:** `application/json`
    ```json
    {
        "name": "Mouse"
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `unique:device_types,name`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 3,
            "name": "Mouse",
            "created_at": "2024-01-01T10:10:00.000000Z",
            "updated_at": "2024-01-01T10:15:00.000000Z"
        },
        "message": "Device Type Updated"
    }
    ```

### 5. Delete Device Type

Deletes a device type by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device type to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Device Type Deleted"
    }
    ```
-   **Error Response (404 Not Found - Device Type Not Found):**
    ```json
    {
        "success": false,
        "message": "No query results for model [App\\Models\\DeviceType] 100"
    }
    ```

//test
