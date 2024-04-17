@extends('layouts.app')

@section('content')
    <style>
        #list {
            position: absolute;
            background-color: #f1f2f0;
            width: 300px;
            border-radius: 10px;
            display: none;
        }

        #list li:hover {
            background-color: silver;
            border-radius: 10px;
        }

        #list li {
            display: block;
            padding: 4px;
            cursor: default;
        }

    </style>


    <div class="container">
        <div class="row">
            <div class="col-4">
                <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                <a href="{{ url('/home/new-invoice-data') }}" class="btn btn-secondary form-control mb-2">Napravi novu
                    fakturu</a>
                <a href="{{ route('home.all_invoices') }}" class="btn btn-secondary form-control mb-5">Sve fakture</a>
                <div class="col">Dobavljač: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ \App\Models\Supplier::find($invoice->supplier_id)->supplier }}</span>
                </div>
                <div class="col">Zaduženje: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ $invoice->invoice_amount }}</span> din. <span
                        class="float-end">Uneto u bazu: {{ \Illuminate\Support\Facades\DB::table('entrances')->
                                   where('invoice_id', $invoice->id)->select('sum')->sum('sum') }}</span>
                </div>
                <div class="col">Otpr/faktura: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="col">Datum fakturisanja: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('d. M. Y.') }}</span>
                </div>
                <hr>
                <div class="mt-4">
                    <form action="{{ route('saveEntrance', ['id'=>$invoice->id]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-2">
                                <label for="code">Šifra</label>
                                <input type="text" name="code" id="code" placeholder="Unos je obavezan"
                                       class="form-control" readonly required>
                            </div>
                            <div class="col-7">
                                <label for="search">Unesi slova artikla ili šifru</label>
                                <input type="text" name="article" id="search" placeholder="Unos je obavezan"
                                       class="form-control" required>
                                <ul id="list" class="border m-0 p-0" style="display: none"></ul>
                            </div>
                            <div class="col-3">
                                <label for="pcs">Količina</label>
                                <input type="number" step=".01" min="0" name="pcs" id="pcs"
                                       placeholder="Unos je obavezan" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="purchase_price">Nabavna cena - bruto</label>
                                <input type="number" step=".01" min="0" name="purchase_price" id="purchase_price"
                                       placeholder="Unos je obavezan" class="form-control" required>
                            </div>
                            <div class="col">
                                <label for="price">Prodajna cena (Stocks tabela)</label>
                                <input type="number" step=".01" min="0" name="price" id="price"
                                       placeholder="Unos je obavezan" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <label for="rebate">Rabat</label>
                                <input type="number" step=".01" min="0" name="rebate" id="rebate" class="form-control"
                                       value="0">
                            </div>
                            <div class="col">
                                <label for="discount">Rabat2</label>
                                <input type="number" step=".01" min="0" name="discount" id="discount"
                                       class="form-control" value="0">
                            </div>
                            <div class="col">
                                <label for="tax">Porez</label>
                                <input type="number" step=".01" min="0" name="tax" id="tax" class="form-control"
                                       value="20">
                            </div>
                        </div>
                        <label for="sum">Ukupno</label>
                        <input type="number" step=".01" min="0" name="sum" id="sum" class="form-control" readonly
                               required>
                        <button type="submit" class="btn btn-secondary form-control mt-4">Snimi</button>
                    </form>
                    {{--                    @if(session()->has('message1'))--}}
                    {{--                        <div class="alert alert-success">--}}
                    {{--                            {{ session()->get('message1') }}--}}
                    {{--                        </div>--}}
                    {{--                    @endif--}}
                </div>

            </div>
            <div class="col-1"></div>
            <div class="col-7">
                @if(isset($entrances))
                    <table class="table table-striped-columns table-hover border-warning text-center">
                        <thead>
                        <tr class="table table-secondary border-dark">
                            <th>Šifra</th>
                            <th>Artikal</th>
                            <th>Količina</th>
                            <th>Bruto nabavna Cena</th>
                            <th>Rabat</th>
                            <th>Rabat2</th>
                            <th>Porez</th>
                            <th>Ukupno</th>
                            <th></th>
                        </tr>
                        </thead>
                        @foreach($entrances as $entrance)
                            <tbody>
                            <tr>
                                <td>{{ $entrance->code }}</td>
                                <td>{{ $entrance->article }}</td>
                                <td>{{ $entrance->pcs }}</td>
                                <td>{{ $entrance->purchase_price }}</td>
                                <td>{{ $entrance->rebate }}</td>
                                <td>{{ $entrance->discount }}</td>
                                <td>{{ $entrance->tax }}</td>
                                <td>{{ $entrance->sum }}</td>
                                <td><a href="{{ route('home.updateBeforeDelete', ['id'=>$entrance->id, 'code'=>$entrance->code, 'invoice_id'=>$invoice->id]) }}" class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Obrisi</a>
                                </td>
                            </tr>
                            </tbody>
                        @endforeach
                    </table>
                    <div class="float-end">Ukupno sve: {{ $total_per_invoice }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>

        function writeDataInList(array) {
            document.getElementById('list').innerHTML = "";
            if (array.length === 0) {
                document.getElementById('list').style.display = 'none';
            } else {
                document.getElementById('list').style.display = 'block';
                // document.getElementById('pcs').disabled = false;
            }
            for (let i = 0; i < array.length; i++) {
                document.getElementById('list').innerHTML += '<li data-code = "' + array[i].code + '" data-article = "' + array[i].article + '">'
                    + 'Šifra ' + array[i].code + ' ' + array[i].article + "</li>";
            }
            let suggestions = document.querySelectorAll('#list li');
            for (let i = 0; i < suggestions.length; i++) {
                suggestions[i].addEventListener('click', function () {
                    writeDataInList([]);
                    let code = suggestions[i].dataset.code;
                    document.getElementById('code').value = code;
                    let articleName = suggestions[i].dataset.article;
                    document.getElementById('search').value = articleName;

                });
            }
            let tax = document.getElementById('tax');
            tax.addEventListener('keyup', function () {
                let pcs = document.getElementById('pcs');
                let purchasePrice = document.getElementById('purchase_price');
                let sum = document.getElementById('sum');
                let rebate = document.getElementById('rebate');
                let discount = document.getElementById('discount');
                if (pcs.value && purchasePrice.value && rebate.value && discount.value && tax.value) {
                    sum.value = pcs.value * (purchasePrice.value * (1 - rebate.value / 100) * (1 - discount.value / 100) * (1 + tax.value / 100));
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }

                if (tax.value === '') {
                    sum.value = '';
                }
            })

            let purchasePrice = document.getElementById('purchase_price');
            purchasePrice.addEventListener('keyup', function () {
                let pcs = document.getElementById('pcs');
                let rebate = document.getElementById('rebate');
                let discount = document.getElementById('discount');
                let sum = document.getElementById('sum');
                if (pcs.value && purchasePrice.value && rebate.value && discount.value && tax.value) {
                    sum.value = pcs.value * (purchasePrice.value * (1 - rebate.value / 100) * (1 - discount.value / 100) * (1 + tax.value / 100));
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (purchasePrice.value === '') {
                    sum.value = '';
                }
            })

            let rebate = document.getElementById('rebate');
            rebate.addEventListener('keyup', function () {
                let pcs = document.getElementById('pcs');
                let purchasePrice = document.getElementById('purchase_price');
                let discount = document.getElementById('discount');
                let sum = document.getElementById('sum');
                if (pcs.value && purchasePrice.value && rebate.value && discount.value && tax.value) {
                    sum.value = pcs.value * (purchasePrice.value * (1 - rebate.value / 100) * (1 - discount.value / 100) * (1 + tax.value / 100));
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (rebate.value === '') {
                    sum.value = '';
                }
            })

            let discount = document.getElementById('discount');
            discount.addEventListener('keyup', function () {
                let pcs = document.getElementById('pcs');
                let purchasePrice = document.getElementById('purchase_price');
                let rebate = document.getElementById('rebate');
                let sum = document.getElementById('sum');
                if (pcs.value && purchasePrice.value && rebate.value && discount.value && tax.value) {
                    sum.value = pcs.value * (purchasePrice.value * (1 - rebate.value / 100) * (1 - discount.value / 100) * (1 + tax.value / 100));
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (discount.value === '') {
                    sum.value = '';
                }
            })

            let pcs = document.getElementById('pcs');
            pcs.addEventListener('keyup', function () {
                let discount = document.getElementById('discount');
                let purchasePrice = document.getElementById('purchase_price');
                let rebate = document.getElementById('rebate');
                let sum = document.getElementById('sum');
                if (discount.value && purchasePrice.value && rebate.value && discount.value && tax.value) {
                    sum.value = pcs.value * (purchasePrice.value * (1 - rebate.value / 100) * (1 - discount.value / 100) * (1 + tax.value / 100));
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (pcs.value === '') {
                    sum.value = '';
                }
            })

        }

        // $('#search').change(function () {
        //    // var id = $(this).val();
        //     $.get('/api/stock', function (data) {
        //         console.log(data);
        //         writeDataInList(data);
        //     })
        // })


        window.addEventListener('load', function () {
            fetch('/api/stock')
                .then(res => res.json())
                .then(data => {
                    // ovde se obradjuje uspesno dobijen odgovor sa api rute
                    console.log(data);
                    writeDataInList(data);
                })
            document.getElementById('search').addEventListener('keyup', function (event) {
                let query = document.getElementById('search').value;
                console.log(query);
                fetch('/api/stock/' + query)
                    .then(res => res.json())
                    .then(data => {
                        console.log(data);
                        writeDataInList(data);
                    });
            });
        });


    </script>





@endsection

