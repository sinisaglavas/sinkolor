<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function stock()
    {
        $all_stocks = Stock::query()
            ->select('code', 'article', 'unit', 'purchase_price', 'margin', 'price', 'pcs', 'sum')
            ->get();

        return view('home.stock', compact('all_stocks'));
    }
}
