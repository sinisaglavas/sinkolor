@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control mb-5">Svi artikli - LAGER</a>
                <h3>Odaberi dan i evidentiraj promet:</h3>
                <form action="{{ route('home.requestedDay') }}" method="get" class="mb-5">
                    <label for="date">Odaberi datum</label>
                    <input type="date" name="date" value="{{ date('d.m.Y') }}" class="form-control" id="date" required>
                    <button class="btn btn-secondary form-control mt-2">Posalji</button>
                </form>
                <p>Zadnji evidentirani dan: {{ Carbon\Carbon::parse(\App\Models\Output::latest()->first()->date_of_turnover)->format('l d. M. Y.') }}
                    &nbsp;&nbsp;{{ \App\Models\Output::where('date_of_turnover', \App\Models\Output::latest()->first()->date_of_turnover)->sum('sum') }}</p>
                <div class="row mb-1">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>1]) }}" class="btn btn-info" style="width: 100%;">Januar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 1)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>2]) }}" class="btn btn-info" style="width: 100%;">Februar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 2)->sum('sum') }}</a></div>

                </div>
                <div class="row mb-1">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>3]) }}" class="btn btn-info" style="width: 100%;">Mart
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 3)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>4]) }}" class="btn btn-info" style="width: 100%;">April
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 4)->sum('sum') }}</a></div>
                </div>
                <div class="row mb-1">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>5]) }}" class="btn btn-info" style="width: 100%;">Maj
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 5)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>6]) }}" class="btn btn-info" style="width: 100%;">Jun
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 6)->sum('sum') }}</a></div>
                </div>
                <div class="row mb-1">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>7]) }}" class="btn btn-info" style="width: 100%;">Jul
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 7)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>8]) }}" class="btn btn-info" style="width: 100%;">Avgust
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 8)->sum('sum') }}</a></div>
                </div>
                <div class="row mb-1">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>9]) }}" class="btn btn-info" style="width: 100%;">Septembar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 9)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>10]) }}" class="btn btn-info" style="width: 100%;">Oktobar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 10)->sum('sum') }}</a></div>
                </div>
                <div class="row mb-3">
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>11]) }}" class="btn btn-info" style="width: 100%;">Novembar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 11)->sum('sum') }}</a></div>
                    <div class="col"><a href="{{ route('home.getMonth', ['id'=>12]) }}" class="btn btn-info" style="width: 100%;">Decembar
                            &nbsp;{{ \App\Models\Output::whereMonth('date_of_turnover', 12)->sum('sum') }}</a></div>
                </div>
            </div>
            <div class="col"></div>
            <div class="col-7">
                @if(isset($turnover_by_days))
                <table class="table border-warning text-center">
                    <thead>
                    <tr class="table table-secondary border-dark">
                        <th>Datum prometa</th>
                        <th>Ukupan promet</th>
                        <th>Profit po danu</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($turnover_by_days as $turnover_by_day)
                        <tr>
                            <td>
                                <a style="text-decoration: none; color: black" href="{{ route('home.requestedDay2', ['search_date'=>$turnover_by_day->day]) }}">
                                    {{ Carbon\Carbon::parse($turnover_by_day->day)->format('d. M. Y.') }}</a>
                            </td>
                            <td><a style="text-decoration: none; color: black; font-weight: bold" href="{{ route('home.requestedDay2', ['search_date'=>$turnover_by_day->day]) }}">
                                    {{ $turnover_by_day->total }}</a></td>
                            <td>{{ \App\Models\Output::where('date_of_turnover', $turnover_by_day->day)->sum('total_profit') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
@endsection



