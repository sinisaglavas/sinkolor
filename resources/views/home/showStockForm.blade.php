@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control">Lager</a>
                <h5 class="mt-5">Poslednji uneti artikal: </h5>
                <h5 class="mt-2">Šifra: {{ \App\Models\Stock::orderBy('id', 'DESC')->first()->code }}</h5>
                <h5 class="mt-2">Artikal: {{ \App\Models\Stock::orderBy('id', 'DESC')->first()->article }}</h5>
                <hr>
            </div>
            <div class="col-1"></div>
            <div class="col-6">
                <h2>Novi Artikal</h2>
                <form action="{{ route('saveStock') }}" method="POST">
                    @csrf
                    <label for="code">Sifra</label>
                    <input type="number" name="code" id="code" placeholder="Unos je obavezan" class="form-control" min="1"

                          value="{{ \App\Models\Stock::orderBy('id', 'DESC')->first()->code + 1 }}" required>
                    <label for="article">Artikal</label>
                    <input type="text" name="article" id="article" placeholder="Unos je obavezan" class="form-control" required>
                    <label for="unit">Jedinica Mere</label>
                    <select name="unit" id="unit" class="form-control">
                        <option value="kom">kom</option>
                        <option value="kg">kg</option>
                        <option value="l">l</option>
                        <option value="m'">m'</option>
                        <option value="m2">m2</option>
                        <option value="m3">m3</option>
                    </select>
                    <label for="price">Cena prodajna</label>
                    <input type="number" name="price" placeholder="Unos je obavezan" class="form-control" min="1" id="price" required>
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
