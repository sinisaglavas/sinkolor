<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function stock()
    {
        $all_stocks = Stock::query()
            ->select('code', 'article', 'unit', 'purchase_price', 'purchase_price_sum', 'margin', 'price', 'pcs', 'sum')
            ->get();

//        $stock = Stock::where('code', $request->code)->first();
//        $stock->pcs = $stock->pcs + $request->pcs;
//        $stock->purchase_price = $request->purchase_price * (1-$request->rebate / 100) * (1-$request->discount / 100) * (1 + $request->tax / 100);
//
//        $stock->price = $request->price;
//        $stock->margin = ($stock->price / $stock->purchase_price -1) * 100;
//        $stock->margin = round($stock->margin, 2);
//        $stock->sum = $stock->pcs * $stock->price;
//        $stock->update();

        return view('home.stock', compact('all_stocks'));
    }
}
