@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-3">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ url('/home/new-customer-invoices') }}" class="btn btn-danger form-control">Nova faktura kupca</a>
                <hr>
                <h3>Evidentiraj uplatu kupca</h3>
                <form action="{{ route('home.addCustomerPayment') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label for="invoice-id">Id fakture</label>
                            <input type="number" name="invoice_id" id="invoice-id" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="customer">Izaberi kupca:</label>
                            <select class="form-control" name="customer" id="customer" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id}}">{{ $customer->customer }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label for="invoice-payment">Iznos uplate</label>
                    <input type="number" step=".01" name="invoice_payment" id="invoice-payment" class="form-control"
                           min="1" required>
                    <button type="submit" class="btn btn-danger form-control mt-4">Snimi</button>
                </form>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
            </div>
            <div class="col-1"></div>
            <div class="col-8">
                <h2>Sve fakture</h2>
                <table class="table text-center">
                    <thead>
                    <tr>
                        <th scope="col">Id fakt.</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Dobavljač</th>
                        <th scope="col">Otpr/faktura</th>
                        <th scope="col">Iznos</th>
                        <th scope="col">Uk. plaćeno</th>
                        <th scope="col">Ostatak</th>
                        <th></th>
                    </tr>
                    </thead>
                    @foreach($all_invoices as $invoice)
                        <tbody>
                        @if($invoice->invoice_amount - \App\Models\CustomerPayment::where('customer_invoice_id', $invoice->id)->where('customer_id', $invoice->customer_id)->sum('invoice_payment')  <= 0)
                            <tr style="background-color: #C8FFC6" >
                                <th scope="row">{{ $invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td><a href="{{ route('home.oneCustomerInvoices', ['id'=>$invoice->customer_id]) }}"
                                       style="text-decoration: none" title="Sve fakture kupca">{{ \App\Models\Customer::find($invoice->customer_id)->customer }}</a>
                                </td>
                                <td><a href="{{ route('home.invoice', ['id'=>$invoice->id]) }}"
                                       style="text-decoration: none;" title="Pogledaj fakturu">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\CustomerPayment::where('customer_invoice_id', $invoice->id)->where('customer_id', $invoice->customer_id)->sum('invoice_payment') }}</td>
                                <td style="color: red">{{ $invoice->invoice_amount - $paid }}</td>
                                <td><a href="{{ route('home.editInvoiceData',['id'=>$invoice->id]) }}"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Promeni</a>
                                </td>
                            </tr>
                            @else
                            <tr>
                                <th scope="row">{{ $invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td><a href="{{ route('home.oneCustomerInvoices', ['id'=>$invoice->customer_id]) }}"
                                       style="text-decoration: none" title="Sve fakture kupca">{{ \App\Models\Customer::find($invoice->customer_id)->customer }}</a>
                                </td>
                                <td><a href="{{ route('home.invoice', ['id'=>$invoice->id]) }}"
                                       style="text-decoration: none;" title="Pogledaj fakturu">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\CustomerPayment::where('customer_invoice_id', $invoice->id)->where('customer_id', $invoice->customer_id)->sum('invoice_payment') }}</td>
                                <td style="color: red">{{ $invoice->invoice_amount - $paid }}</td>
                                <td><a href="{{ route('home.editInvoiceData',['id'=>$invoice->id]) }}"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Promeni</a>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

    </div>



@endsection


