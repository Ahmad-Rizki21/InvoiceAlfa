<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\DistributionCenterController;
use App\Http\Controllers\FranchiseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RemoteLocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketNoteController;
use App\Http\Controllers\TicketTimerController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::group(['prefix' => '/auth', 'as' => 'auth.'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::patch('/me', [AuthController::class, 'updateMe']);
    });
});

Route::group(['prefix' => '/data', 'middleware' => 'auth:api', 'as' => 'data.'], function () {
    Route::get('/total-unpaid-amount', [ConsoleController::class, 'dataTotalUnpaidAmount'])->name('total-unpaid-amount');
    Route::get('/total-unpaid-customer', [ConsoleController::class, 'dataTotalUnpaidCustomer'])->name('total-unpaid-customer');
    Route::get('/total-pending-review', [ConsoleController::class, 'dataTotalPendingReview'])->name('total-pending-review');
    Route::get('/total-rejected', [ConsoleController::class, 'dataTotalRejected'])->name('total-rejected');
    Route::get('/working-clock/average', [ConsoleController::class, 'dataAverageWorkingClock'])->name('average-working-clock');
    Route::get('/working-clock/median', [ConsoleController::class, 'dataMedianWorkingClock'])->name('median-working-clock');
    Route::get('/total-users', [ConsoleController::class, 'dataTotalUsers'])->name('total-users');
    Route::get('/chart-ticket-timer', [ConsoleController::class, 'chartTicketTimer'])->name('chart-ticket-timer');
    Route::get('/chart-total-ticket', [ConsoleController::class, 'chartTotalTicket'])->name('chart-total-ticket');
});

Route::group(['prefix' => '/users', 'middleware' => 'auth:api', 'as' => 'users.'], function () {
    Route::get('/', [UsersController::class, 'index'])->name('index');
    Route::get('/{id}', [UsersController::class, 'show'])->name('show');
    Route::post('/', [UsersController::class, 'store'])->name('store');
    Route::patch('/{id}', [UsersController::class, 'update'])->name('update');
    Route::delete('/{id}', [UsersController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/distribution-centers', 'middleware' => 'auth:api', 'as' => 'distribution-center.'], function () {
    Route::get('/', [DistributionCenterController::class, 'index'])->name('index');
    Route::post('/import/upload', [DistributionCenterController::class, 'importUpload'])->name('import.upload');
    Route::post('/import/cache', [DistributionCenterController::class, 'importCache'])->name('import.cache');
    Route::post('/import/process', [DistributionCenterController::class, 'importProcess'])->name('import.process');
    Route::post('/import/fix', [DistributionCenterController::class, 'importFix'])->name('import.fix');
    Route::get('/import/errors', [DistributionCenterController::class, 'importErrors'])->name('import.errors');
    Route::delete('/import/row/{id}', [DistributionCenterController::class, 'importRowDelete'])->name('import.row.delete');
    Route::get('/{id}', [DistributionCenterController::class, 'show'])->name('show');
    Route::post('/', [DistributionCenterController::class, 'store'])->name('store');
    Route::patch('/{id}', [DistributionCenterController::class, 'update'])->name('update');
    Route::delete('/{id}', [DistributionCenterController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/franchises', 'middleware' => 'auth:api', 'as' => 'franchise.'], function () {
    Route::get('/', [FranchiseController::class, 'index'])->name('index');
    Route::post('/import/upload', [FranchiseController::class, 'importUpload'])->name('import.upload');
    Route::post('/import/cache', [FranchiseController::class, 'importCache'])->name('import.cache');
    Route::post('/import/process', [FranchiseController::class, 'importProcess'])->name('import.process');
    Route::post('/import/fix', [FranchiseController::class, 'importFix'])->name('import.fix');
    Route::get('/import/errors', [FranchiseController::class, 'importErrors'])->name('import.errors');
    Route::delete('/import/row/{id}', [FranchiseController::class, 'importRowDelete'])->name('import.row.delete');
    Route::get('/{id}', [FranchiseController::class, 'show'])->name('show');
    Route::post('/', [FranchiseController::class, 'store'])->name('store');
    Route::patch('/{id}', [FranchiseController::class, 'update'])->name('update');
    Route::delete('/{id}', [FranchiseController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/stores', 'middleware' => 'auth:api', 'as' => 'store.'], function () {
    Route::get('/', [StoreController::class, 'index'])->name('index');
    Route::post('/import/upload', [StoreController::class, 'importUpload'])->name('import.upload');
    Route::post('/import/cache', [StoreController::class, 'importCache'])->name('import.cache');
    Route::post('/import/process', [StoreController::class, 'importProcess'])->name('import.process');
    Route::post('/import/fix', [StoreController::class, 'importFix'])->name('import.fix');
    Route::get('/import/errors', [StoreController::class, 'importErrors'])->name('import.errors');
    Route::delete('/import/row/{id}', [StoreController::class, 'importRowDelete'])->name('import.row.delete');
    Route::get('/{id}', [StoreController::class, 'show'])->name('show');
    Route::post('/', [StoreController::class, 'store'])->name('store');
    Route::patch('/{id}', [StoreController::class, 'update'])->name('update');
    Route::delete('/{id}', [StoreController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/settings', 'middleware' => 'auth:api', 'as' => 'settings.'], function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::post('/', [SettingsController::class, 'store'])->name('store');
});

Route::group(['prefix' => '/invoices', 'middleware' => 'auth:api', 'as' => 'invoice.'], function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/active', [InvoiceController::class, 'active'])->name('active');
    Route::get('/template', [InvoiceController::class, 'template'])->name('template');
    Route::post('/export', [InvoiceController::class, 'requestExport'])->name('export.request');
    Route::post('/simple-import', [InvoiceController::class, 'simpleImport'])->name('simple-import');
    Route::post('/simple-import/upload', [InvoiceController::class, 'simpleImportUpload'])->name('simple-import.upload');
    Route::post('/payment-proofs', [InvoiceController::class, 'uploadPaymentProofs'])->name('payment-proof');
    Route::get('/payment-proofs', [InvoiceController::class, 'getPaymentProofs']);
    Route::post('/payment-proofs/delete', [InvoiceController::class, 'destroyPaymentProofs'])->name('payment-proof.destroy');
    Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
    Route::post('/', [InvoiceController::class, 'store'])->name('store');
    Route::patch('/{id}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/user-access', 'middleware' => 'auth:api', 'as' => 'user-access.'], function () {
    Route::group(['prefix' => 'roles', 'as' => 'role.'], function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::patch('/{id}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
    });
    Route::group(['prefix' => 'permissions', 'as' => 'permission.'], function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/{id}/role', [PermissionController::class, 'showForRole'])->name('show.role');
        Route::patch('/{id}/role', [PermissionController::class, 'updateForRole'])->name('update.role');
    });
});

Route::group(['prefix' => '/remote-locations', 'middleware' => 'auth:api', 'as' => 'remote-location.'], function () {
    Route::get('/', [RemoteLocationController::class, 'index'])->name('index');
    Route::get('/{id}', [RemoteLocationController::class, 'show'])->name('show');
    Route::post('/', [RemoteLocationController::class, 'store'])->name('store');
    Route::patch('/{id}', [RemoteLocationController::class, 'update'])->name('update');
    Route::delete('/{id}', [RemoteLocationController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => '/reports', 'middleware' => 'auth:api', 'as' => 'report.'], function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
});

Route::group(['prefix' => '/tickets', 'middleware' => 'auth:api', 'as' => 'tickets.'], function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('/{id}/latest-timer', [TicketController::class, 'latestTimer'])->name('latest-timer');
    Route::get('/{id}', [TicketController::class, 'show'])->name('show');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    Route::patch('/{id}', [TicketController::class, 'update'])->name('update');
    Route::delete('/{id}', [TicketController::class, 'destroy'])->name('destroy');

    Route::group(['prefix' => '/{ticketId}/notes', 'as' => 'notes.'], function () {
        Route::get('/', [TicketNoteController::class, 'index'])->name('index');
        Route::post('/', [TicketNoteController::class, 'store'])->name('store');
        Route::patch('/{id}', [TicketNoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [TicketNoteController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => '/{ticketId}/timers', 'as' => 'timers.'], function () {
        Route::get('/', [TicketTimerController::class, 'index'])->name('index');
        Route::post('/toggle', [TicketTimerController::class, 'toggle'])->name('toggle');
        Route::post('/complete', [TicketTimerController::class, 'complete'])->name('complete');
        Route::patch('/{id}', [TicketTimerController::class, 'update'])->name('update');
        Route::delete('/{id}', [TicketTimerController::class, 'destroy'])->name('destroy');
    });
});
