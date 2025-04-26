@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-3">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ route('home.allCustomerInvoices') }}" class="btn btn-danger form-control mb-2">Sve fakture kupaca</a>
                <a href="{{ url('/home/new-customer-invoices') }}" class="btn btn-danger form-control mb-2">Nova faktura kupca</a>
                <hr>
                <h3>{{ $customer->customer }}</h3>
                <p class="m-0">{{ $customer->address }} , {{ $customer->city }}</p>
                <p class="m-0">PIB: {{ $customer->pib}} , MB: {{ $customer->mb }}</p>
                <p>Telefon: <span class="fw-bold">{{ $customer->phone }}</span></p>
                <hr>
                @if(isset($customer_invoice) && isset($customer_payments))
                    <h4> Faktura: {{ $customer_invoice->invoice_number }}</h4>
                    <p>Iznos: &nbsp;&nbsp;<span class="fw-bold">{{ $customer_invoice->invoice_amount }}</span></p>
                    @if((count($customer_payments) != 0))
                        @foreach($customer_payments as $customer_payment)
                            <p>Uplata kupca je evidentirana: &nbsp;&nbsp;<span
                                    class="fw-bold">{{ $customer_payment->invoice_payment }}</span>
                                &nbsp;&nbsp;{{ \Carbon\Carbon::parse($customer_payment->created_at)->format('d.m.Y.') }}</p>
                        @endforeach
                    @else
                        <p>Nema snimljenih uplata za ovu fakturu!</p>
                    @endif
                @endif
            </div>
            <div class="col-1"></div>
            <div class="col-8">
                <h2>Sve fakture dobavljača:</h2>
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
                    </tr>
                    </thead>
                    @foreach($customer_invoices as $customer_invoice)
                        <tbody>
                        @if($customer_invoice->invoice_amount - \App\Models\CustomerPayment::where('customer_invoice_id', $customer_invoice->id)->where('customer_id', $customer_invoice->customer_id)->sum('invoice_payment')  <= 0)
                            <tr style="background-color: #C8FFC6">
                                <th scope="row">{{ $customer_invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($customer_invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td>{{ \App\Models\Customer::find($customer_invoice->customer_id)->customer }}</td>
                                <td><a href="{{ route('home.customerInvoice', ['id'=>$customer_invoice->id]) }}" title="Pogledaj fakturu kupca"
                                       style="text-decoration: none;">{{ $customer_invoice->invoice_number }}</a></td>
                                <td><a href="{{ route('home.customerInvoicePayment', ['id'=>$customer_invoice->id]) }}" title="Klik na datum uplate kupca"
                                       class="text-decoration-none">{{ $customer_invoice->invoice_amount }}</a></td>
                                <td>{{ $paid = \App\Models\CustomerPayment::where('customer_invoice_id', $customer_invoice->id)->where('customer_id', $customer_invoice->customer_id)->sum('invoice_payment') }}</td>
                                <td>{{ $customer_invoice->invoice_amount - $paid }}</td>
                            </tr>
                        @else
                            <tr>
                                <th scope="row">{{ $customer_invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($customer_invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td>{{ \App\Models\Customer::find($customer_invoice->customer_id)->customer }}</td>
                                <td><a href="{{ route('home.customerInvoice', ['id'=>$customer_invoice->id]) }}" title="Pogledaj fakturu kupca"
                                       style="text-decoration: none;">{{ $customer_invoice->invoice_number }}</a></td>
                                <td><a href="{{ route('home.customerInvoicePayment', ['id'=>$customer_invoice->id]) }}" title="Klik na datum uplate kupca"
                                       class="text-decoration-none">{{ $customer_invoice->invoice_amount }}</a></td>
                                <td>{{ $paid = \App\Models\CustomerPayment::where('customer_invoice_id', $customer_invoice->id)->where('customer_id', $customer_invoice->customer_id)->sum('invoice_payment') }}</td>
                                <td>{{ $customer_invoice->invoice_amount - $paid }}</td>
                            </tr>
                        @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>


    </div>


@endsection



