<?php

declare(strict_types=1);

use App\Http\Controllers\Operations\AuthController;
use App\Http\Controllers\Operations\QuotationController;
use App\Http\Controllers\Operations\ServiceRequestController;
use App\Http\Controllers\Operations\StatusController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/operations');

Route::middleware('guest')->group(function (): void {
    Route::get('/operations/login', [AuthController::class, 'showLogin'])->name('operations.login');
    Route::post('/operations/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('operations.login.submit');
});

Route::middleware('auth')->prefix('operations')->name('operations.')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [ServiceRequestController::class, 'index'])->name('index');
    Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{serviceRequest}/quotations', [QuotationController::class, 'store'])
        ->name('requests.quotations.store');
    Route::patch('/requests/{serviceRequest}/status', [StatusController::class, 'update'])
        ->name('requests.status.update');
});
