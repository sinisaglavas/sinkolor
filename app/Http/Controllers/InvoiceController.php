<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
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
        $invoice_amount = $invoice->invoice_amount; // iznos fakture
        $payments = Payment::where('invoice_id', $invoice->id)->
                    where('supplier_id', $invoice->supplier_id)->sum('invoice_payment'); // ukupno placen iznos za fakturu
        $rest = $invoice_amount - $payments; // preostali iznos za placanje

        // potrebno za vracanje na allInvoices.blade.php
        $all_invoices = Invoice::all();
        $suppliers = Supplier::all();

        return view('home.allInvoices', compact('invoice', 'supplier', 'rest', 'all_invoices', 'suppliers'));

    }

}
