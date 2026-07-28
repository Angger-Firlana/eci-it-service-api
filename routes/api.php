<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\DeviceModelController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ServiceRequestCancellationController;
use App\Http\Controllers\ServiceRequestCostController;
use App\Http\Controllers\ServiceRequestLocationController;
use App\Http\Controllers\ServiceRequestApprovalController;
use App\Http\Controllers\ReferenceDataController;
use App\Http\Controllers\ExportInvoiceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CostTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContactAdminController;
use App\Http\Controllers\InboxApprovalController;
use App\Http\Controllers\MailSettingController;
use App\Http\Controllers\SetEmailItController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'getDataMe'])->middleware('auth:sanctum');
});

Route::post('/contact-admin', [ContactAdminController::class, 'send']);

Route::prefix('device-type')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [DeviceTypeController::class, 'index'])->middleware('role:user,admin,operator');
    Route::get('/{id}', [DeviceTypeController::class, 'show'])->middleware('role:admin,operator');
    Route::post('/', [DeviceTypeController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [DeviceTypeController::class, 'update'])->middleware('role:admin');
    Route::delete('/{id}', [DeviceTypeController::class, 'destroy'])->middleware('role:admin');
});

Route::prefix('device-model')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [DeviceModelController::class, 'index'])->middleware('role:user,admin,operator');
    Route::get('/{id}', [DeviceModelController::class, 'show'])->middleware('role:user,admin,operator');
    Route::post('/', [DeviceModelController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [DeviceModelController::class, 'update'])->middleware('role:admin');
    Route::patch('/{id}', [DeviceModelController::class, 'patch'])->middleware('role:admin');
    Route::delete('/{id}', [DeviceModelController::class, 'destroy'])->middleware('role:admin');
});

Route::prefix('devices')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [DeviceController::class, 'index'])->middleware('role:user,admin,operator,manager');
    Route::get('/{id}', [DeviceController::class, 'show'])->middleware('role:user,admin,operator,manager');
    Route::post('/', [DeviceController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [DeviceController::class, 'update'])->middleware('role:admin');
    Route::patch('/{id}', [DeviceController::class, 'patch'])->middleware('role:admin');
    Route::delete('/{id}', [DeviceController::class, 'destroy'])->middleware('role:admin');

});

Route::prefix('service-requests')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [ServiceRequestController::class, 'index'])->middleware('role:user,admin,operator,manager');
    Route::get('/summary', [ServiceRequestController::class, 'summary'])
        ->middleware('role:user,admin,operator,manager')
        ->middleware('throttle:60,1');
    Route::get('/stats', [ServiceRequestController::class, 'stats'])->middleware('role:admin,operator,manager');
    Route::get('/{id}', [ServiceRequestController::class, 'show'])->middleware('role:user,admin,operator,manager');
    Route::post('/', [ServiceRequestController::class, 'store'])->middleware('role:user,operator,admin,manager');
    Route::put('/{id}', [ServiceRequestController::class, 'update'])->middleware('role:operator,admin');
    Route::delete('/{id}', [ServiceRequestController::class, 'destroy'])->middleware('role:admin,operator,technician,supervisor,manager,senior manager,ceo');
    Route::get('/{id}/allowed-transitions', [ServiceRequestController::class, 'allowedTransitions'])->middleware('role:user,admin,operator,manager,technician');
    Route::put('/{id}/update-note', [ServiceRequestController::class, 'updateNote'])->middleware('role:operator,admin');
    Route::put('/{id}/update-solution', [ServiceRequestController::class, 'updateSolution'])->middleware('role:operator,admin');
    Route::get('/{id}/download-invoice', [ExportInvoiceController::class, 'download'])->middleware('role:user,admin,operator,manager');
    Route::get('/{id}/preview-invoice', [ExportInvoiceController::class, 'downloadPreview'])->middleware('role:user,admin,operator,manager');
    Route::get('/{id}/can-print-invoice', [ExportInvoiceController::class, 'canPrint'])->middleware('role:user,admin,operator,manager');
    
    //costs
    Route::get('/{serviceRequestId}/costs', [ServiceRequestCostController::class, 'index'])->middleware('role:admin,operator,manager');
    Route::post('/{serviceRequestId}/costs', [ServiceRequestCostController::class, 'store'])->middleware('role:admin,operator,manager');
    Route::put('/{serviceRequestId}/costs/{costId}', [ServiceRequestCostController::class, 'update'])->middleware('role:admin,operator,manager');
    Route::get('/{serviceRequestId}/costs/{costId}/attachment', [ServiceRequestCostController::class, 'attachment'])->middleware('role:user,admin,operator,manager');
    Route::delete('/{serviceRequestId}/costs/{costId}', [ServiceRequestCostController::class, 'destroy'])->middleware('role:admin,operator,manager');

    //Locations
    Route::post('/{serviceRequestId}/locations', [ServiceRequestLocationController::class, 'store'])->middleware('role:admin,operator,manager');
    Route::get('/{serviceRequestId}/locations', [ServiceRequestLocationController::class, 'index'])->middleware('role:user,admin,operator,manager');
    Route::get('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'show'])->middleware('role:user,admin,operator,manager');
    Route::put('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'update'])->middleware('role:admin,operator,manager');
    Route::delete('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'destroy'])->middleware('role:admin,operator,manager');

    // Approval (Keep existing or aliased if needed)
    Route::get('/{serviceRequestId}/approvers', [ApprovalController::class, 'getApproversByServiceRequestId'])->middleware('role:admin,operator,manager');
    Route::post('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'store'])->middleware('role:admin,operator,manager');
    Route::get('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'index'])->middleware('role:admin,operator,manager');
    Route::put('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'update'])->middleware('role:admin,operator,manager');
    Route::delete('/{serviceRequestId}/approvals/{approvalId}', [ServiceRequestApprovalController::class, 'destroy'])->middleware('role:admin,operator,manager');
    Route::post('/approved/{approvalId}', [ApprovalController::class, 'approveVendorRequest'])->middleware('role:admin,operator,manager');
    Route::post('/need-repair/{serviceRequestId}', [ApprovalController::class, 'deviceNeedRepair'])->middleware('role:admin,operator,manager');
    Route::post('/no-need-repair/{serviceRequestId}', [ApprovalController::class, 'deviceNoNeedRepair'])->middleware('role:admin,operator,manager');
    Route::post('/rejected/{approvalId}', [ApprovalController::class, 'rejectVendorRequest'])->middleware('role:admin,operator,manager');

    //Cancellation
    Route::get('/{serviceRequestId}/cancellation', [ServiceRequestCancellationController::class, 'index'])->middleware('role:user,admin,operator,manager');
    Route::post('/{serviceRequestId}/cancellation', [ServiceRequestCancellationController::class, 'store'])->middleware('role:user,admin,operator,manager');
    Route::put('/{serviceRequestId}/cancellation/{cancellationId}', [ServiceRequestCancellationController::class, 'update'])->middleware('role:user,admin,operator,manager');
    Route::delete('/{serviceRequestId}/cancellation/{cancellationId}', [ServiceRequestCancellationController::class, 'destroy'])->middleware('role:user,admin,operator,manager');
});

Route::prefix('references')->middleware('auth:sanctum')->group(function() {
    Route::get('/service-types', [ReferenceDataController::class, 'getServiceTypes']);
    Route::post('/service-types', [ReferenceDataController::class, 'storeServiceType']);
    Route::get('/statuses', [ReferenceDataController::class, 'getStatuses']);
    Route::get('/vendors', [ReferenceDataController::class, 'getVendors']);
    Route::get('/roles', [ReferenceDataController::class, 'getRoles']);
    Route::get('/departments', [ReferenceDataController::class, 'getDepartments']);
    Route::get('/cost-types', [ReferenceDataController::class, 'getCostTypes']);
});

Route::prefix('departments')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::post('/', [DepartmentController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [DepartmentController::class, 'update'])->middleware('role:admin');
    Route::delete('/{id}', [DepartmentController::class, 'destroy'])->middleware('role:admin');
});

Route::prefix('users')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [UserController::class, 'update'])->middleware('role:admin');
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('role:admin');
});

Route::prefix('invoices')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [InvoiceController::class, 'index']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::get('/{id}/print', [InvoiceController::class, 'print']);
    Route::get('/{id}/download', [InvoiceController::class, 'download']);
});

Route::prefix('vendors')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [VendorController::class, 'index']);
    Route::get('/{id}', [VendorController::class, 'show']);
    Route::post('/', [VendorController::class, 'store'])->middleware('role:admin,operator');
    Route::put('/{id}', [VendorController::class, 'update'])->middleware('role:admin,operator');
    Route::delete('/{id}', [VendorController::class, 'destroy'])->middleware('role:admin,operator');
});

Route::prefix('cost-types')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [CostTypeController::class, 'index']);
    Route::get('/{id}', [CostTypeController::class, 'show']);
    Route::post('/', [CostTypeController::class, 'store'])->middleware('role:admin');
    Route::put('/{id}', [CostTypeController::class, 'update'])->middleware('role:admin');
    Route::delete('/{id}', [CostTypeController::class, 'destroy'])->middleware('role:admin');
});

Route::prefix('notifications')->middleware('auth:sanctum')->group(function(){
    Route::get('/', [NotificationController::class, 'index']);
    Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
});

Route::prefix('inbox-approvals')->middleware('auth:sanctum')->group(function(){
    Route::get('/{statusId}', [InboxApprovalController::class, 'index'])->middleware('role:admin,operator,manager');
    Route::get('/{statusId}/summary', [InboxApprovalController::class, 'summary'])
        ->middleware('role:admin,operator,manager')
        ->middleware('throttle:60,1');
    Route::put('/{id}/read', [InboxApprovalController::class, 'readInbox'])->middleware('role:admin,operator,manager');
});

Route::prefix('mail-settings')->middleware(['auth:sanctum', 'role:admin'])->group(function(){
    Route::get('/', [MailSettingController::class, 'show']);
    Route::put('/', [MailSettingController::class, 'update']);
    Route::post('/test', [MailSettingController::class, 'test']);

    Route::prefix('it-emails')->group(function(){
        Route::get('/', [SetEmailItController::class, 'index']);
        Route::post('/', [SetEmailItController::class, 'store']);
        Route::put('/{id}', [SetEmailItController::class, 'update']);
        Route::delete('/{id}', [SetEmailItController::class, 'destroy']);
    });
});
