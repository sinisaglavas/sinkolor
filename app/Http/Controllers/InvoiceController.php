<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Models\Customer;
>>>>>>> e4bbb5e (kreiranje kupaca)
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
<<<<<<< HEAD
    //
=======
    public function customerInvoices()
    {
        $customers = Customer::all();

        return view('home.newCustomerInvoiceData', compact('customers'));
    }
>>>>>>> e4bbb5e (kreiranje kupaca)
}
