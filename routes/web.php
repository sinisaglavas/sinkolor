<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home/new-invoice-data', [App\Http\Controllers\HomeController::class, 'newInvoice'])->name('home.newInvoice');

Route::get('/home/stock', [App\Http\Controllers\HomeController::class, 'stock'])->name('home.stock');

Route::get('/home/show-stock-form', function (){ return view('home.showStockForm'); });

Route::get('/stock/{id}/edit', [App\Http\Controllers\HomeController::class, 'editStock']);
Route::put('/stock/{id}/edit', [App\Http\Controllers\HomeController::class, 'updateStock'])->name('home.updateStock');

Route::get('/home/show-entrance-form/{id}', [App\Http\Controllers\HomeController::class, 'showEntranceForm'])
    ->name('showEntranceForm');
Route::get('/home/all-invoices', [App\Http\Controllers\HomeController::class, 'all_invoices'])->name('home.all_invoices');
Route::get('/home/invoice/{id}', [App\Http\Controllers\HomeController::class, 'invoice'])->name('home.invoice');
Route::get('/home/turnover', function () { return view('home.turnover'); });
Route::get('/home/turnover-by-days', [App\Http\Controllers\HomeController::class, 'turnover_by_days'])->name('home.turnoverByDays');
Route::get('/requested-day', [App\Http\Controllers\HomeController::class, 'requestedDay'])->name('home.requestedDay');
Route::get('/requested-day-2/{search_date}', [App\Http\Controllers\HomeController::class, 'requestedDay2'])->name('home.requestedDay2');
Route::get('/update/{id}/{code}/{invoice_id}/before-delete-entrance', [App\Http\Controllers\HomeController::class, 'updateBeforeDelete'])->name('home.updateBeforeDelete');
Route::get('/update/{id}/{search_date}/before-delete-output', [App\Http\Controllers\HomeController::class, 'updateBeforeDelete2'])->name('home.updateBeforeDelete2');
Route::get('/home/total-debt', [App\Http\Controllers\HomeController::class, 'totalDebt'])->name('home.totalDebt');
Route::get('/home/new-supplier', function (){ return view('home.newSupplier'); });
Route::get('/home.supplier-invoices/{id}', [App\Http\Controllers\HomeController::class, 'supplier_invoices'])->name('home.supplier_invoices');

Route::get('/home/get-month/{id}', [App\Http\Controllers\HomeController::class, 'getMonth'])->name('home.getMonth');
Route::get('/home/invoice-payment/{id}', [App\Http\Controllers\HomeController::class, 'invoice_payment'])->name('home.invoice_payment');

Route::get('/home/edit-invoice-data/{id}', [App\Http\Controllers\HomeController::class, 'editInvoiceData'])->name('home.editInvoiceData');
Route::put('/invoice/{id}/edit', [App\Http\Controllers\HomeController::class, 'updateInvoice'])->name('updateInvoice');

Route::post('/home/add-supplier', [App\Http\Controllers\HomeController::class, 'add_supplier'])->name('home.addSupplier');
Route::post('/save-stock',[App\Http\Controllers\HomeController::class,'saveStock'])->name('saveStock');
Route::post('/save-invoice', [App\Http\Controllers\HomeController::class, 'saveInvoice'])->name('saveInvoice');
Route::post('/save-entrance/{id}', [App\Http\Controllers\HomeController::class, 'saveEntrance'])->name('saveEntrance');
Route::post('/home/save-output/{search_date}', [App\Http\Controllers\HomeController::class, 'saveOutput'])->name('home.saveOutput');
Route::post('/home/add-payment', [App\Http\Controllers\HomeController::class, 'addPayment'])->name('home.addPayment');
Route::post('/search-stock', [App\Http\Controllers\HomeController::class, 'searchStock'])->name('searchStock');
