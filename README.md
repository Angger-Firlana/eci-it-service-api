# ECI IT Service API Database Schema Documentation

This document outlines the database schema for the ECI IT Service API, including tables, their attributes, and relationships.

## Entity Relationship Diagram (ERD) Description

The database schema is designed to manage various aspects of IT service requests, user authentication, notifications, audit logging, and an approval system. It is composed of several interconnected modules: User & Authorization, Notification & Audit, Master Data, Status Flow, Device Management, Service Request Management, Location & Vendor Management, Service Cancellation, Cost & Invoice, and an Approval System with a Condition Engine.

---

## 🔐 User & Authorization

### `users` table
-   **id**: Primary Key. Unique identifier for the user.
-   **name**: User's full name.
-   **email**: User's email address, must be unique.
-   **password**: Hashed password for user authentication.
-   **pin**: Personal identification number for additional security or quick access.

### `roles` table
-   **id**: Primary Key. Unique identifier for the role.
-   **name**: Name of the role (e.g., 'Admin', 'Technician', 'User').
-   **description**: A brief description of the role's responsibilities.

### `user_roles` table
-   **id**: Primary Key.
-   **user_id**: Foreign Key referencing `users.id`. Links a user to a role.
-   **role_id**: Foreign Key referencing `roles.id`. Links a role to a user.

### Relationships (User & Authorization)
-   `users` has a one-to-many relationship with `user_roles` (one user can have many roles).
-   `roles` has a one-to-many relationship with `user_roles` (one role can be assigned to many users).
-   `user_roles` is a pivot table establishing a many-to-many relationship between `users` and `roles`.

---

## 🔔 Notification & Audit

### `notifications` table
-   **id**: Primary Key. Unique identifier for the notification.
-   **user_id**: Foreign Key referencing `users.id`. The recipient of the notification.
-   **title**: Title of the notification.
-   **message**: Full content of the notification.
-   **read_at**: Timestamp when the notification was read (nullable).
-   **created_at**: Timestamp when the notification was created.

### `audit_logs` table
-   **id**: Primary Key. Unique identifier for the audit log entry.
-   **actor_id**: Foreign Key referencing `users.id`. The user who performed the action.
-   **entity_id**: ID of the entity that was affected (e.g., `service_request.id`).
-   **entity_type_id**: Foreign Key referencing `entity_types.id`. Identifies the type of entity affected.
-   **action**: Description of the action performed (e.g., 'created', 'updated', 'deleted').
-   **old_status_id**: Foreign Key referencing `statuses.id`. The status before the action (nullable).
-   **new_status_id**: Foreign Key referencing `statuses.id`. The status after the action (nullable).
-   **created_at**: Timestamp of the audit event.

### Relationships (Notification & Audit)
-   `users` has a one-to-many relationship with `notifications` (one user can receive many notifications).
-   `users` has a one-to-many relationship with `audit_logs` (one user can be the actor for many audit events).
-   `entity_types` has a one-to-many relationship with `audit_logs` (one entity type can be associated with many audit events).
-   `statuses` has a one-to-many relationship with `audit_logs` (one status can be an old or new status in many audit events).

---

## 🧩 Master Data

### `entity_types` table
-   **id**: Primary Key. Unique identifier for the entity type.
-   **code**: Unique code for the entity type (e.g., 'SR' for Service Request).
-   **name**: Name of the entity type (e.g., 'Service Request').

### `statuses` table
-   **id**: Primary Key. Unique identifier for the status.
-   **entity_type_id**: Foreign Key referencing `entity_types.id`. Associates a status with a specific entity type.
-   **code**: Unique code for the status within an entity type (e.g., 'OPEN', 'IN_PROGRESS').
-   **name**: Descriptive name for the status (e.g., 'Open', 'In Progress').

### Relationships (Master Data)
-   `entity_types` has a one-to-many relationship with `statuses` (one entity type can have many statuses).

---

## 🔄 Status Flow

### `status_transitions` table
-   **id**: Primary Key. Unique identifier for the status transition rule.
-   **code**: Unique code for the transition (e.g., 'OPEN_TO_IN_PROGRESS').
-   **from_status_id**: Foreign Key referencing `statuses.id`. The starting status for the transition.
-   **to_status_id**: Foreign Key referencing `statuses.id`. The ending status for the transition.
-   **description**: Description of the transition rule.

### `status_transition_roles` table
-   **id**: Primary Key.
-   **status_transition_id**: Foreign Key referencing `status_transitions.id`. Links a role to a specific transition.
-   **role_id**: Foreign Key referencing `roles.id`. The role required to perform this transition.

### Relationships (Status Flow)
-   `statuses` has a one-to-many relationship with `status_transitions` (a status can be a 'from' or 'to' status in multiple transitions).
-   `status_transitions` has a one-to-many relationship with `status_transition_roles` (one transition can require multiple roles).
-   `roles` has a one-to-many relationship with `status_transition_roles` (one role can be involved in multiple transitions).
-   `status_transition_roles` is a pivot table establishing a many-to-many relationship between `status_transitions` and `roles`.

---

## 🛠 Device

### `device_types` table
-   **id**: Primary Key. Unique identifier for the device type.
-   **name**: Name of the device type (e.g., 'Laptop', 'Printer', 'Monitor').

### `device_models` table
-   **id**: Primary Key. Unique identifier for the device model.
-   **device_type_id**: Foreign Key referencing `device_types.id`. Associates a model with a device type.
-   **brand**: Brand of the device (e.g., 'Dell', 'HP').
-   **model**: Specific model name/number.

### `devices` table
-   **id**: Primary Key. Unique identifier for a specific device instance.
-   **device_model_id**: Foreign Key referencing `device_models.id`. Associates a device with its model.
-   **serial_number**: Unique serial number for the device.

### Relationships (Device)
-   `device_types` has a one-to-many relationship with `device_models` (one device type can have many models).
-   `device_models` has a one-to-many relationship with `devices` (one device model can have many physical devices).

---

## 📄 Service Request

### `service_types` table
-   **id**: Primary Key. Unique identifier for the service type.
-   **name**: Name of the service type (e.g., 'Repair', 'Installation', 'Maintenance').

### `service_requests` table
-   **id**: Primary Key. Unique identifier for the service request.
-   **user_id**: Foreign Key referencing `users.id`. The user who initiated the request.
-   **admin_id**: Foreign Key referencing `users.id`. The admin assigned to the request (nullable).
-   **service_type_id**: Foreign Key referencing `service_types.id`. The type of service requested.
-   **service_number**: Unique identifier for the service request (e.g., 'SR-2023-0001').
-   **request_date**: Date when the request was made.
-   **estimated_date**: Estimated completion date for the service (nullable).
-   **status_id**: Foreign Key referencing `statuses.id`. The current status of the service request.

### `service_request_details` table
-   **id**: Primary Key.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. Links details to a service request.
-   **device_id**: Foreign Key referencing `devices.id`. The device associated with this request detail.
-   **complaint**: Description of the complaint or issue.

### Relationships (Service Request)
-   `users` has one-to-many relationships with `service_requests` (one user can create many requests, one user can be an admin for many requests).
-   `service_types` has a one-to-many relationship with `service_requests` (one service type can be requested many times).
-   `statuses` has a one-to-many relationship with `service_requests` (one status can apply to many service requests).
-   `service_requests` has a one-to-many relationship with `service_request_details` (one request can have many details, e.g., for multiple devices).
-   `devices` has a one-to-many relationship with `service_request_details` (one device can be involved in multiple request details).

---

## 📍 Location & Vendor

### `vendors` table
-   **id**: Primary Key. Unique identifier for the vendor.
-   **name**: Name of the vendor.
-   **maps_url**: URL to the vendor's location on a map (nullable).
-   **description**: Description of the vendor.

### `service_locations` table
-   **id**: Primary Key.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. The service request associated with this location.
-   **vendor_id**: Foreign Key referencing `vendors.id`. The vendor providing service at this location (nullable).
-   **location_type**: Type of location (e.g., 'On-site', 'Off-site', 'Vendor').
-   **is_active**: Boolean indicating if this location is currently active for the request.

### Relationships (Location & Vendor)
-   `vendors` has a one-to-many relationship with `service_locations` (one vendor can be associated with many service locations).
-   `service_requests` has a one-to-many relationship with `service_locations` (one service request can have multiple associated service locations throughout its lifecycle).

---

## ❌ Service Cancellation

### `service_cancellations` table
-   **id**: Primary Key.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. The service request that was cancelled.
-   **cancelled_by**: Foreign Key referencing `users.id`. The user who cancelled the request.
-   **reason**: Textual reason for the cancellation.

### Relationships (Service Cancellation)
-   `service_requests` has a one-to-one relationship with `service_cancellations` (one service request can only have one cancellation record).
-   `users` has a one-to-many relationship with `service_cancellations` (one user can cancel many service requests).

---

## 💰 Cost & Invoice

### `cost_types` table
-   **id**: Primary Key. Unique identifier for the cost type.
-   **code**: Unique code for the cost type (e.g., 'PARTS', 'LABOR').
-   **name**: Name of the cost type (e.g., 'Parts Cost', 'Labor Cost').

### `service_costs` table
-   **id**: Primary Key.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. The service request these costs are associated with.
-   **cost_type_id**: Foreign Key referencing `cost_types.id`. The type of cost.
-   **amount**: The monetary amount of the cost.
-   **description**: Description of the specific cost item.

### `invoices` table
-   **id**: Primary Key.
-   **invoice_number**: Unique identifier for the invoice.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. The service request associated with this invoice.
-   **issue_date**: Date the invoice was issued.
-   **due_date**: Date the invoice is due.
-   **total_amount**: The total monetary amount of the invoice.
-   **status_id**: Foreign Key referencing `statuses.id`. The current status of the invoice (e.g., 'Paid', 'Pending', 'Overdue').

### Relationships (Cost & Invoice)
-   `service_requests` has a one-to-many relationship with `service_costs` (one service request can have many cost items).
-   `cost_types` has a one-to-many relationship with `service_costs` (one cost type can apply to many service cost items).
-   `service_requests` has a one-to-one relationship with `invoices` (one service request typically has one invoice).
-   `statuses` has a one-to-many relationship with `invoices` (one status can apply to many invoices).

---

## ✅ Approval System

### `approval_policies` table
-   **id**: Primary Key. Unique identifier for the approval policy.
-   **entity_type_id**: Foreign Key referencing `entity_types.id`. The type of entity this policy applies to (e.g., 'Service Request').
-   **condition_type_id**: Foreign Key referencing `condition_types.id`. The type of condition used to trigger this policy.
-   **condition_value**: The value of the condition (e.g., 'HIGH', '5000' if amount > 5000).
-   **is_active**: Boolean indicating if the policy is currently active.

### `approval_policy_steps` table
-   **id**: Primary Key.
-   **approval_policy_id**: Foreign Key referencing `approval_policies.id`. The policy this step belongs to.
-   **step_order**: The order of this step in the approval process.
-   **role_id**: Foreign Key referencing `roles.id`. The role required to approve this step.
-   **is_mandatory**: Boolean indicating if this step is mandatory for approval.

### `vendor_approvals` table
-   **id**: Primary Key.
-   **approval_policy_id**: Foreign Key referencing `approval_policies.id`. The policy that triggered this approval.
-   **approval_policy_step_id**: Foreign Key referencing `approval_policy_steps.id`. The specific step being approved.
-   **service_request_id**: Foreign Key referencing `service_requests.id`. The service request requiring approval.
-   **approver_id**: Foreign Key referencing `users.id`. The user who approved/rejected this step.
-   **approved_at**: Timestamp when the approval/rejection occurred (nullable).
-   **assigned_by**: Foreign Key referencing `users.id`. The user who assigned this approval step (if applicable).
-   **assigned_at**: Timestamp when this approval step was assigned.
-   **status_id**: Foreign Key referencing `statuses.id`. The status of this approval step (e.g., 'Pending', 'Approved', 'Rejected').

### Relationships (Approval System)
-   `entity_types` has a one-to-many relationship with `approval_policies` (one entity type can have many approval policies).
-   `condition_types` has a one-to-many relationship with `approval_policies` (one condition type can be used in many approval policies).
-   `approval_policies` has a one-to-many relationship with `approval_policy_steps` (one policy can have many approval steps).
-   `roles` has a one-to-many relationship with `approval_policy_steps` (one role can be required for many approval steps).
-   `approval_policies` has a one-to-many relationship with `vendor_approvals` (one policy can trigger many individual vendor approvals).
-   `approval_policy_steps` has a one-to-many relationship with `vendor_approvals` (one step can be part of many individual vendor approvals).
-   `service_requests` has a one-to-many relationship with `vendor_approvals` (one service request can require multiple vendor approvals).
-   `users` has one-to-many relationships with `vendor_approvals` (one user can be an approver or an assigner for many approvals).
-   `statuses` has a one-to-many relationship with `vendor_approvals` (one status can apply to many vendor approval steps).

---

## 🧠 Condition Engine

### `condition_type_data` table
-   **id**: Primary Key. Unique identifier for a type of condition data.
-   **type_data**: Description or identifier for the type of data (e.g., 'Numeric', 'String', 'Boolean').

### `condition_types` table
-   **id**: Primary Key. Unique identifier for the condition type.
-   **condition_type_data_id**: Foreign Key referencing `condition_type_data.id`. The underlying data type for this condition.
-   **code**: Unique code for the condition type (e.g., 'AMOUNT_GREATER_THAN', 'PRIORITY_IS').
-   **name**: Descriptive name for the condition type (e.g., 'Amount Greater Than', 'Priority Is').

### Relationships (Condition Engine)
-   `condition_type_data` has a one-to-many relationship with `condition_types` (one condition data type can have many specific condition types).