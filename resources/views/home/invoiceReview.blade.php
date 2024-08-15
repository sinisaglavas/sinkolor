<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Štampa</title>
</head>
<body>

<div class="container">
    <div class="row mt-1">
        <div class="col-4">
            <a href="{{ url('/home') }}" class="text-decoration-none" style="color: black"><p class="fw-bold m-0">DOO "SIN KOLOR"</p></a>
            <h6>Kralja Petra I 92</h6>
            <h6>21433 Silbaš Srbija</h6>
            <p>TR: 340-11031905-33</p>
        </div>
        <div class="col-3"></div>
        <div class="col-5">
            <h5>Kupac:</h5>
            <h6 class="fw-bold">{{ $customer->customer }}</h6>
            <h6>{{ $customer->address }}</h6>
            <h6>{{ $customer->city }}</h6>
            <h6>PIB: {{ $customer->pib }} &nbsp; &nbsp;MB: {{ $customer->mb }}</h6>
            <h6></h6>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h5 class="fw-bold m-0">Račun broj: 1{{ $invoice->invoice_number }} </h5>
            <div>Mesto izdavanja računa: Silbaš</div>
            <div>Datum prometa dobara i usluga: <span
                    class="fw-bold">{{ Carbon\Carbon::parse($invoice->invoicing_date)->format('j. F Y.') }}</span></div>
            <div>Plaćanje odmah</div>
        </div>
    </div>
    <div class="row mt-1">
        <hr>
        <div class="col-12 mb-3">
            <table class="table table-bordered">
                <thead>
                <tr class="table-active text-center">
                    <th scope="col">Šifra</th>
                    <th scope="col">OPIS</th>
                    <th scope="col">JM</th>
                    <th scope="col">Količina</th>
                    <th scope="col">Cena</th>
                    <th scope="col">Vrednost</th>
                </tr>
                </thead>
                @foreach($outputs as $output)
                    <tbody>
                    <tr class="text-center">
                        <th scope="row">{{ $output->code }}</th>
                        <td>{{ $output->article }}</td>
                        <td>kom</td>
                        <td>{{ $output->pcs }}</td>
                        <td>{{ $output->price }}</td>
                        <td>{{ $output->sum }}</td>
                    </tr>
                    </tbody>
                @endforeach
            </table>
            <hr>
            <div class="float-end">Ukupno sve: <span class="fw-bold">{{ $total_per_invoice }}</span></div>
            <div class="mt-5">DOO Sin Kolor iz Silbaša nije u sistemu PDV-a.</div>
            <div>
                <p>Roba preuzeta u viđenom stanju, naknadne reklamacije se ne priznaju.
                    Za sve sporove nadležan je sud u mestu isporučioca.</p>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            Račun izdao
        </div>
    </div>
</div>
</body>
</html>






