<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Sifarnik</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .page {
            page-break-after: always;
            padding: 10px;
        }

        .layout-table {
            width: 100%;
        }

        .column-table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            line-height: 0.6;
        }

        th, td {
            border: 1px solid #667;
            padding: 1px 2px;
            text-align: left;
            font-size: 7px;
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
                                    <td style="font-size: 9px;">{{ $item->article }}</td>
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
            { $pdf->page_text(495, 803, "Strana {PAGE_NUM} od {PAGE_COUNT}", null, 8); }
        </script>
</div>
</body>

</html>
