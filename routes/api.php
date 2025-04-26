<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Stock;
use App\Models\Output;
<<<<<<< HEAD
=======
use App\Models\Invoice;
>>>>>>> e4bbb5e (kreiranje kupaca)
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/stock/{q}', function ($q, Request $request){
    $all_stock = Stock::where('code', 'LIKE', '%'.$q.'%')->orWhere('article', 'LIKE', '%'.$q.'%')->get();
    return $all_stock;
});
Route::get('/output/{q}', function ($q, Request $request){
   $target_output = Output::where('date_of_turnover', $q)->get();
   return $target_output;
});
Route::get('/code/{q}', function ($q, Request $request) {
    $code = Stock::where('code', 'LIKE', $q)->get();
    return $code;
});
<<<<<<< HEAD
=======
Route::get('/invoice/{q}', function ($q, Request $request) {
   $invoice = Invoice::where('invoice_number', 'LIKE', $q)->with('supplier')->first();
   return $invoice;
});

>>>>>>> e4bbb5e (kreiranje kupaca)
