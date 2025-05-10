<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Stanje robe SIN KOLOR</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        /*odvajanje thead od tbody*/
        thead::after
        {
            content: "";
            display: block;
            height: 0.3em;
            width: 100%;
            background: white;
        }

        th, td {
            padding: 1.8px;
            text-align: center;
        }

        th {
            background-color: #636464;
            color: #fff;
        }
        td {
            border: 1px solid #000;
        }

        .page-break {
            page-break-after: always;
        }
        pre {
            display: block;
            margin: 0;
            text-align: right;
        }

    </style>

</head>

<body>
<script type="text/php">
    if (isset($pdf))
    { $pdf->page_text(36, 26, "Strana {PAGE_NUM} od {PAGE_COUNT}", null, 6);
        $pdf->page_text(150, 22, "STANJE ROBE U 'SIN KOLOR' DOO SILBAŠ NA DAN: {{ $date_of_turnover }}", null, 9); }
</script>
<!-- GLAVNI SADRZAJ -->
@foreach($pages as $stocks)
    <table>
        <thead>
        <tr>
            <th>Šifra</th>
            <th>Opis artikla</th>
            <th>Jedinica</th>
            <th>Cena</th>
            <th>Stanje</th>
            <th>Popis</th>
            <th>Napomena</th>
        </tr>
        </thead>
        <tbody>
        @foreach($stocks as $item)
            <tr>
                <td>{{ $item->code }}</td>
                <td>{{ $item->article }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ number_format($item->price, 2, ',', '.') }}</td>
                <td>{{ $item->pcs }}</td>
                <td></td> <!-- Rucno popunjavanje -->
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

<script type="text/php">
    if (isset($pdf))
    { $pdf->page_text(495, 798, "Strana {PAGE_NUM} od {PAGE_COUNT}", null, 5); }
</script>

</body>
</html>

