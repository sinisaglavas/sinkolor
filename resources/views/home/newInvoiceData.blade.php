@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mt-4">
            <div class="col-4">
                <div class="row">
                    <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
                </div>
                <div class="row mt-2">
                    <a href="{{ url('/home/turnover-by-days') }}" class="btn btn-secondary form-control">Izlaz robe - Promet</a>
                </div>
                <div class="row mt-2">
                    <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control mb-5">Sve fakture</a>
                </div>
            </div>
            <div class="col-2"></div>
            <div class="col">
                <form action="{{ route('saveInvoice') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label for="supplier">Izaberi dobavljaca:</label>
                            <select class="form-control" name="supplier" id="supplier" required>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id}}">{{ $supplier->supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <a href="{{ url('/home/new-supplier') }}" class="btn btn-secondary form-control mt-4">Unesi novog dobavljača</a>
                        </div>
                    </div>
                    <div class="row">
                        <label for="invoicing_date">Datum ulaza:</label>
                        <input type="date" name="invoicing_date" class="form-control" id="invoicing_date" required>
                    </div>
                    <div class="row">
                        <label for="invoice_amount">Iznos sa racuna:</label>
                        <input type="number" step=".01" name="invoice_amount" class="form-control" id="invoice_amount" required>
                    </div>
                    <div class="row">
                        <label for="invoice_number">Otpremnica/faktura:</label>
                        <input type="text" name="invoice_number" class="form-control" id="invoice_number" required>
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-success form-control mt-3">Snimi</button>
                            </div>
                            <div class="col">
                                @if(isset($invoice))
                                    <a href="{{ route('showEntranceForm', ['id'=>$id]) }}" class="btn btn-warning form-control mt-3">{{ $invoice }}</a>
                                @endif
                            </div>
                        </div>

                    </div>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
            </div>
        </div>
    </div>



@endsection
