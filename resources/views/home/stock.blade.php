@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="row">
            <div class="col-2">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control">Glavni meni</a>
            </div>
            <div class="col-2">
                <a href="{{ url('home/show-stock-form') }}" class="btn btn-secondary form-control">Novi artikal</a>
            </div>
            <div class="col-2">
                <a href="{{ route('home.turnoverByDays') }}" class="btn btn-secondary form-control">Izlaz robe - Promet</a>
            </div>
            <div class="col-3">
                <button class="btn btn-success form-control">Ukupno nabavna:
                    <span>{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('purchase_price') }}</span></button>
            </div>
            <div class="col-3">
                <button class="btn btn-success form-control">Ukupno na lageru:
                    <span>{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('sum') }}</span></button>
            </div>
        </div>
        <div class="row mt-4 mb-2">
            <div class="col-7">
                <h5>Svi artikli - LAGER</h5>
            </div>
            <div class="col-5">
                <form action="{{ route('searchStock') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="code_article" class="form-control"
                               placeholder="Ukucaj šifru artikla"
                               aria-label="Search client" required>
                        <input type="submit" class="btn btn-outline-secondary" value="Traži">
                    </div>
                </form>
            </div>
        </div>

        @if(isset($search_stocks))
            <table class="table border-warning text-center">
                <thead>
                <tr>
                    <th>Šifra</th>
                    <th>Artikal</th>
                    <th>Stanje</th>
                    <th>JM</th>
                    <th>Nabavna cena</th>
                    <th>Marža</th>
                    <th>Cena</th>
                    <th>Ukupno</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($search_stocks as $search_stock)
                    <tr>
                        <td>{{ $search_stock->code }}</td>
                        <td>{{ $search_stock->article }}</a></td>
                        @if($search_stock->pcs <= 0)
                        <td style="background: #e5c7ca">{{ $search_stock->pcs }}</td>
                        @else
                        <td>{{ $search_stock->pcs }}</td>
                        @endif
                        <td>{{ $search_stock->unit }}</td>
                        <td>{{ $search_stock->purchase_price }}</td>
                        <td>{{ $search_stock->margin }}</td>
                        <td>{{ $search_stock->price }}</td>
                        <td>{{ $search_stock->sum }}</td>
                        <td style="background: #babbbc;"><a href="/stock/{{ $search_stock->id }}/edit" class="text-decoration-none" style="color: black">Promeni</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
        <table class="table table-striped-columns table-hover border-warning text-center">
            <thead>
            <tr class="table table-secondary border-dark">
                <th>Šifra</th>
                <th>Artikal</th>
                <th>Stanje</th>
                <th>JM</th>
                <th>Nabavna cena</th>
                <th>Marža</th>
                <th>Cena</th>
                <th>Ukupno</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($all_stocks as $all_stock)
                <tr>
                    <td>{{ $all_stock->code }}</td>
                    <td>{{ $all_stock->article }}</a></td>
                    @if($all_stock->pcs <= 0)
                        <td style="background: #e5c7ca">{{ $all_stock->pcs }}</td>
                    @else
                        <td>{{ $all_stock->pcs }}</td>
                    @endif
                    <td>{{ $all_stock->unit }}</td>
                    <td>{{ $all_stock->purchase_price }}</td>
                    <td>{{ $all_stock->margin }}</td>
                    <td>{{ $all_stock->price }}</td>
                    <td>{{ $all_stock->sum }}</td>
                    <td style="background: #babbbc;"><a href="/stock/{{ $all_stock->id }}/edit"
                                                        class="text-decoration-none" style="color: black">Promeni</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>


    </div>




@endsection
