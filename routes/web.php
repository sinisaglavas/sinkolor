<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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
Route::get('/home/edit-customer-invoice-data/{id}', [App\Http\Controllers\CustomerController::class, 'editCustomerInvoiceData'])->name('home.editCustomerInvoiceData');
Route::get('/home/descending-article/{search_date}', [App\Http\Controllers\HomeController::class, 'descendingArticle'])->name('home.descendingArticle');

Route::put('/invoice/{id}/edit', [App\Http\Controllers\HomeController::class, 'updateInvoice'])->name('updateInvoice');
Route::put('/customer-invoice/{id}/edit', [App\Http\Controllers\CustomerController::class, 'updateCustomerInvoice'])->name('updateCustomerInvoice');

Route::post('/home/add-supplier', [App\Http\Controllers\HomeController::class, 'add_supplier'])->name('home.addSupplier');
Route::post('/save-stock',[App\Http\Controllers\HomeController::class,'saveStock'])->name('saveStock');
Route::post('/save-invoice', [App\Http\Controllers\HomeController::class, 'saveInvoice'])->name('saveInvoice');
Route::post('/save-entrance/{id}', [App\Http\Controllers\HomeController::class, 'saveEntrance'])->name('saveEntrance');
Route::post('/home/save-output/{search_date}', [App\Http\Controllers\HomeController::class, 'saveOutput'])->name('home.saveOutput');
Route::post('/home/add-payment', [App\Http\Controllers\HomeController::class, 'addPayment'])->name('home.addPayment');
Route::post('/search-stock', [App\Http\Controllers\HomeController::class, 'searchStock'])->name('searchStock');

Route::get('/home/stock', [App\Http\Controllers\StockController::class, 'stock'])->name('home.stock')->middleware('auth');


Route::get('/home/new-customer-invoices', [App\Http\Controllers\InvoiceController::class, 'customerInvoices'])->middleware('auth');
Route::get('/home/new-customer', [App\Http\Controllers\CustomerController::class, 'newCustomer'])->middleware('auth');
Route::post('save-customer', [App\Http\Controllers\CustomerController::class, 'saveCustomer'])->middleware('auth');
Route::post('save-customer-invoice', [App\Http\Controllers\CustomerController::class, 'saveCustomerInvoice'])->middleware('auth');
Route::get('home/show-customer-entrance-form/{id}', [App\Http\Controllers\CustomerController::class, 'showCustomerEntranceForm'])->middleware('auth')
    ->name('home.showCustomerEntranceForm');
Route::post('save-customer-output', [App\Http\Controllers\CustomerController::class, 'saveCustomerOutput'])->middleware('auth')->name('saveCustomerOutput');
Route::post('/home/add-customer-payment', [App\Http\Controllers\CustomerController::class, 'addCustomerPayment'])->name('home.addCustomerPayment');

Route::middleware(['auth'])->group(function () {
    Route::get('/home/all-customer-invoices', [App\Http\Controllers\CustomerController::class, 'allCustomerInvoices'])->middleware('auth')->name('home.allCustomerInvoices');
    Route::get('/home/one-customer-invoices/{id}', [App\Http\Controllers\CustomerController::class, 'oneCustomerInvoices'])->middleware('auth')->name('home.oneCustomerInvoices');
    Route::get('/home/customer-invoice-payment/{id}', [App\Http\Controllers\CustomerPaymentController::class, 'customerInvoicePayment'])->middleware('auth')->name('home.customerInvoicePayment');
    Route::get('/home/customer-invoice/{id}', [App\Http\Controllers\CustomerController::class, 'customerInvoice'])->middleware('auth')->name('home.customerInvoice');
    Route::get('/just-delete-article/{id}/{code}/{invoice_id}', [App\Http\Controllers\CustomerController::class, 'justDeleteArticle'])->name('justDeleteArticle');
    Route::get('/mark-customer-invoice/{id}', [App\Http\Controllers\CustomerController::class, 'markCustomerInvoice'])->name('markCustomerInvoice')->middleware('auth');
    Route::get('/generate-prescription/{id}', [App\Http\Controllers\PrescriptionController::class, 'generatePDF'])->
    name('generatePDF');
    Route::get('/generate-codebook-pdf', [App\Http\Controllers\PrescriptionController::class, 'generateCodebookPDF'])->
    name('generateCodebookPDF');
// Invoices controller
    Route::get('/mark-invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'markInvoice'])->name('markInvoice')->middleware('auth');
 // EfakturaLogController
    Route::get('/efaktura-logs', [App\Http\Controllers\EfakturaLogController::class, 'index'])->name('efakturaLogs');
    Route::post('/efaktura-resend/{invoice}', [\App\Http\Controllers\EfakturaLogController::class, 'resend'])->name('efaktura.resend');
    Route::post('/send-to-sef/{id}', [App\Http\Controllers\EfakturaLogController::class, 'sendToSef'])->name('sendToSef');

    Route::get('/test-xml/{invoiceId}', [App\Http\Controllers\EfakturaLogController::class, 'testXml']);


    Route::get('/test-efaktura', function () {
        try {
            //$response = Http::get('https://demoapi.efaktura.mfin.gov.rs');
            $response = Http::withOptions(['verify' => false])
                ->get('https://demoapi.efaktura.mfin.gov.rs');
            return response()->json([
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    });


});
