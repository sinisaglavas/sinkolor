<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function customerInvoicePayment($id)
    {
        $customer_invoice = CustomerInvoice::find($id);
        $customer_payments = CustomerPayment::where('customer_invoice_id', $id)->get();
        $customer_invoices = CustomerInvoice::where('customer_id', $customer_invoice->customer_id)->get();
        $customer = Customer::find($customer_invoice->customer_id);

        return view('home.oneCustomerInvoices', compact('customer_invoice', 'customer_payments', 'customer_invoices', 'customer'));
    }
}
