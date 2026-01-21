# API Documentation for ECI IT Service

This document provides an overview of the API endpoints, their expected request formats, and typical response structures for the ECI IT Service application.

---

## Authentication Endpoints

Base URL: `/api/auth`

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
-   **Success Response (200 OK):**
    ```json
    {
        "success": true,
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "department_id": 1
                ...
            },
            "token": "YOUR_ACCESS_TOKEN"
        },
        "message": "Login successful"
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
        "department_id": 1
    }
    ```
-   **Success Response (201 Created)**

### 3. Get Authenticated User Data

-   **Endpoint:** `/me`
-   **Method:** `GET`
-   **Authentication:** Bearer Token (Required)

---

## Service Request Endpoints

Base URL: `/api/service-requests`

### 1. Get All Service Requests

-   **Endpoint:** `/`
-   **Method:** `GET`
-   **Query Parameters:** `status_id`, `search`, `page`, etc.

### 2. Create Service Request

-   **Endpoint:** `/`
-   **Method:** `POST`
-   **Request:** `application/json`
    ```json
    {
        "service_type_id": 1,
        "details": [
            {
                "device_id": 1,
                "complaint": "Screen broken"
            }
        ]
    }
    ```

### 3. Update Service Request (Status Change / Invoice Generation)

-   **Endpoint:** `/{id}`
-   **Method:** `PUT`
-   **Request:** `application/json`
    ```json
    {
        "status_id": 2, // 2 = Completed
        "admin_id": 1   // Required when setting status to 2
    }
    ```
-   **Note:** Updating status to `2` automatically generates an **Invoice**.

### 4. Export Invoice PDF

-   **Endpoint:** `/{id}/download-invoice`
-   **Method:** `GET`
-   **Description:** Downloads the generated invoice PDF for a completed service request.

### 5. Allowed Transitions

-   **Endpoint:** `/{id}/allowed-transitions`
-   **Method:** `GET`
-   **Description:** Returns a list of allowed status transitions for the user.

---

## Invoice Endpoints

Base URL: `/api/invoices`

### 1. Get All Invoices

-   **Endpoint:** `/`
-   **Method:** `GET`

### 2. Get Invoice by ID

-   **Endpoint:** `/{id}`
-   **Method:** `GET`

### 3. Print Invoice Data

-   **Endpoint:** `/{id}/print`
-   **Method:** `GET`
-   **Description:** Returns formatted JSON data suitable for frontend print views.
    ```json
    {
        "success": true,
        "data": {
            "invoice_number": "INV202401210001",
            "issue_date": "2024-01-21T00:00:00.000000Z",
            "customer": { ... },
            "device": { ... }, // Single device details
            ...
        }
    }
    ```

---

## Reference Data Endpoints

Base URL: `/api/references`

-   **Service Types:** `GET /service-types`
-   **Statuses:** `GET /statuses`
-   **Vendors:** `GET /vendors`

---

## Department & User Management

### Users
-   Users can now belong to a `Department`.
-   `department_id` is available in User responses.

### Departments (Database Only)
-   `departments` table created with `name` and `code`.
-   Relationship linked to Users.