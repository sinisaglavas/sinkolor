<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function customerInvoices()
    {
        $customers = Customer::all();

        return view('home.newCustomerInvoiceData', compact('customers'));
    }

    public function markInvoice($id)
    {
        $invoice = Invoice::find($id);
        $supplier = Supplier::find($invoice->supplier_id);

        // potrebno za vracanje na allInvoices.blade.php
        $all_invoices = Invoice::all();
        $suppliers = Supplier::all();

        return view('home.allInvoices', compact('invoice', 'supplier', 'all_invoices', 'suppliers'));

    }

}
