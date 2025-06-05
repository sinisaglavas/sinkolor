@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
                <a href="{{ url('/home/new-customer-invoices') }}" class="btn btn-danger form-control mt-3">Nova faktura kupca</a>
            </div>
            <div class="col-2"></div>
            <div class="col-5">
                <h2>Novi Kupac</h2>
                <form action="{{ url('save-customer') }}" method="POST">
                    @csrf
                    <label for="customer">Kupac</label>
                    <input type="text" name="customer" id="customer" placeholder="Naziv kupca - Unos je obavezan" class="form-control" required>
                    <label for="address">Adresa</label>
                    <input type="text" name="address" id="address" placeholder="Adresa kupca - Opciono" class="form-control">
                    <div class="row">
                        <div class="col">
                            <label for="postalCode">Poštanski broj</label>
                            <input type="number" name="postal_code" id="postalCode" placeholder="Unos je obavezan" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="city">Grad</label>
                            <input type="text" name="city" id="city" placeholder="Sedište kupca - Unos je obavezan" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="pib">Poreski identifikacioni broj</label>
                            <input type="tel" pattern="[0-9]{9}" name="pib" id="pib" placeholder="PIB - Unos 9 cifara je obavezan" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="mb">Matični broj</label>
                            <input type="tel" pattern="[0-9]{8}" name="mb" id="mb" placeholder="MB - Unos 8 cifara je obavezan" class="form-control" required>
                        </div>
                    </div>
                    <label for="phone">Kontakt tel.</label>
                    <input type="tel" pattern="[0-9]{6,12}" name="phone" id="phone" placeholder="Unesi 6 do 12 cifara (sve spojeno) - Opciono" class="form-control">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email - Opciono" class="form-control">
                    <label for="jbkjs">JBKJS - za budžetske korisnike</label>
                    <input type="number" name="jbkjs" id="jbkjs" placeholder="Jedinstveni broj za budžetske korisnike - Opciono" class="form-control">
                    <button type="submit" class="btn btn-secondary form-control mt-4">Snimi</button>
                </form>
                @if(session()->has('message'))
                    <div class="alert alert-success text-center">
                        {{ session()->get('message') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


