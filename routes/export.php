<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/invoices/export', [InvoiceController::class, 'export'])->name('invoices.export');
