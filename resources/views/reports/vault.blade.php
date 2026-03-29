<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "amiri", sans-serif;
            direction: rtl;
        }

        table {
            direction: rtl;
        }

        th,
        td {
            text-align: right;
        }

        body {
            color: #333;
            font-size: 11px;
            margin: 20px;
            background-color: #FBFBFB;

            direction: rtl;
            text-align: right;

        }

        h1,
        h2 {
            text-align: center;
            color: #346ABE;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .section-title {
            background: #346ABE;
            color: #fff;
            padding: 8px 10px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            table-layout: fixed;
            /* Ensures all columns render correctly in PDF */
            word-wrap: break-word;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #FBFBFB;
            color: #070000;
            font-weight: bold;
            text-transform: capitalize;
            font-size: 11px;
        }

        td {
            background-color: #fff;
        }

        tr:nth-child(even) td {
            background-color: #F9F9F9;
        }

        .filters-table td {
            border: none;
            padding: 4px 8px;
        }

        .filters-subtable {
            width: 95%;
            margin: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filters-subtable td {
            border: none;
            padding: 3px 6px;
            font-size: 10px;
        }

        .summary-table th {
            width: 220px;
            background: #F8F8F8;
            color: #222;
        }

        .summary-table td {
            background: #fff;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
        }

        .nested {
            font-size: 10px;
            color: #555;
            white-space: pre-wrap;
        }

        /* Footer */
        footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }


        tfoot td {
            font-weight: bold;
            background: #f5f5f5;
        }

        .info {
            margin-bottom: 15px;
        }

    </style>
</head>
<body>


    <h2>تقرير الخزائن</h2>

    <div class="info">
        <p><strong>من:</strong> {{ $data['from'] }}</p>
        <p><strong>إلى:</strong> {{ $data['to'] }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المالك</th>
                <th>الرصيد الحالي</th>
                <th>إجمالي الداخل</th>
                <th>إجمالي الخارج</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data['vaults']) && count($data['vaults']) > 0)

            @foreach($data['vaults'] as $index => $vault)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $vault['owner'] }}</td>
                <td>{{ $vault['balance'] }}</td>
                <td>{{ $vault['total_in'] }}</td>
                <td>{{ $vault['total_out'] }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="6" class="no-data">لا يوجد بيانات للعرض</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">إجمالي الأرصدة</td>
                <td>{{ $data['total_balances'] }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <footer>
        تم توليد التقرير في تاريخ: {{ now()->format('Y-m-d H:i') }}
    </footer>
</body>
</html>
