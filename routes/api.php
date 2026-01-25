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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'getDataMe'])->middleware('auth:sanctum');
});

Route::prefix('device-type')->group(function () {
    Route::get('/', [DeviceTypeController::class, 'index']);
    Route::get('/{id}', [DeviceTypeController::class, 'show']);
    Route::post('/', [DeviceTypeController::class, 'store']);
    Route::put('/{id}', [DeviceTypeController::class, 'update']);
    Route::delete('/{id}', [DeviceTypeController::class, 'destroy']);
});

Route::prefix('device-model')->group(function(){
    Route::get('/', [DeviceModelController::class, 'index']);
    Route::get('/{id}', [DeviceModelController::class, 'show']);
    Route::post('/', [DeviceModelController::class, 'store']);
    Route::put('/{id}', [DeviceModelController::class, 'update']);
    Route::patch('/{id}', [DeviceModelController::class, 'patch']);
    Route::delete('/{id}', [DeviceModelController::class, 'destroy']);
});

Route::prefix('devices')->group(function(){
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{id}', [DeviceController::class, 'show']);
    Route::post('/', [DeviceController::class, 'store']);
    Route::put('/{id}', [DeviceController::class, 'update']);
    Route::patch('/{id}', [DeviceController::class, 'patch']);
    Route::delete('/{id}', [DeviceController::class, 'destroy']);

});

Route::prefix('service-requests')->group(function(){
    Route::get('/', [ServiceRequestController::class, 'index']);
    Route::get('/stats', [ServiceRequestController::class, 'stats']);
    Route::get('/{id}', [ServiceRequestController::class, 'show']);
    Route::post('/', [ServiceRequestController::class, 'store']);
    Route::put('/{id}', [ServiceRequestController::class, 'update']);
    Route::delete('/{id}', [ServiceRequestController::class, 'destroy']);
    Route::get('/{id}/allowed-transitions', [ServiceRequestController::class, 'allowedTransitions']);
    Route::get('/{id}/download-invoice', [ExportInvoiceController::class, 'download']);
    
    //costs
    Route::get('/{serviceRequestId}/costs', [ServiceRequestCostController::class, 'index']);
    Route::post('/{serviceRequestId}/costs', [ServiceRequestCostController::class, 'store']);
    Route::put('/{serviceRequestId}/costs/{costId}', [ServiceRequestCostController::class, 'update']);
    Route::delete('/{serviceRequestId}/costs/{costId}', [ServiceRequestCostController::class, 'destroy']);

    //Locations
    Route::post('/{serviceRequestId}/locations', [ServiceRequestLocationController::class, 'store']);
    Route::get('/{serviceRequestId}/locations', [ServiceRequestLocationController::class, 'index']);
    Route::get('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'show']);
    Route::put('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'update']);
    Route::delete('/{serviceRequestId}/locations/{locationId}', [ServiceRequestLocationController::class, 'destroy']);

    // Approval (Keep existing or aliased if needed)
    Route::get('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'index']);
    Route::post('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'store']);
    Route::put('/{serviceRequestId}/approvals', [ServiceRequestApprovalController::class, 'update']);
    Route::delete('/{serviceRequestId}/approvals/{approvalId}', [ServiceRequestApprovalController::class, 'destroy']);
    Route::post('/approved/{approvalId}', [ApprovalController::class, 'approveVendorRequest']);
    Route::post('/rejected/{approvalId}', [ApprovalController::class, 'rejectVendorRequest']);

    //Cancellation
    Route::get('/{serviceRequestId}/cancellation', [ServiceRequestCancellationController::class, 'index']);
    Route::post('/{serviceRequestId}/cancellation', [ServiceRequestCancellationController::class, 'store']);
    Route::put('/{serviceRequestId}/cancellation', [ServiceRequestCancellationController::class, 'update']);
});

Route::prefix('references')->group(function() {
    Route::get('/service-types', [ReferenceDataController::class, 'getServiceTypes']);
    Route::get('/statuses', [ReferenceDataController::class, 'getStatuses']);
    Route::get('/vendors', [ReferenceDataController::class, 'getVendors']);
    Route::get('/roles', [ReferenceDataController::class, 'getRoles']);
    Route::get('/departments', [ReferenceDataController::class, 'getDepartments']);
    Route::get('/users', [ReferenceDataController::class, 'getUsers']);
});

Route::prefix('departments')->group(function(){
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::put('/{id}', [DepartmentController::class, 'update']);
    Route::delete('/{id}', [DepartmentController::class, 'destroy']);
});

Route::prefix('users')->group(function(){
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::prefix('invoices')->group(function(){
    Route::get('/', [InvoiceController::class, 'index']);
    Route::get('/{id}', [InvoiceController::class, 'show']);
    Route::get('/{id}/print', [InvoiceController::class, 'print']);
});

Route::prefix('vendors')->group(function(){
    Route::get('/', [VendorController::class, 'index']);
    Route::get('/{id}', [VendorController::class, 'show']);
    Route::post('/', [VendorController::class, 'store']);
    Route::put('/{id}', [VendorController::class, 'update']);
    Route::delete('/{id}', [VendorController::class, 'destroy']);
});

Route::prefix('cost-types')->group(function(){
    Route::get('/', [CostTypeController::class, 'index']);
    Route::get('/{id}', [CostTypeController::class, 'show']);
    Route::post('/', [CostTypeController::class, 'store']);
    Route::put('/{id}', [CostTypeController::class, 'update']);
    Route::delete('/{id}', [CostTypeController::class, 'destroy']);
});

Route::get('/export-invoice/{id}', [ExportInvoiceController::class, 'download']);