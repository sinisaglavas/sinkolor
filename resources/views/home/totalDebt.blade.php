@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ url('/home/new-invoice-data') }}" class="btn btn-secondary form-control mb-2">Napravi novu
                    fakturu</a>
                <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control mb-3">Sve fakture</a>
                <div class="row">
                    <div class="col-6">
                        <h6>31.12.2024.</h6>
                        <h6 class="small">Stanje lagera: &nbsp;<span>{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('sum') }}</span></h6>
                        <h6 class="small">Dug dobavljacima:  &nbsp;<span>{{ \App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment') }}</span></h6>
                        <h6 class="small">Realno stanje:  &nbsp;<span class="fw-bold">{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('sum') - (\App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment')) }}</span></h6>
                    </div>
                    <div class="col-6">
                        <h6>31.12.2023.</h6>
                        <h6 class="small">Stanje lagera: &nbsp;<span>{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('sum') }}</span></h6>
                        <h6 class="small">Dug dobavljacima:  &nbsp;<span>{{ \App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment') }}</span></h6>
                        <h6 class="small">Realno stanje:  &nbsp;<span class="fw-bold">{{ \Illuminate\Support\Facades\DB::table('stocks')->sum('sum') - (\App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment')) }}</span></h6>
                    </div>
                </div>

            </div>
            <div class="col-1"></div>
            <div class="col-7">
                <h2>Ukupan dug po svim fakturama:
                    <span class="btn btn-success" style="font-size: 18px">{{ \App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment') }}</span>
                </h2>
                <table class="table text-center">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Dobavljač</th>
                        <th scope="col">Ukupno zaduženje</th>
                        <th scope="col">Ukupno razduženje</th>
                        <th scope="col">Ostatak duga</th>
                    </tr>
                    </thead>
                    @foreach($suppliers as $supplier)
                        <tbody>
                        <tr>
                            <th scope="row">{{ $supplier->id }}</th>
                            <td><a href="{{ route('home.supplier_invoices', ['id'=>$supplier->id]) }}" class="text-decoration-none">{{ $supplier->supplier }}</a></td>
                            <td>{{ $debt = \App\Models\Invoice::where('supplier_id', $supplier->id)->sum('invoice_amount') }}</td>
                            <td>{{ $paid = \App\Models\Payment::where('supplier_id', $supplier->id)->sum('invoice_payment') }}</td>
                            <td>{{ $sum = $debt - $paid }}</td>
                        </tr>
                        </tbody>
                    @endforeach
                </table>
                <div class="float-end btn btn-success">
                    Dug firme po svim dobavljačima ukupno: {{ \App\Models\Invoice::sum('invoice_amount') - \App\Models\Payment::sum('invoice_payment') }}
                </div>
            </div>
        </div>


    </div>


@endsection


