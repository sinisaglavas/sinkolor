@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control mb-2">Lager</a>
                <a href="{{ route('home.turnoverByDays') }}" class="btn btn-secondary form-control">Izlaz robe - Promet</a>
                <h5 class="mt-5">Poslednji uneti artikal: </h5>
                <h5 class="mt-2">Šifra: {{ \App\Models\Stock::orderBy('id', 'DESC')->first()->code }}</h5>
                <h5 class="mt-2">Artikal: {{ \App\Models\Stock::orderBy('id', 'DESC')->first()->article }}</h5>
                <hr>
            </div>
            <div class="col-1"></div>
            <div class="col-6">
                <h2>Izmeni Artikal</h2>
                <form action="{{ route('home.updateStock', ['id'=>$article->id]) }}" method="POST">
                    @csrf
                    @method('put')
                    <label for="code">Šifra</label>
                    <input type="number" name="code" placeholder="Unos je obavezan" class="form-control" min="1"
                           value="{{ $article->code }}" id="code" readonly required>
                    <label for="article">Artikal</label>
                    <input type="text" name="article" placeholder="Unos je obavezan" class="form-control"
                           value="{{ $article->article }}" id="article" required>
                    <label for="unit">Jedinica Mere</label>
                    <select name="unit" id="unit" class="form-control" required>
                        <option value="{{ $article->unit }}">{{ $article->unit }}</option>
                        <option value="kom">kom</option>
                        <option value="kg">kg</option>
                        <option value="l">l</option>
                        <option value="m'">m'</option>
                        <option value="m2">m2</option>
                        <option value="m3">m3</option>
                    </select>
                    <label for="price">Cena prodajna</label>
                    <input type="number" name="price" step=".01" placeholder="Unos je obavezan" class="form-control" min="1"
                           value="{{ $article->price }}" id="price" required>
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

