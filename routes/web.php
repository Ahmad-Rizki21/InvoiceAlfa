<?php

use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => 'printing'], function() {
    Route::get('/print/invoice/{id}', [InvoiceController::class, 'printInvoice'])->name('print.invoice');
    Route::get('/print/receipt/{id}', [InvoiceController::class, 'printReceipt'])->name('print.receipt');
});

Route::get('/debug-ws', [ConsoleController::class, 'debugWs'])->name('debug.ws');

Route::get('/{any?}', [ConsoleController::class, 'index'])->where('any', '^(?!api).*$')->name('home');
