@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control">Lager</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control">Sve fakture</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ route('home.newInvoice') }}" class="btn btn-secondary form-control">Nova faktura dobavljača</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ url('/home/turnover-by-days') }}" class="btn btn-secondary form-control">Izlaz robe -
                            Promet</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ route('home.totalDebt') }}" class="btn btn-secondary form-control">Dobavljači - Ukupan dug</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ url('/home/new-customer-invoices') }}" class="btn btn-danger form-control">Nova faktura kupca</a>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <a href="{{ route('home.allCustomerInvoices') }}" class="btn btn-danger form-control mb-5">Sve fakture kupaca</a>
                    </div>
                </div>
            </div>
            <div class="col-1"></div>
            <div class="col-7 mt-2">
                <img src="{{ asset('images/sin-kolor-logo-2.jpg') }}" alt="Slika" style="width: 800px; border-radius: 10px">
            </div>
        </div>



    </div>
@endsection
