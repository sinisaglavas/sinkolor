@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mt-4">
            <div class="col-4">
                <div class="row">
                    <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
                </div>
                <div class="row mt-2">
                    <a href="{{ route('home.allCustomerInvoices') }}" class="btn btn-danger form-control mb-5">Sve fakture kupaca</a>
                </div>
            </div>
            <div class="col-2"></div>
            <div class="col-4">
                <form action="{{ url('save-customer-invoice') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col">
                            <label for="customer">Izaberi kupca:</label>
                            <select class="form-control" name="customer" id="customer" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id}}">{{ $customer->customer }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <a href="{{ url('/home/new-customer') }}" class="btn btn-danger form-control mt-4">Novi kupac</a>
                        </div>
                    </div>
                    <div class="row">
                        <label for="invoicing_date">Datum izlaza:</label>
                        <input type="date" name="invoicing_date" class="form-control" id="invoicing_date" required>
                    </div>
                    <div class="row">
                        <label for="invoice_amount">Iznos sa otpremnice:</label>
                        <input type="number" min="0" step=".01" name="invoice_amount" class="form-control" id="invoice_amount" required>
                    </div>
                    <div class="row">
                        <label for="invoice_number">Otpremnica:</label>
                        <input type="text" name="invoice_number" class="form-control" id="invoice_number" required>
                        <div class="row">
                            <div class="col">
                                <button class="btn btn-danger form-control mt-3">Snimi</button>
                            </div>
                            <div class="col">
                                @if(isset($invoice))
                                    <a href="{{ route('home.showCustomerEntranceForm', ['id'=>$id]) }}" class="btn btn-warning form-control mt-3">{{ $invoice }}</a>
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

