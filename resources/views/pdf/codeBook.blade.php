<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Šifarnik SIN KOLOR</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .layout-table {
            width: 100%;
        }

        .column-table {
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

        table td {
            line-height: 0.73;
            font-size: 11px;
            font-weight: bold;
        }

        table th {
            border: 1px solid #667;
            padding: 3px 3px;
            text-align: left;
            font-size: 10px;
        }

        th {
            background-color: #eee;
        }

        .column {
            width: 33.33%;
            padding: 0 2px; /* ili čak 0 */
            margin: 0;
        }

    </style>
</head>

<body>
<script type="text/php">
    if (isset($pdf))
    { $pdf->page_text(36, 26, "Strana {PAGE_NUM} od {PAGE_COUNT}", null, 6);
        $pdf->page_text(120, 22, "ŠIFARNIK ROBE ZA 'SIN KOLOR' DOO SILBAŠ NA DAN:
    {{ \Carbon\Carbon::parse()->now()->timezone('Europe/Belgrade')->translatedFormat('j. F Y.  H:i') }}h", null, 9); }
</script>
<!-- GLAVNI SADRZAJ -->
@foreach($pages as $columns)
    <div class="page">
        <table class="layout-table">
            <tr>
                @foreach($columns as $column)
                    <td class="column">
                        <table class="column-table">
                            <thead>
                            <tr>
                                <th>Šifra</th>
                                <th>Opis</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($column as $item)
                                <tr>
                                    <td>{{ $item->code }}</td>
                                    <td>{{ $item->article }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
@endforeach

<div class="footer">
    <script type="text/php">
            if (isset($pdf))
            { $pdf->page_text(510, 795, "Str.{PAGE_NUM} od {PAGE_COUNT}", null, 7); }
        </script>
</div>
</body>

</html>
