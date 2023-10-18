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
                            <a href="{{ route('home.stock') }}" class="btn btn-secondary form-control m-2">Svi artikli - LAGER</a>
                        </div>
                        <div class="col">
                            <a href="{{ route('home.turnoverByDays') }}" class="btn btn-secondary form-control m-2">Promet po danima</a>
                        </div>
                    </div>
                </div>
                <div class="col-10">
                    <form action="" method="post" id="form">
                        @csrf
                        <table class="table table-striped-columns table-hover border-warning text-center">
                            <thead>
                            <tr class="table table-secondary border-dark">
                                <th>Promet na dan</th>
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
                                <td><input type="date" name="date_of_turnover" id="date_of_turnover" class="form-control"
                                    @if(isset($date)) value="{{ $date }}"> @endif</td>
                                <td><input type="text" name="code" id="code" class="form-control" required></td>
                                <td><input type="text" name="article" id="search" placeholder="Unos je obavezan" class="form-control" required>
                                    <ul id="list" class="border m-0 p-0" style="display: none"></ul></td>
                                <td><input type="number" min="0" name="pcs" id="pcs"
                                            placeholder="Unos je obavezan" class="form-control" required></td>
                                <td><input type="number" step=".01" min="0" name="price" id="price"
                                           placeholder="Unos je obavezan" class="form-control" required></td>
                                <td><input type="number" step=".01" min="0" name="sum" id="sum" class="form-control" required></td>
                                <td><button type="submit" class="btn btn-secondary form-control mt-2" id="save-data">Snimi</button></td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12">
                    <h4 style="display: inline-block"></h4>
                    <h4 style="display:inline-block; float: right">Ukupno promet: {{ \Illuminate\Support\Facades\DB::table('outputs')->sum('sum') }}  dinara</h4>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-center">
                    <table class="table table-striped-columns table-hover border-warning text-center">
                        <thead>
                        <tr class="table table-secondary border-dark">
                            <th>Promet na dan</th>
                            <th>Šifra</th>
                            <th>Artikal</th>
                            <th>Komada</th>
                            <th>Cena</th>
                            <th>Ukupno</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="tbody"></tbody>
                    </table>
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
                    + 'Šifra ' + array[i].code + ' , ' + array[i].article + "</li>";
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
            let price = document.getElementById('price');
            price.addEventListener('blur', function () {
                let pcs = document.getElementById('pcs');
                let sum = document.getElementById('sum');
                if (pcs.value && price.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }

                if (price.value === '') {
                    sum.value = '';
                }
            })
        }


        window.addEventListener('load', function () {
            fetch('/api/stock')
                .then(res => res.json())
                .then(data => {
                    // ovde se obradjuje uspesno dobijen odgovor sa api rute
                   // console.log(data);
                    writeDataInList(data);
                })
            document.getElementById('search').addEventListener('keyup', function (event) {
                let query = document.getElementById('search').value;
                console.log(query);
                fetch('/api/stock/' + query)
                    .then(res => res.json())
                    .then(data => {
                        writeDataInList(data);
                    });
            });

        });


        function inputData(array){
            document.getElementById('tbody').innerHTML = "";
            for (let i=0; i<array.length;i++){
                const tr = document.createElement('tr');
                tr.id = 'tr-id';
                document.getElementById('tbody').appendChild(tr);

                const td1 = document.createElement('td');
                td1.className = 'td-class';
                td1.appendChild(document.createTextNode(array[i].date_of_turnover));
                tr.appendChild(td1);

                const td2 = document.createElement('td');
                td2.className = 'td-class';
                td2.appendChild(document.createTextNode(array[i].code));
                tr.appendChild(td2);

                const td3 = document.createElement('td');
                td3.className = 'td-class';
                td3.appendChild(document.createTextNode(array[i].article));
                tr.appendChild(td3);

                const td4 = document.createElement('td');
                td4.className = 'td-class';
                td4.appendChild(document.createTextNode(array[i].pcs));
                tr.appendChild(td4);

                const td5 = document.createElement('td');
                td5.className = 'td-class';
                td5.appendChild(document.createTextNode(array[i].price));
                tr.appendChild(td5);

                const td6 = document.createElement('td');
                td6.className = 'td-class';
                td6.appendChild(document.createTextNode(array[i].sum));
                tr.appendChild(td6);

                const td7 = document.createElement('td');
                td7.setAttribute('href', '""');
                td7.className = 'td-class btn btn-sm btn-warning';
                td7.appendChild(document.createTextNode('Obriši'));
                tr.appendChild(td7);
            }
        }

        window.addEventListener('load', function () {
           fetch('/api/output')
           .then(res => res.json())
           .then(data => {
               inputData(data);
           })
            document.getElementById('date_of_turnover').addEventListener('blur', function (event) {
                let date = document.getElementById('date_of_turnover').value;
                console.log(date);
                fetch('/api/output/' + date)
                .then(res => res.json())
                .then(data => {
                    console.log(data);
                   inputData(data);
                });

            });

        });

        function writeDataInInput(array){
            let code = document.getElementById('code');
            code.value = array[0].code;
            let article = document.getElementById('search');
            article.value = array[0].article;
            let price = document.getElementById('price');
            price.value = array[0].price;
            let pcs = document.getElementById('pcs');
            pcs.addEventListener('blur', function () {
                let sum = document.getElementById('sum');
                if (pcs.value && price.value) {
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }

                if (price.value === '') {
                    sum.value = '';
                }
            });
            price.addEventListener('keyup', function () {
                if (pcs.value && price.value) {
                    let sum = document.getElementById('sum');
                    sum.value = pcs.value * price.value;
                    sum.value = Math.round((sum.value * 100 + Number.EPSILON)) / 100 // skracuje na dve decimale
                }
                if (price.value === '') {
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

