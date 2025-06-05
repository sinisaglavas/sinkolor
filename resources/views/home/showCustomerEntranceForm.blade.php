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
                <a href="{{ url('/home/new-customer-invoices') }}" class="btn btn-danger form-control mb-2">Nova faktura kupca</a>
                <a href="{{ route('home.allCustomerInvoices') }}" class="btn btn-danger form-control mb-2">Sve fakture kupaca</a>
                <div class="col">Dobavljač: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ \App\Models\Customer::find($invoice->customer_id)->customer }}</span>
                </div>
                <div class="col">Zaduženje: &nbsp; &nbsp; &nbsp;<span
                        style="font-weight: bold; font-size: 17px">{{ $invoice->invoice_amount }}</span> din. <span
                        class="float-end">Uneto u bazu: {{ \Illuminate\Support\Facades\DB::table('customer_outputs')->
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
                    <form action="{{ route('saveCustomerOutput', ['id'=>$invoice->id]) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-3">
                                <label for="code">Šifra</label>
                                <input type="text" name="code" id="code" placeholder="Unos je obavezan"
                                       class="form-control" readonly required>
                            </div>
                            <div class="col-9">
                                <label for="search">Unesi slova artikla ili šifru</label>
                                <input type="text" name="article" id="search" placeholder="Unos je obavezan"
                                       class="form-control" required>
                                <ul id="list" class="border m-0 p-0" style="display: none"></ul>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <label for="pcs">Količina</label>
                                <input type="number" step=".01" min="0" name="pcs" id="pcs"
                                       placeholder="Unos je obavezan" class="form-control" required>
                            </div>
                            <div class="col">
                                <label for="price">Prodajna cena (Stocks tabela)</label>
                                <input type="number" step=".01" min="0" name="price" id="price"
                                       placeholder="Unos je obavezan" class="form-control" required>
                            </div>
                        </div>
                        <label for="sum">Ukupno</label>
                        <input type="number" step=".01" min="0" name="sum" id="sum" class="form-control" readonly
                               required>
                        <button type="submit" class="btn btn-danger form-control mt-4">Snimi</button>
                    </form>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('generatePDF', $invoice->id) }}" class="btn btn-danger form-control mt-2"
                               target="_blank">Štampa-PDF
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('efakturaLogs') }}" class="btn btn-danger form-control mt-2">Efaktura-provera</a>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col">
                                <form action="{{ route('sendToSef', $invoice->id) }}" method="post" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-danger form-control"
                                    onclick="return confirm('Da li ste sigurni da želite poslati ovu fakturu?')">Pošalji fakturu u SEF</button>
                                </form>
                                @if(session()->has('success'))
                                    <div class="alert alert-success mt-1 p-1">
                                        {{ session()->get('success') }}
                                    </div>
                                @endif
                                @if(session()->has('error'))
                                    <div class="alert alert-success mt-1 p-1">
                                        {{ session()->get('error') }}
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <form action="{{ route('attemptCancelInvoice', $invoice->id) }}" method="post" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-warning form-control"
                                            onclick="return confirm('Da li ste sigurni da želite STORNIRATI ovu fakturu?')">Storniraj fakturu u SEF-u</button>
                                </form>
                                @if(session()->has('success'))
                                    <div class="alert alert-success mt-1 p-1">
                                        {{ session()->get('success') }}
                                    </div>
                                @endif
                                @if(session()->has('error'))
                                    <div class="alert alert-success mt-1 p-1">
                                        {{ session()->get('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                </div>
            </div>
            <div class="col-1"></div>
            <div class="col-7">
                @if(isset($outputs))
                    <table class="table table-striped-columns table-hover border-warning text-center">
                        <thead>
                        <tr class="table table-secondary border-dark">
                            <th>Šifra</th>
                            <th>Artikal</th>
                            <th>Količina</th>
                            <th>Cena</th>
                            <th>Ukupno</th>
                            <th></th>
                        </tr>
                        </thead>
                        @foreach($outputs as $output)
                            <tbody>
                            <tr>
                                <td>{{ $output->code }}</td>
                                <td>{{ $output->article }}</td>
                                <td>{{ $output->pcs }}</td>
                                <td>{{ $output->price }}</td>
                                <td>{{ $output->sum }}</td>
                                <td><a href="{{ route('justDeleteArticle', ['id'=>$output->id, 'code'=>$output->code, 'invoice_id'=>$invoice->id]) }}" class="btn btn-sm btn-warning"
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
                document.getElementById('list').innerHTML += '<li data-code = "' + array[i].code + '" data-article = "' + array[i].article + '" data-price = "' + array[i].price + '">'
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
                    let priceName = suggestions[i].dataset.price;
                    document.getElementById('price').value = priceName;

                });
            }
            var pcs = document.getElementById('pcs');
            pcs.addEventListener('keyup', function () {
                let price = document.getElementById('price');
                let sum = document.getElementById('sum');
                if (pcs.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }

                if (pcs.value === '') {
                    sum.value = '';
                }
            })

        }

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


