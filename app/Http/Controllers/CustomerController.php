<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerOutput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    public function newCustomer()
    {
        return view('home.newCustomer');
    }

    public function saveCustomer(Request $request)
    {
        $new_customer = new Customer();
        $new_customer->customer = $request->customer;
        $new_customer->address = $request->address;
        $new_customer->city = $request->city;
        $new_customer->pib = $request->pib;
        $new_customer->mb = $request->mb;
        $new_customer->phone = $request->phone;
        $new_customer->save();

        return redirect()->back()->with('message', 'Novi kupac je snimljen');

    }

    public function saveCustomerInvoice(Request $request)
    {
        $new_customer_invoice = new CustomerInvoice();
        $new_customer_invoice->invoice_number = $request->invoice_number;
        $new_customer_invoice->invoice_amount = $request->invoice_amount;
        $new_customer_invoice->invoicing_date = $request->invoicing_date;
        $new_customer_invoice->customer_id = $request->customer;
        $new_customer_invoice->save();

        $customers = Customer::all();
        $invoice = 'Unesi artikle sa fakture';
        $id = CustomerInvoice::where('invoice_number', $request->invoice_number)->latest()->first()->id;

        Session::flash('message','Osnovni podaci su snimljeni, sada unesite artikle');

        return view('home.newCustomerInvoiceData', compact('customers', 'invoice', 'id'));

    }

    public function showCustomerEntranceForm($id)
    {
        $invoice = CustomerInvoice::find($id);
        $outputs = CustomerOutput::where('invoice_id', $id)->get();
        $total_per_invoice = CustomerOutput::where('invoice_id', $id)->sum('sum');

        return view('home.showCustomerEntranceForm', compact('invoice', 'outputs', 'total_per_invoice'));

    }

    public function saveCustomerOutput(Request $request)
    {
        $new_customer_output = new CustomerOutput();
        $new_customer_output->code = $request->code;
        $new_customer_output->article = $request->article;
        $new_customer_output->pcs = $request->pcs;
        $new_customer_output->price = $request->price;
        $new_customer_output->sum = $request->sum;
        $new_customer_output->invoice_id = $request->id;
        $new_customer_output->save();

        $invoice = CustomerInvoice::find($request->id);
        $outputs = CustomerOutput::where('invoice_id', $request->id)->get();
        $total_per_invoice = CustomerOutput::where('invoice_id', $request->id)->sum('sum');

        return view('home.showCustomerEntranceForm', compact('invoice', 'outputs', 'total_per_invoice'));

    }

    public function invoiceReview($id)
    {
        $invoice = CustomerInvoice::find($id);
        $customer = Customer::find($invoice->customer_id);
        $outputs = CustomerOutput::where('invoice_id', $invoice->id)->get();
        $total_per_invoice = CustomerOutput::where('invoice_id', $invoice->id)->sum('sum');
        return view('home.invoiceReview', compact('customer', 'outputs', 'total_per_invoice', 'invoice'));
    }

    public function allCustomerInvoices()
    {
        $customers = Customer::all();
        $all_invoices = DB::table('customer_invoices')
            ->orderBy('id', 'asc')
            ->get();

        return view('home.allCustomerInvoices', compact('customers','all_invoices'));
    }

}
