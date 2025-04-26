<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    @if(isset($customer))
        <title>Faktura za {{ $customer->customer }}</title>
    @else
        <title>Faktura za kupca</title>
    @endif
</head>

    <style>
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "DejaVu Sans", sans-serif;
        }

        /** Kljucni stilovi za header **/
        @page {
            margin-top: 340px;
            margin-bottom: 50px;
        }

        .header {
            position: fixed;
            top: -320px;  /* Podeseno za veći offset */
            left: 0;
            right: 0;
            height: 100px;
            z-index: 1;  /* Osigurava da header bude iznad sadrzaja */
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 50px;
        }

        .header-table img {
            border-radius: 7px;
        }

        .invoiceQrCustomer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .customer, span {
            margin-left: 10px;
        }

        /** Obavezno za sadrzaj **/
        .content {
            position: relative;
            z-index: 0;
        }
        .customerOutput table {
            page-break-inside: auto; /* Omogucava normalno lomljenje tabela */
            width: 100%; /* Da zauzme celu dostupnu sirinu */
            border-collapse: collapse; /* Da spoji ivice celija */
            padding: 7px;
        }
        thead {
            display: table-header-group; /* Obezbedjuje da se header tabele ponavlja */
        }
        .customerOutput table th {
            border: 1px solid lightgrey;
            text-align: center;
            padding: 3px;
            font-size: 12px;
        }
        .customerOutput table td {
            border: 1px solid lightgrey;
            text-align: center;
            font-size: 12px;
        }

        .box {
            float: left;
            font-size: 9px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .signature {
            float: right;
            font-size: 11px;
        }

        .footer {
            /*height: 30px; !* Visina podnozja *!*/
            /*text-align: center;*/
            /*font-size: 22px;*/
            /*margin-bottom: 30px;*/
        }

    </style>

<body>

<!-- Header (ponavlja se na svim stranama) -->
<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 35%">
                <img src="{{ public_path('image/sinkolor.jpg') }}" style="height: 80px;" alt="Logo">
            </td>
            <td style="width: 32%; text-align: left; font-size: 11px;">
                DOO "SIN KOLOR"<br>
                SILBAŠ, Kralja Petra I 92<br>
                Kontakt: 021/3990136<br>
                Mobilni: 060/5590990
            </td>
            <td style="width: 33%; text-align: left; font-size: 11px;">
                PIB: 113008454<br>
                Matični broj: 21788007<br>
                <strong>ERSTE banka: 340-11031905-33</strong>
            </td>
        </tr>
    </table>
    <div style="margin-top: 60px;">
        <table class="invoiceQrCustomer-table">
            <tr>
                <td style="width: 60%; text-align: left; font-size: 11px;">
                    <h4 style="font-size: 14px; margin: 0;">Račun broj: 1{{ $customerInvoice->invoice_number }}</h4>
                    <span>Broj otpremnice: {{ $customerInvoice->invoice_number }}</span><br>
                    <span>Datum prometa dobara i usluga:
                        {{ Carbon\Carbon::parse($customerInvoice->invoicing_date)->translatedFormat('j. F Y.') }}
                    </span><br>
                    <span>Plaćanje odmah</span>
                </td>
                <td class="customer" style="width: 30%; text-align: left; font-size: 11px;">
                    <div style="border-left: 1px solid dimgray;">
                        <span>Kupac:</span><br>
                        <span style="font-size: 12px; font-weight: bold;">{{ $customer->customer }}</span><br>
                        <span>{{ $customer->address }}</span><br>
                        <span>{{ $customer->city }}</span><br>
                        <span>PIB: {{ $customer->pib }}</span><br>
                        <span>MB: {{ $customer->mb }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- GLAVNI SADRZAJ -->
    <div class="content">
        <div class="customerOutput">
            <table>
                <thead>
                <tr>
                    <th>Šifra</th>
                    <th>OPIS</th>
                    <th>JM</th>
                    <th>Količina</th>
                    <th>Cena</th>
                    <th>Vrednost</th>
                </tr>
                </thead>
                @foreach($customerOutput as $output)
                    <tbody>
                    <tr>
                        <th>{{ $output->code }}</th>
                        <td>{{ $output->article }}</td>
                        <td>kom</td>
                        <td>{{ $output->pcs }}</td>
                        <td>{{ $output->price }}</td>
                        <td>{{ $output->sum }}</td>
                    </tr>
                    </tbody>
                @endforeach
            </table>
        </div>
        <hr style="margin-bottom: 0">
        <div class="clearfix">
            <div class="box" style="text-align: left; width: 50%;">
                <span>Mesto izdavanja računa: Silbaš</span><br>
                <span>DOO Sin Kolor iz Silbaša nije u sistemu PDV-a.</span><br>
                <span style="font-weight: bold; font-size: 10px;">Prilikom uplate pozvati se na broj:
                    1{{ preg_replace('/[^0-9]/', '', $customerInvoice->invoice_number) }}</span><br>
                <span>Roba preuzeta u viđenom stanju, naknadne reklamacije se ne priznaju.</span><br>
                <span>Za sve sporove nadležan je sud u mestu isporučioca.</span>
            </div>
            <div class="box" style="text-align: center; width: 20%; margin-top: 20px;">
                <img src="data:image/png;base64,{{ $imageData }}" alt="QR Kod" style="width: 100px;"><br>
                <span>IPS QR kod</span>
            </div>
            <div class="box" style="text-align: right; width: 30%;">
                <span style="font-size: 14px; font-weight: bold; margin-right: 20px;">Ukupno: {{ $total_per_invoice }} </span>
            </div>
        </div>

        <div class="signature">
            <p>______________________</p>
            <span>Račun izdao</span>
        </div>
        <div class="footer">
            <script type="text/php">
                if (isset($pdf)) {
               $pdf->page_text(495, 783, "Strana {PAGE_NUM} od {PAGE_COUNT}", null, 8);
           }
</script>
        </div>
    </div>

</body>

</html>
