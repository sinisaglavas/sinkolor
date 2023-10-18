@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-3">Glavni meni</a>
                <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control mb-3">Lager</a>
                <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control">Sve fakture</a>
            </div>
            <div class="col-1"></div>
            <div class="col-6">
                <h2>Promeni Fakturu</h2>
                <form action="{{ route('updateInvoice', ['id'=>$invoice->id]) }}" method="POST">
                    @csrf
                    @method('put')
                    <label for="invoice_number">Broj fakture</label>
                    <input type="text" name="invoice_number" placeholder="Unos je obavezan" class="form-control"
                           value="{{ $invoice->invoice_number }}" id="invoice_number" required>
                    <label for="invoice_amount">Iznos fakture</label>
                    <input type="number" name="invoice_amount" placeholder="Unos je obavezan" class="form-control" min="1"
                           step=".01" value="{{ $invoice->invoice_amount }}" id="invoice_amount" required>
                    <label for="invoicing_date">Datum fakturisanja</label>
                    <input type="date" name="invoicing_date" placeholder="Unos je obavezan" class="form-control"
                           value="{{ $invoice->invoicing_date }}" id="invoicing_date" required>
                    <button type="submit" class="btn btn-secondary form-control mt-4">Snimi</button>
                </form>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

