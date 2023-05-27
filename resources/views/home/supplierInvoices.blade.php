@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-3">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control mb-2">Sve fakture</a>
                <a href="{{ route('home.totalDebt') }}" class="btn btn-secondary form-control">Dobavljači - Ukupan
                    dug</a>
                <hr>
                <h3>{{ $supplier->supplier }}</h3>
                <p>{{ $supplier->other_data }}</p>
                <hr>
                @if(isset($invoice) && isset($payments))
                    <h4> Faktura: {{ $invoice->invoice_number }}</h4>
                    <p>Iznos: &nbsp;&nbsp;<span class="fw-bold">{{ $invoice->invoice_amount }}</span></p>
                    @if((count($payments) != 0))
                        @foreach($payments as $payment)
                            <p>Uplata dobavljaču: &nbsp;&nbsp;<span
                                    class="fw-bold">{{ $payment->invoice_payment }}</span>
                                &nbsp;&nbsp;{{ \Carbon\Carbon::parse($payment->created_at)->format('d.m.Y.') }}</p>
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
                    @foreach($supplier_invoices as $supplier_invoice)
                        <tbody>
                        @if($supplier_invoice->invoice_amount - \App\Models\Payment::where('invoice_id', $supplier_invoice->id)->where('supplier_id', $supplier_invoice->supplier_id)->sum('invoice_payment')  <= 0)
                            <tr style="background-color: #C8FFC6">
                                <th scope="row">{{ $supplier_invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($supplier_invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td>{{ \App\Models\Supplier::find($supplier_invoice->supplier_id)->supplier }}</td>
                                <td><a href="{{ route('home.invoice_payment', ['id'=>$supplier_invoice->id]) }}"
                                       class="text-decoration-none">{{ $supplier_invoice->invoice_number }}</a></td>
                                <td>{{ $supplier_invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\Payment::where('invoice_id', $supplier_invoice->id)->where('supplier_id', $supplier_invoice->supplier_id)->sum('invoice_payment') }}</td>
                                <td>{{ $supplier_invoice->invoice_amount - $paid }}</td>
                            </tr>
                        @else
                            <tr>
                                <th scope="row">{{ $supplier_invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($supplier_invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td>{{ \App\Models\Supplier::find($supplier_invoice->supplier_id)->supplier }}</td>
                                <td><a href="{{ route('home.invoice_payment', ['id'=>$supplier_invoice->id]) }}"
                                       class="text-decoration-none">{{ $supplier_invoice->invoice_number }}</a></td>
                                <td>{{ $supplier_invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\Payment::where('invoice_id', $supplier_invoice->id)->where('supplier_id', $supplier_invoice->supplier_id)->sum('invoice_payment') }}</td>
                                <td style="color: red">{{ $supplier_invoice->invoice_amount - $paid }}</td>
                            </tr>
                        @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>


    </div>


@endsection


