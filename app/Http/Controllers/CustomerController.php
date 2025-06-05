<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerOutput;
use App\Models\CustomerPayment;
use App\Models\Entrance;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\Supplier;
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
        $new_customer->postal_code = $request->postal_code;
        $new_customer->city = $request->city;
        $new_customer->pib = $request->pib;
        $new_customer->mb = $request->mb;
        $new_customer->phone = $request->phone;
        $new_customer->email = $request->email;
        $new_customer->jbkjs = $request->jbkjs;
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

    public function allCustomerInvoices()
    {
        $customers = Customer::all();
        $all_invoices = DB::table('customer_invoices')
            ->orderBy('id', 'asc')
            ->get(); // sve fakture kupaca

        return view('home.allCustomerInvoices', compact('customers','all_invoices'));
    }

    public function oneCustomerInvoices($id)
    {
        $customer_invoices = CustomerInvoice::where('customer_id', $id)->get();
        $customer = Customer::find($id);

        return view('home.oneCustomerInvoices', compact('customer_invoices', 'customer'));

    }

    public function customerInvoice($id)
    {
        $invoice = CustomerInvoice::find($id);
        $outputs = CustomerOutput::where('invoice_id', $id)->get();
        $total_per_invoice = CustomerOutput::where('invoice_id', $id)->sum('sum');

        return view('home.showCustomerEntranceForm', compact('invoice', 'outputs', 'total_per_invoice'));
    }

    public function addCustomerPayment(Request $request)
    {
        $new_customer_payment = new CustomerPayment();
        $new_customer_payment->invoice_payment = $request->invoice_payment;
        $new_customer_payment->customer_invoice_id = $request->invoice_id;
        $new_customer_payment->customer_id = $request->customer_id;
        $new_customer_payment->save();

        return redirect()->back()->with('message', 'Uplata je snimljena');
    }

    public function justDeleteArticle($id, $code, $invoice_id) // metoda za brisanje ulaza robe kupca
    {
        $delete_article = CustomerOutput::find($id); // artikal sa svim parametrima koji se brise
        $delete_article->delete();
        $invoice = CustomerInvoice::find($invoice_id);
        $outputs = CustomerOutput::where('invoice_id', $invoice_id)->get();
        $total_per_invoice = CustomerOutput::where('invoice_id', $invoice_id)->sum('sum');
        //return redirect()->route('requestedDay', ['date'=>$search_date]);
        //return redirect()->back()->with('message', 'Artikal je obrisan iz prometa i vracen ponovo na stanje lagera');
        return view('home.showCustomerEntranceForm', compact('invoice', 'outputs', 'total_per_invoice'));

    }

    public function markCustomerInvoice($id)
    {
        $customer_invoice = CustomerInvoice::find($id);
        $customer = Customer::find($customer_invoice->customer_id);
        $invoice_amount = $customer_invoice->invoice_amount; // iznos fakture
        $payments = CustomerPayment::where('customer_invoice_id', $customer_invoice->id)->
        where('customer_id', $customer_invoice->customer_id)->sum('invoice_payment'); // ukupno placen iznos za fakturu
        $rest = $invoice_amount - $payments; // preostali iznos za placanje

        // potrebno za vracanje na allInvoices.blade.php
        $all_invoices = DB::table('customer_invoices')
            ->orderBy('id', 'asc')
            ->get(); // sve fakture kupaca poredjane po id silazno
        $customers = Customer::all();

        return view('home.allCustomerInvoices', compact('customer_invoice', 'customer', 'rest', 'all_invoices', 'customers'));
    }

    public function editCustomerInvoiceData(Request $request, $id)
    {
        $invoice = CustomerInvoice::find($id);
        $customer = Customer::find($invoice->customer_id)->customer;

        return view('home.editCustomerInvoiceData', compact('invoice', 'customer'));

    }

    public function updateCustomerInvoice(Request $request, $id)
    {
        $invoice = CustomerInvoice::find($id);
        $request->validate([
            'invoice_number'=>'required',
            'invoice_amount'=>'required',
            'invoicing_date'=>'required'
        ]);
        $invoice->invoice_number = $request->invoice_number;
        $invoice->invoice_amount = $request->invoice_amount;
        $invoice->invoicing_date = $request->invoicing_date;
        $invoice->update();

        return redirect()->back()->with('message', 'Podaci su promenjeni');
    }


}
