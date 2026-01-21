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

---

## Device Endpoints

Base URL: `/api/devices`

### 1. Get All Devices

Retrieves a list of all devices.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "device_model_id": 1,
                "serial_number": "SN12345",
                "created_at": "2024-01-01T12:00:00.000000Z",
                "updated_at": "2024-01-01T12:00:00.000000Z"
            }
        ],
        "message": "Device Found"
    }
    ```

### 2. Get Device by ID

Retrieves a single device by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_model_id": 1,
            "serial_number": "SN12345",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:00:00.000000Z"
        },
        "message": "Device Found"
    }
    ```

### 3. Create New Device

Creates a new device.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "device_model_id": 1,
        "serial_number": "SN12345"
    }
    ```
-   **Validation Rules:**
    *   `device_model_id`: `required`, `exists:device_models,id`
    *   `serial_number`: `required`, `string`, `unique:devices,serial_number`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "device_model_id": 1,
            "serial_number": "SN12345",
            "updated_at": "2024-01-01T12:10:00.000000Z",
            "created_at": "2024-01-01T12:10:00.000000Z",
            "id": 3
        },
        "message": "Device Create Successfully"
    }
    ```

### 4. Update Device (PUT)

Completely replaces an existing device by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device to update.
-   **Request:** `application/json`
    ```json
    {
        "device_model_id": 2,
        "serial_number": "SN54321"
    }
    ```
-   **Validation Rules:**
    *   `device_model_id`: `sometimes`, `exists:device_models,id`
    *   `serial_number`: `sometimes`, `string`, `unique:devices,serial_number`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_model_id": 2,
            "serial_number": "SN54321",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:15:00.000000Z"
        },
        "message": "Device Update Successfully"
    }
    ```

### 5. Update Device (PATCH)

Partially updates an existing device by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PATCH`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device to update.
-   **Request:** `application/json`
    ```json
    {
        "serial_number": "SN54321B"
    }
    ```
-   **Validation Rules:**
    *   `device_model_id`: `sometimes`, `exists:device_models,id`
    *   `serial_number`: `sometimes`, `string`, `unique:devices,serial_number`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "device_model_id": 2,
            "serial_number": "SN54321B",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "updated_at": "2024-01-01T12:20:00.000000Z"
        },
        "message": "Device Patch Successfully"
    }
    ```

### 6. Delete Device

Deletes a device by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the device to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Device Delete Successfully"
    }
    ```

---

## Service Request Endpoints

Base URL: `/api/service-requests`

### 1. Get All Service Requests

Retrieves a list of all service requests.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "admin_id": 1,
                "user_id": 2,
                "service_type_id": 1,
                "request_date": "2024-01-20",
                "status_id": 1,
                "details": [
                    {
                        "id": 1,
                        "service_request_id": 1,
                        "device_id": 1,
                        "complaint": "Screen is broken"
                    }
                ]
            }
        ],
        "message": "Service Requests Found"
    }
    ```

### 2. Get Service Request Statistics

Retrieves statistics about service requests.

-   **Endpoint:** `/stats`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "total": 100,
            "pending": 20,
            "in_progress": 30,
            "completed": 50
        },
        "message": "Service Request Stats Found"
    }
    ```

### 3. Get Service Request by ID

Retrieves a single service request by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2024-01-20",
            "status_id": 1,
            "details": [
                {
                    "id": 1,
                    "service_request_id": 1,
                    "device_id": 1,
                    "complaint": "Screen is broken"
                }
            ]
        },
        "message": "Service Request Found"
    }
    ```

### 4. Create New Service Request

Creates a new service request.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "admin_id": 1,
        "user_id": 2,
        "service_type_id": 1,
        "request_date": "2024-01-20",
        "status_id": 1,
        "details": [
            {
                "device_id": 1,
                "complaint": "Screen is broken",
                "complaint_images": []
            }
        ]
    }
    ```
-   **Validation Rules:**
    *   `admin_id`: `required`, `exists:users,id`
    *   `user_id`: `sometimes`, `exists:users,id`
    *   `service_type_id`: `sometimes`, `exists:service_types,id`
    *   `request_date`: `required`, `date`
    *   `status_id`: `required`, `exists:statuses,id`
    *   `details`: `required`, `array`
    *   `details.*.device_id`: `required`, `exists:devices,id`
    *   `details.*.complaint`: `required`, `string`
    *   `details.*.complaint_images`: `sometimes`, `array`
    *   `details.*.complaint_images.*`: `sometimes`, `file`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`


-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2024-01-20",
            "status_id": 1,
            "id": 1,
            "details": [
                {
                    "service_request_id": 1,
                    "device_id": 1,
                    "complaint": "Screen is broken",
                    "id": 1
                }
            ]
        },
        "message": "Service Request Created"
    }
    ```

### 5. Update Service Request

Updates an existing service request by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request to update.
-   **Request:** `application/json`
    ```json
    {
        "status_id": 2
    }
    ```
-   **Validation Rules:**
    *   `admin_id`: `sometimes`, `exists:users,id`
    *   `user_id`: `sometimes`, `exists:users,id`
    *   `service_type_id`: `sometimes`, `exists:users,id`
    *   `request_date`: `sometimes`, `date`
    *   `estimated_date`: `sometimes`, `date`
    *   `status_id`: `sometimes`, `exists:statuses,id`
    *   `details`: `sometimes`, `array`
    *   `details.*.id`: `sometimes`, `exists:service_request_details,id`
    *   `details.*.device_id`: `sometimes`, `exists:devices,id`
    *   `details.*.complaint`: `sometimes`, `string`
    *   `details.*.complaint_images`: `sometimes`, `array`
    *   `details.*.complaint_images.*`: `sometimes`, `file`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`
    *   `service_location`: `sometimes`, `array`
    *   `service_location.location_type`: `sometimes`, `in:internal,external`
    *   `service_location.vendor_id`: `sometimes`, `exists:vendors,id`
    *   `service_location.is_active`: `sometimes`, `boolean`
    *   `service_costs`: `sometimes`, `array`
    *   `service_costs.*.cost_type_id`: `sometimes`, `exists:cost_types,id`
    *   `service_costs.*.amount`: `sometimes`, `numeric`
    *   `service_costs.*.description`: `sometimes`, `string`
    *   `service_cancellation`: `sometimes`, `array`
    *   `service_cancellation.reason`: `sometimes`, `string`
    *   `service_cancellation.canceled_by`: `sometimes`, `exists:users,id`
    *   `vendor_approvals`: `sometimes`, `array`
    *   `vendor_approvals.*.id`: `sometimes`, `exists:vendor_approvals,id`
    *   `vendor_approvals.*.approval_policy_id`: `sometimes`, `exists:approval_policies,id`
    *   `vendor_approvals.*.approval_policy_step_id`: `sometimes`, `exists:approval_policy_steps,id`
    *   `vendor_approvals.*.approver_id`: `sometimes`, `exists:users,id`
    *   `vendor_approvals.*.assigned_by`: `sometimes`, `exists:users,id`
    *   `vendor_approvals.*.approved_at`: `sometimes`, `date`
    *   `vendor_approvals.*.status_id`: `sometimes`, `exists:statuses,id`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "admin_id": 1,
            "user_id": 2,
            "service_type_id": 1,
            "request_date": "2024-01-20",
            "status_id": 2,
            "details": [
                {
                    "id": 1,
                    "service_request_id": 1,
                    "device_id": 1,
                    "complaint": "Screen is broken"
                }
            ]
        },
        "message": "Service Request Updated"
    }
    ```

### 6. Delete Service Request

Deletes a service request by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Service Request Deleted"
    }
    ```

### 7. Get Allowed Transitions for a Service Request

Retrieves the allowed status transitions for a service request.

-   **Endpoint:** `/{id}/allowed-transitions`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "name": "In Progress"
            },
            {
                "id": 2,
                "name": "Cancelled"
            }
        ],
        "message": "Allowed Transitions Found"
    }
    ```

### 8. Cancel a Service Request

Cancels a service request.

-   **Endpoint:** `/{id}/cancellation`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request to cancel.
-   **Request:** `application/json`
    ```json
    {
        "reason": "No longer needed"
    }
    ```
-   **Validation Rules:**
    *   `reason`: `required`, `string`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "reason": "No longer needed",
            "canceled_by": 1
        },
        "message": "Service request cancelled successfully"
    }
    ```

### 9. Get Costs for a Service Request

Retrieves all costs associated with a service request.

-   **Endpoint:** `/{id}/costs`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "service_request_id": 1,
                "cost_type_id": 1,
                "amount": 100.50,
                "description": "Screen replacement"
            }
        ],
        "message": "Costs Found"
    }
    ```

### 10. Add a Cost to a Service Request

Adds a cost to a service request.

-   **Endpoint:** `/{id}/costs`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.
-   **Request:** `application/json`
    ```json
    {
        "cost_type_id": 1,
        "amount": 100.50,
        "description": "Screen replacement"
    }
    ```
-   **Validation Rules:**
    *   `cost_type_id`: `required`, `exists:cost_types,id`
    *   `amount`: `required`, `numeric`, `min:0`
    *   `description`: `nullable`, `string`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "cost_type_id": 1,
            "amount": 100.50,
            "description": "Screen replacement"
        },
        "message": "Cost added successfully"
    }
    ```

### 11. Remove a Cost from a Service Request

Removes a cost from a service request.

-   **Endpoint:** `/{id}/costs/{costId}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.
    *   `costId` (integer, required): The ID of the cost to remove.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Cost removed successfully"
    }
    ```

### 12. Set Service Location for a Service Request

Sets the service location for a service request.

-   **Endpoint:** `/{id}/locations`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.
-   **Request:** `application/json`
    ```json
    {
        "location_type": "external",
        "vendor_id": 1
    }
    ```
-   **Validation Rules:**
    *   `location_type`: `required`, `in:internal,external`
    *   `vendor_id`: `required_if:location_type,external`, `exists:vendors,id`
    *   `is_active`: `boolean`

-   **Success Response (201 Created or 200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "location_type": "external",
            "vendor_id": 1,
            "is_active": true
        },
        "message": "Service location set/updated successfully"
    }
    ```

### 13. Update Service Location for a Service Request

Updates the service location for a service request.

-   **Endpoint:** `/{id}/locations/{locationId}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.
    *   `locationId` (integer, required): The ID of the service location to update.
-   **Request:** `application/json`
    ```json
    {
        "is_active": false
    }
    ```
-   **Validation Rules:**
    *   `location_type`: `sometimes`, `in:internal,external`
    *   `vendor_id`: `required_if:location_type,external`, `exists:vendors,id`
    *   `is_active`: `boolean`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "location_type": "external",
            "vendor_id": 1,
            "is_active": false
        },
        "message": "Service location updated successfully"
    }
    ```

### 14. Approve a Vendor Request

Approves a vendor request for a service request.

-   **Endpoint:** `/approved/{id}`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the vendor approval.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "approval_policy_id": 1,
            "approval_policy_step_id": 1,
            "approver_id": 1,
            "assigned_by": 1,
            "approved_at": "2024-01-21T10:00:00.000000Z",
            "status_id": 2
        },
        "message": "Vendor request approved successfully"
    }
    ```

### 15. Reject a Vendor Request

Rejects a vendor request for a service request.

-   **Endpoint:** `/rejected/{id}`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the vendor approval.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "service_request_id": 1,
            "approval_policy_id": 1,
            "approval_policy_step_id": 1,
            "approver_id": 1,
            "assigned_by": 1,
            "approved_at": null,
            "status_id": 3
        },
        "message": "Vendor request rejected successfully"
    }
    ```

### 16. Get Service Request Details

Retrieves all details for a specific service request.

-   **Endpoint:** `/{id}/details`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "service_request_id": 1,
                "device_id": 1,
                "complaint": "Screen is broken",
                "complaint_images": ["complaint-images/image1.jpg", "complaint-images/image2.png"],
                "created_at": "2024-01-20T10:00:00.000000Z",
                "updated_at": "2024-01-20T10:00:00.000000Z",
                "device": {
                    "id": 1,
                    "device_model_id": 1,
                    "serial_number": "SN12345",
                    "device_model": {
                        "id": 1,
                        "brand": "Dell",
                        "model": "Latitude 5420"
                    }
                }
            }
        ],
        "message": "Service request details retrieved successfully",
        "code": 200
    }
    ```

### 17. Create Service Request Detail

Adds a new detail to an existing service request.

-   **Endpoint:** `/{id}/details`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service request.
-   **Request:** `application/json`
    ```json
    {
        "device_id": 1,
        "complaint": "Keyboard not working",
        "complaint_images": []
    }
    ```
-   **Validation Rules:**
    *   `device_id`: `required`, `exists:devices,id`
    *   `complaint`: `required`, `string`
    *   `complaint_images`: `sometimes`, `array`
    *   `complaint_images.*`: `sometimes`, `file`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "id": 3,
            "service_request_id": 1,
            "device_id": 1,
            "complaint": "Keyboard not working",
            "complaint_images": [],
            "created_at": "2024-01-20T10:15:00.000000Z",
            "updated_at": "2024-01-20T10:15:00.000000Z",
            "device": {
                "id": 1,
                "device_model_id": 1,
                "serial_number": "SN12345"
            }
        },
        "message": "Service request detail created successfully",
        "code": 201
    }
    ```

### 18. Update Service Request Detail

Updates an existing service request detail.

-   **Endpoint:** `/details/{detailId}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `detailId` (integer, required): The ID of the service request detail.
-   **Request:** `application/json`
    ```json
    {
        "device_id": 2,
        "complaint": "Screen flickering issue",
        "complaint_images": []
    }
    ```
-   **Validation Rules:**
    *   `device_id`: `sometimes`, `exists:devices,id`
    *   `complaint`: `sometimes`, `string`
    *   `complaint_images`: `sometimes`, `array`
    *   `complaint_images.*`: `sometimes`, `file`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 3,
            "service_request_id": 1,
            "device_id": 2,
            "complaint": "Screen flickering issue",
            "complaint_images": ["complaint-images/new-image.jpg"],
            "updated_at": "2024-01-20T10:20:00.000000Z",
            "device": {
                "id": 2,
                "device_model_id": 2,
                "serial_number": "SN54321"
            }
        },
        "message": "Service request detail updated successfully",
        "code": 200
    }
    ```

### 19. Delete Service Request Detail

Deletes a service request detail.

-   **Endpoint:** `/details/{detailId}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `detailId` (integer, required): The ID of the service request detail to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Service request detail deleted successfully",
        "code": 200
    }
    ```

---

## Vendor Endpoints

Base URL: `/api/vendors`

### 1. Get All Vendors

Retrieves a list of all vendors.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Query Parameters:**
    *   `search` (optional): A string to filter vendors by their `name`.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "name": "Tech Solutions Inc.",
                "maps_url": "https://maps.google.com/?q=123+Tech+Street+Silicon+Valley+CA",
                "description": "Leading technology solutions provider specializing in hardware and software services.",
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-01-01T10:00:00.000000Z"
            },
            {
                "id": 2,
                "name": "Hardware Pro Services",
                "maps_url": "https://maps.google.com/?q=456+Hardware+Ave+Tech+City+CA",
                "description": "Professional hardware repair and maintenance services for all types of IT equipment.",
                "created_at": "2024-01-01T10:05:00.000000Z",
                "updated_at": "2024-01-01T10:05:00.000000Z"
            }
        ],
        "message": "Vendors retrieved successfully",
        "code": 200
    }
    ```

### 2. Get Vendor by ID

Retrieves a single vendor by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the vendor.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "Tech Solutions Inc.",
            "maps_url": "https://maps.google.com/?q=123+Tech+Street+Silicon+Valley+CA",
            "description": "Leading technology solutions provider specializing in hardware and software services.",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        },
        "message": "Vendor retrieved successfully",
        "code": 200
    }
    ```

### 3. Create New Vendor

Creates a new vendor.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "name": "New Tech Company",
        "maps_url": "https://maps.google.com/?q=789+New+Tech+Blvd+Digital+Park+CA",
        "description": "Specialized in network infrastructure and cloud services."
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `unique:vendors,name`
    *   `maps_url`: `sometimes`, `url`
    *   `description`: `sometimes`, `string`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "id": 6,
            "name": "New Tech Company",
            "maps_url": "https://maps.google.com/?q=789+New+Tech+Blvd+Digital+Park+CA",
            "description": "Specialized in network infrastructure and cloud services.",
            "created_at": "2024-01-20T11:00:00.000000Z",
            "updated_at": "2024-01-20T11:00:00.000000Z"
        },
        "message": "Vendor created successfully",
        "code": 201
    }
    ```

### 4. Update Vendor

Updates an existing vendor by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the vendor to update.
-   **Request:** `application/json`
    ```json
    {
        "name": "Updated Tech Company",
        "maps_url": "https://maps.google.com/?q=456+Updated+Ave+Tech+City+CA",
        "description": "Updated description for technology services provider."
    }
    ```
-   **Validation Rules:**
    *   `name`: `sometimes`, `string`, `unique:vendors,name,{id}`
    *   `maps_url`: `sometimes`, `url`
    *   `description`: `sometimes`, `string`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "Updated Tech Company",
            "maps_url": "https://maps.google.com/?q=456+Updated+Ave+Tech+City+CA",
            "description": "Updated description for technology services provider.",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-20T11:15:00.000000Z"
        },
        "message": "Vendor updated successfully",
        "code": 200
    }
    ```

### 5. Delete Vendor

Deletes a vendor by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the vendor to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Vendor deleted successfully",
        "code": 200
    }
    ```

---

## Service Type Endpoints

Base URL: `/api/service-types`

### 1. Get All Service Types

Retrieves a list of all service types.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Query Parameters:**
    *   `search` (optional): A string to filter service types by their `name`.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "name": "Hardware Repair",
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-01-01T10:00:00.000000Z"
            },
            {
                "id": 2,
                "name": "Software Installation",
                "created_at": "2024-01-01T10:05:00.000000Z",
                "updated_at": "2024-01-01T10:05:00.000000Z"
            }
        ],
        "message": "Service types retrieved successfully",
        "code": 200
    }
    ```

### 2. Get Service Type by ID

Retrieves a single service type by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service type.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "Hardware Repair",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        },
        "message": "Service type retrieved successfully",
        "code": 200
    }
    ```

### 3. Create New Service Type

Creates a new service type.

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Authentication:** Bearer Token (Required)
-   **Request:** `application/json`
    ```json
    {
        "name": "Data Recovery Service"
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `unique:service_types,name`

-   **Success Response (201 Created):**
    ```json
    {
        "success": true,
        "data": {
            "id": 11,
            "name": "Data Recovery Service",
            "created_at": "2024-01-20T11:00:00.000000Z",
            "updated_at": "2024-01-20T11:00:00.000000Z"
        },
        "message": "Service type created successfully",
        "code": 201
    }
    ```

### 4. Update Service Type

Updates an existing service type by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service type to update.
-   **Request:** `application/json`
    ```json
    {
        "name": "Advanced Hardware Repair"
    }
    ```
-   **Validation Rules:**
    *   `name`: `required`, `string`, `unique:service_types,name,{id}`

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "Advanced Hardware Repair",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-20T11:15:00.000000Z"
        },
        "message": "Service type updated successfully",
        "code": 200
    }
    ```

### 5. Delete Service Type

Deletes a service type by ID.

-   **Endpoint:** `/{id}`
-   **Method:** `DELETE`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the service type to delete.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": null,
        "message": "Service type deleted successfully",
        "code": 200
    }
    ```

---

## Status Endpoints

Base URL: `/api/statuses`

### 1. Get All Statuses

Retrieves a list of all statuses, optionally filtered by entity type.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Query Parameters:**
    *   `entity_type_id` (optional): Filter statuses by entity type ID.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "entity_type_id": 1,
                "code": "PENDING",
                "name": "Pending",
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-01-01T10:00:00.000000Z",
                "entity_type": {
                    "id": 1,
                    "name": "Service Request"
                }
            },
            {
                "id": 2,
                "entity_type_id": 1,
                "code": "IN_PROGRESS",
                "name": "In Progress",
                "created_at": "2024-01-01T10:05:00.000000Z",
                "updated_at": "2024-01-01T10:05:00.000000Z",
                "entity_type": {
                    "id": 1,
                    "name": "Service Request"
                }
            }
        ],
        "message": "Statuses retrieved successfully",
        "code": 200
    }
    ```

### 2. Get Status by ID

Retrieves a single status by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the status.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "entity_type_id": 1,
            "code": "PENDING",
            "name": "Pending",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z",
            "entity_type": {
                "id": 1,
                "name": "Service Request"
            }
        },
        "message": "Status retrieved successfully",
        "code": 200
    }
    ```

---

## Role Endpoints

Base URL: `/api/roles`

### 1. Get All Roles

Retrieves a list of all roles.

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": [
            {
                "id": 1,
                "name": "admin",
                "created_at": "2024-01-01T10:00:00.000000Z",
                "updated_at": "2024-01-01T10:00:00.000000Z"
            },
            {
                "id": 2,
                "name": "user",
                "created_at": "2024-01-01T10:05:00.000000Z",
                "updated_at": "2024-01-01T10:05:00.000000Z"
            },
            {
                "id": 3,
                "name": "technician",
                "created_at": "2024-01-01T10:10:00.000000Z",
                "updated_at": "2024-01-01T10:10:00.000000Z"
            },
            {
                "id": 4,
                "name": "superior",
                "created_at": "2024-01-01T10:15:00.000000Z",
                "updated_at": "2024-01-01T10:15:00.000000Z"
            }
        ],
        "message": "Roles retrieved successfully",
        "code": 200
    }
    ```

### 2. Get Role by ID

Retrieves a single role by its ID.

-   **Endpoint:** `/{id}`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)
-   **Path Parameters:**
    *   `id` (integer, required): The ID of the role.

-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "id": 1,
            "name": "admin",
            "created_at": "2024-01-01T10:00:00.000000Z",
            "updated_at": "2024-01-01T10:00:00.000000Z"
        },
        "message": "Role retrieved successfully",
        "code": 200
    }
    ```

---

## Error Response Format

All API endpoints follow a consistent error response format:

### Standard Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": [
            "Specific validation error message"
        ]
    },
    "code": 400
}
```

### Common HTTP Status Codes
- **200 OK**: Request successful
- **201 Created**: Resource created successfully
- **400 Bad Request**: Invalid request data
- **401 Unauthorized**: Authentication required or failed
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server error

### Authentication Headers
For protected endpoints, include the following header:
```
Authorization: Bearer YOUR_ACCESS_TOKEN_HERE
```

### Content Type
All POST/PUT/PATCH requests should include:
```
Content-Type: application/json
Accept: application/json
```

---

## Usage Examples

### Complete Service Request Creation Flow
```bash
# 1. Login to get token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@eci-service.com",
    "password": "admin123"
  }'

# 2. Create service request with details
curl -X POST http://localhost:8000/api/service-requests \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "service_type_id": 1,
    "details": [
      {
        "device_id": 1,
        "complaint": "Laptop screen is broken"
      }
    ],
    "service_location": {
      "location_type": "internal"
    }
  }'
```

### Pagination
For list endpoints that support pagination:
```bash
curl -X GET "http://localhost:8000/api/service-requests?per_page=10&page=2" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

This documentation covers all available endpoints in the ECI IT Service API with complete request/response examples and validation rules.