@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
                <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control mt-3">Lager</a>
                <a href="{{ route('home.newInvoice') }}" class="btn btn-secondary form-control mt-3">Napravi novu fakturu</a>
            </div>
            <div class="col-2"></div>
            <div class="col-5">
                <h2>Novi Dobavljač</h2>
                <form action="{{ route('home.addSupplier') }}" method="POST">
                    @csrf
                    <label for="supplier">Dobavljač</label>
                    <input type="text" name="supplier" id="supplier" placeholder="Naziv dobavljača - unos je obavezan" class="form-control" required>
                    <label for="other_data">Drugi podaci</label>
                    <textarea name="other_data" id="other_data" cols="30" rows="5" placeholder="Adresa, mesto, telefon...bilo koji podaci od značaja" class="form-control"></textarea>
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

