<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Sold OR Stagnant Products Report Export</title>
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

    </style>
</head>
<body>

    <h2>تقرير المنتجات الراكدة والمباعة</h2>

    <div class="section-title">تفاصيل التقرير</div>


    <table border="1" width="100%">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية الكلية المباعة</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data) && count($data) > 0)

            @foreach($data as $product)
            <tr>
                <td>{{ $product['product_name'] }}</td>
                <td>{{ $product['total_sold'] }}</td>
                <td>{{ $product['status'] == 'sold' ? 'مباعة' : 'راكدة' }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="4" class="no-data">لا يوجد بيانات للعرض</td>
            </tr>
            @endif
        </tbody>
    </table>

    <footer>
        تم توليد التقرير في تاريخ: {{ now()->format('Y-m-d H:i') }}
    </footer>
</body>
</html>
