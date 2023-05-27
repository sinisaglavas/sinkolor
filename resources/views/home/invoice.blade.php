
@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ url('/home/new-invoice-data') }}" class="btn btn-secondary form-control mb-2">Napravi novu
                    fakturu</a>
                <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control mb-5">Sve fakture</a>
                <div class="col">Dobavljač: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ $invoice->supplier_id }}</span></div>
                <div class="col">Zaduženje: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ \App\Models\Supplier::find($invoice->supplier_id)->supplier }}</span> din. <span
                        class="float-end">Uneto u bazu: {{ \Illuminate\Support\Facades\DB::table('entrances')->
                                   where('invoices_id', $invoice->id)->select('sum')->sum('sum') }}</span>
                </div>
                <div class="col">Otpr/faktura: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ $invoice->invoice_number }}</span></div>
                <div class="col">Datum prometa: &nbsp; &nbsp;<span style="font-weight: bold">{{\Carbon\Carbon::parse($invoice->invoicing_date)->format('d.m.Y.') }}</span></div>
                <hr>
            </div>
            <div class="col-1"></div>
            <div class="col-7">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Šifra</th>
                        <th scope="col">Artikal</th>
                        <th scope="col">Komada</th>
                        <th scope="col">Nabavna cena</th>
                        <th scope="col">Rabat</th>
                        <th scope="col">Rabat1</th>
                        <th scope="col">Porez</th>
                        <th scope="col">Ukupno</th>
                    </tr>
                    </thead>
                    @foreach($entrances as $entrance)
                        <tbody>
                        <tr>
                            <th scope="row">{{ $entrance->id }}</th>
                            <td>{{ $entrance->code }}</td>
                            <td>{{ $entrance->article }}</td>
                            <td>{{ $entrance->pcs }}</td>
                            <td>{{ $entrance->purchase_price }}</a></td>
                            <td>{{ $entrance->rebate }}</td>
                            <td>{{ $entrance->discount }}</td>
                            <td>{{ $entrance->tax }}</td>
                            <td>{{ $entrance->sum }}</td>
                        </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>


    </div>


@endsection

