<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Models\Stock;
>>>>>>> e4bbb5e (kreiranje kupaca)
use Illuminate\Http\Request;

class StockController extends Controller
{
<<<<<<< HEAD
    //
=======
    public function stock()
    {
        $all_stocks = Stock::query()
            ->select('code', 'article', 'unit', 'purchase_price', 'margin', 'price', 'pcs', 'sum')
            ->get();

        return view('home.stock', compact('all_stocks'));
    }
>>>>>>> e4bbb5e (kreiranje kupaca)
}
