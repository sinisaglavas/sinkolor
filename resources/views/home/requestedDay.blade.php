@extends('layouts.app')

@section('content')
    <style>
        #list {
            position: absolute;
            background-color: #f1f2f0;
            width: 450px;
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


    @if(isset($search_date) && isset($search_data))
        <div class="container">
            @if(session()->has('message'))
                <div class="alert alert-success p-2 text-center">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="row mb-4">
                <div class="col-4">

                </div>
                <div class="col-4"></div>
                <div class="col-4">

                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <div class="row">
                        <div class="col">
                            <a href="{{ url('/home') }}" class="btn btn-secondary form-control mb-2">Glavni meni</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ url('/home/turnover-by-days') }}" class="btn btn-secondary form-control mb-2">Novi izlaz robe</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control">Lager</a>
                        </div>
                    </div>
                </div>
                <div class="col-10">
                    <form action="{{ route('home.saveOutput',['search_date'=>$search_date]) }}" method="post" id="form">
                        @csrf
                        <table class="table table-striped-columns table-hover border-warning text-center">
                            <thead>
                            <tr class="table table-secondary border-dark">
                                <th>Šifra</th>
                                <th>Artikal</th>
                                <th>Komada</th>
                                <th>Cena</th>
                                <th>Ukupno</th>
                                <th>Snimi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><input type="text" name="code" id="code" placeholder="Unesi šifru" class="form-control" autofocus required></td>
                                <td><input type="text" name="article" id="search" placeholder="Unesi slova artikla" class="form-control" required>
                                    <ul id="list" class="border m-0 p-0" style="display: none"></ul></td>
                                <td><input type="number" step=".01" min="0" name="pcs" id="pcs"
                                           placeholder="Unos je obavezan" class="form-control" required></td>
                                <td><input type="number" step=".01" min="0" name="price" id="price"
                                           placeholder="Unos je obavezan" class="form-control" required></td>
                                <td><input type="number" step=".01" min="0" name="sum" id="sum" class="form-control" readonly required></td>
                                <td><input type="submit" class="btn btn-secondary form-control" id="save-data"></td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col text-start">
                    <h4>{{ Carbon\Carbon::parse($search_date)->format('l j. F Y.') }}</h4>
                </div>
                <div class="col text-center">
                    <h4><a href="{{ route('home.requestedDay2', ['search_date'=>$search_date]) }}" class="btn btn-secondary">Uzlazno ↑</a>&nbsp;<a href="{{ route('home.descendingArticle', ['search_date'=>$search_date]) }}" class="btn btn-secondary">Silazno ↓</a></h4>
                </div>
                <div class="col text-end">
                    <h4>Ukupno promet: {{ $sum }} dinara</h4>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-center">
                    <table class="table table-striped-columns table-hover border border-warning text-center">
                        <thead>
                        <tr class="table table-secondary border-dark">
                            <th>Id</th>
                            <th>Šifra</th>
                            <th>Artikal</th>
                            <th>Komada</th>
                            <th>Cena</th>
                            <th>Ukupno</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(session('from_method') == 'requestedDay2' || session('from_method') == 'requestedDay')
                        @foreach($search_data as $data)
                            <tr>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->code}}</td>
                                <td>{{ $data->article }}</td>
                                <td>{{ $data->pcs }}</td>
                                <td>{{ $data->price }}</td>
                                <td>{{ $data->sum }}</td>
                                <td><a href="{{ route('home.updateBeforeDelete2',['id'=>$data->id, 'search_date'=>$search_date]) }}" class="btn btn-sm btn-warning"
                                       onclick="return confirm('Da li ste sigurni?')">Obriši</a>
                                </td>
                            </tr>
                        @endforeach
                        @endif
                        @if(session('from_method') == 'descendingArticle')
                            @foreach($search_data as $data)
                                <tr>
                                    <td>{{ $data->id }}</td>
                                    <td>{{ $data->code}}</td>
                                    <td>{{ $data->article }}</td>
                                    <td>{{ $data->pcs }}</td>
                                    <td>{{ $data->price }}</td>
                                    <td>{{ $data->sum }}</td>
                                    <td><a href="{{ route('home.updateBeforeDelete2',['id'=>$data->id, 'search_date'=>$search_date]) }}" class="btn btn-sm btn-warning"
                                           onclick="return confirm('Da li ste sigurni?')">Obriši</a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <script>
        let pcs = document.getElementById('pcs');
        document.getElementById("save-data").addEventListener("click", function(event) {
            if (pcs.value) {
                // Zaustavljanje podrazumevanog ponašanja obrasca
                // zato sto se podaci šalju asinhrono i onda se forma ne šalje po defaultu
                event.preventDefault();
                // Odloženo slanje podataka nakon 1. sekunde
                setTimeout(function() {
                // .form-pristupamo njegovom roditeljskom elementu forme korišćenjem .form svojstva da bi mogli koristiti submit() metodu
                // .submit() metoda forme se koristi za slanje podataka forme na server
                // korisno kada želimo da programski izvršimo slanje forme na primer nakon određenog vremenskog intervala ili nakon provere uslova
                    document.getElementById("save-data").form.submit();
                }, 1000);
            }
        });

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
                    + 'Šifra ' + array[i].code + ' , ' + array[i].article + ' , ' + 'Cena ' + array[i].price + "</li>";
            }
            let suggestions = document.querySelectorAll('#list li');
            for (let i = 0; i < suggestions.length; i++) {
                suggestions[i].addEventListener('click', function () {
                    writeDataInList([]);
                    let code = suggestions[i].dataset.code;
                    document.getElementById('code').value = code;
                    let articleName = suggestions[i].dataset.article;
                    document.getElementById('search').value = articleName;
                    let price = suggestions[i].dataset.price;
                    document.getElementById('price').value = price;

                });
            }
            let pcs = document.getElementById('pcs');
            pcs.addEventListener('keyup', function () {
                let price = document.getElementById('price');
                let sum = document.getElementById('sum');
                if (pcs.value && price.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (price.value === '' || pcs.value === '') {
                    sum.value = '';
                }
            });
            let price = document.getElementById('price');
            price.addEventListener('keyup', function () {
                let sum = document.getElementById('sum');
                let pcs = document.getElementById('pcs');
                if (pcs.value && price.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale          })
                }
                if (price.value === '' || pcs.value === '') {
                    sum.value = '';
                }

            });
        }




        window.addEventListener('load', function () {
            fetch('/api/stock')
                .then(res => res.json())
                .then(data => {
                    // ovde se obradjuje uspesno dobijen odgovor sa api rute
                       //console.log(data);
                    writeDataInList(data);
                })
            document.getElementById('search').addEventListener('keyup', function (event) {
                let query = document.getElementById('search').value;
                fetch('/api/stock/' + query)
                    .then(res => res.json())
                    .then(data => {
                        writeDataInList(data);
                    });
            });

        });
        // async postavlja da se izvrsi prvo fetch pa onda ostatak koda
       async function writeDataInInput(array){
            let code = document.getElementById('code');
            code.value = array[0].code;
            let article = document.getElementById('search');
            article.value = array[0].article;
            let price = document.getElementById('price');
            price.value = array[0].price;
            let pcs = document.getElementById('pcs');
            // await postavlja da se izvrsi prvo fetch pa onda ostatak koda
            await pcs.addEventListener('keyup', function () {
                let sum = document.getElementById('sum');
                if (pcs.value && price.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }

                if (price.value === '' || pcs.value === '') {
                    sum.value = '';
                }
            });
            await price.addEventListener('keyup', function () {
                if (pcs.value && price.value) {
                    let sum = document.getElementById('sum');
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (price.value === '' || pcs.value === '') {
                    sum.value = '';
                }
            });
        }

        window.addEventListener('load', function () {
            fetch('/api/code')
                .then(res => res.json())
                .then(data => {
                    writeDataInInput(data);
                })
            document.getElementById('code').addEventListener('change', function (event) {
                let query = document.getElementById('code').value;
                fetch('/api/code/' + query)
                    .then(res => res.json())
                    .then(data => {
                        console.log(data[0]);
                        writeDataInInput(data);
                    });
            })
        });

    </script>

@endsection

