<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function customerInvoices()
    {
        $customers = Customer::all();

        return view('home.newCustomerInvoiceData', compact('customers'));
    }
}
