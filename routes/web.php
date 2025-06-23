<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PaymentReceiptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('admin');
});


// Yoki qisqaroq
Route::get('/payment-receipt/{medicalHistory}', [PaymentReceiptController::class, 'generateReceipt'])
    ->name('payment.receipt');
    
Route::get('/payment-receipt/{medicalHistory}/view', [PaymentReceiptController::class, 'viewReceipt'])
    ->name('payment.receipt.view');

Route::get('/download-inspection/{id}', [InspectionController::class, 'downloadWord'])->name('download.inspection');
