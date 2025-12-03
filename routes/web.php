<?php

use App\Http\Controllers\PurchaseRequestApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    // Approval link from email (signed route)
    Route::get('/pr/approve/{id}', [PurchaseRequestApprovalController::class, 'approvalLink'])
        ->name('pr.approve.link');

    // Approval page (requires authentication)
    Route::middleware(['auth'])->group(function () {
        Route::get('/pr/approval/{id}', [PurchaseRequestApprovalController::class, 'approvalPage'])
            ->name('pr.approval.page');

        Route::post('/pr/approval/{id}', [PurchaseRequestApprovalController::class, 'processApproval'])
            ->name('pr.approval.process');

        Route::get('/pr/approval-success', [PurchaseRequestApprovalController::class, 'approvalSuccess'])
            ->name('pr.approval.success');
    });
});
