@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-3">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ url('/home/new-invoice-data') }}" class="btn btn-secondary form-control mb-2">Napravi novu
                    fakturu</a>
                <a href="{{ route('home.totalDebt') }}" class="btn btn-secondary form-control mb-4">Dobavljači - Ukupan
                    dug</a>
                <hr>
                <h5>Uplata po fakturi dobavljača</h5>
                <form action="{{ route('home.addPayment') }}" method="post">
                    @csrf
                    <div class="col">
                        <label for="supplier-id">Dobavljač</label>
                        @if(isset($supplier))
                            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                            <input type="text" name="supplier" id="supplier-id" class="form-control" value="{{ $supplier->supplier }}" readonly required>
                        @else
                            <input type="text" name="supplier_id" id="supplier-id" class="form-control" readonly required>
                        @endif
                    </div>
                    <div class="col">
                        @if(isset($invoice))
                            <input type="hidden" name="invoice_id" id="invoice-id" class="form-control" value="{{ $invoice->id }}" readonly required>
                        @else
                            <input type="hidden" name="invoice_id" id="invoice-id" class="form-control" readonly required>
                        @endif
                    </div>
                    <div class="col">
                        <label for="invoice-number">Broj fakture</label>
                        @if(isset($invoice))
                            <input type="text" id="invoice-number" class="form-control" value="{{ $invoice->invoice_number }}" readonly required>
                        @else
                            <input type="text" id="invoice-number" class="form-control" readonly required>
                        @endif
                    </div>
                    <label for="invoice-payment">Iznos uplate</label>
                    <input type="number" step=".01" name="invoice_payment" id="invoice-payment" class="form-control"
                           min="1" required>
                    <button type="submit" class="btn btn-secondary form-control mt-4">Snimi</button>
                    <div style="font-size: 10px;">Upozorenje:<br>
                        Uplata neće biti evidentirana ako nisu popunjena sva polja.<br>
                        Polja 'Dobavljač' i 'Broj fakture' se popunjavaju klikom na plavo dugme 'Plati'.
                    </div>
                </form>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
            </div>
            <div class="col-9 ps-3">
                <h5 class="text-md-center">S V I &nbsp;U L A Z I &nbsp;D O B A V LJ A Č A</h5>
                <table class="table text-center">
                    <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Datum</th>
                        <th scope="col">Dobavljač</th>
                        <th scope="col">Otpr/faktura</th>
                        <th scope="col">Iznos</th>
                        <th scope="col">Plaćeno</th>
                        <th scope="col">Ostatak</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    @foreach($all_invoices as $invoice)
                        <tbody>
                        @if($invoice->invoice_amount - \App\Models\Payment::where('invoice_id', $invoice->id)->where('supplier_id', $invoice->supplier_id)->sum('invoice_payment')  <= 0)
                            <tr class="align-middle" style="background-color: #C8FFC6">
                                <th scope="row">{{ $invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td><a href="{{ route('home.supplier_invoices', ['id'=>$invoice->supplier_id]) }}"
                                       style="text-decoration: none" title="Sve fakture dobavljača">{{ \App\Models\Supplier::find($invoice->supplier_id)->supplier }}</a>
                                </td>
                                <td style="width: 20%;"><a href="{{ route('home.invoice', ['id'=>$invoice->id]) }}"
                                       style="text-decoration: none;" title="Pogledaj fakturu">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\Payment::where('invoice_id', $invoice->id)->where('supplier_id', $invoice->supplier_id)->sum('invoice_payment') }}</td>
                                <td>{{ $invoice->invoice_amount - $paid }}</td>
                                <td><a href="{{ route('home.editInvoiceData',['id'=>$invoice->id]) }}"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Promeni</a>
                                </td>
                                <td class="border border-1 text-center"><a href="{{ route('markInvoice', ['id'=>$invoice->id]) }}" class="btn btn-sm btn-primary">Plati</a></td>
                            </tr>
                        @else
                            <tr class="align-middle">
                                <th scope="row">{{ $invoice->id }}</th>
                                <td>{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('d. M. Y.') }}</td>
                                <td><a href="{{ route('home.supplier_invoices', ['id'=>$invoice->supplier_id]) }}"
                                       style="text-decoration: none;">{{ \App\Models\Supplier::find($invoice->supplier_id)->supplier }}</a>
                                </td>
                                <td><a href="{{ route('home.invoice', ['id'=>$invoice->id]) }}"
                                       style="text-decoration: none;">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->invoice_amount }}</td>
                                <td>{{ $paid = \App\Models\Payment::where('invoice_id', $invoice->id)->where('supplier_id', $invoice->supplier_id)->sum('invoice_payment') }}</td>
                                <td style="color: red">{{ $invoice->invoice_amount - $paid }}</td>
                                <td><a href="{{ route('home.editInvoiceData',['id'=>$invoice->id]) }}"
                                       class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Promeni</a>
                                </td>
                                <td class="border border-1 text-center"><a href="{{ route('markInvoice', ['id'=>$invoice->id]) }}" class="btn btn-sm btn-primary">Plati</a></td>
                            </tr>
                        @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

    </div>



@endsection

