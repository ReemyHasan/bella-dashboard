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

    </style>
</head>
<body>
    <h2>تقرير طلبات المسوق</h2>

    <p><strong>المسوق:</strong>{{ $data['marketer_name'] }}  </p>
    <p><strong>التاريخ:</strong> {{ $data['date'] }}</p>
    <p><strong>عدد الطلبات:</strong> {{ $data['total_orders'] }}</p>


    <table>
        <thead>
            <tr>
                <th>معرف الطلب</th>
                <th>الرقم التسلسلي للطلب</th>
                <th>الإضافات</th>
                <th>الخصم</th>
                <th>طريقة الخصم</th>
                <th>السعر بالعملة المباعة</th>
                <th>العملة</th>
                <th>قيمة التحويل</th>
                <th>السعر بعد تحويل العملة</th>
                <th>حالة الطلب</th>
                <th>المنتجات</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data['orders']) && count($data['orders']) > 0)

            @foreach($data['orders'] as $order)
            <tr>
                <td>{{ $order['order_id'] }}</td>
                <td>{{ $order['order_number'] }}</td>
                <td>{{ $order['additional_tips'] }}</td>
                <td>{{ $order['deduction_amount'] }}</td>
                <td>{{ $order['deduction_type'] }}</td>
                <td>{{ $order['price_before_exchange'] }}</td>
                <td>{{ $order['currency'] }}</td>
                <td>{{ $order['current_exchange_rate'] }}</td>
                <td>{{ $order['price_after_exchange'] }}</td>
                <td>{{ $order['order_status'] }}</td>
                <td>{{ $order['products'] }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="6" class="no-data">لا يوجد بيانات للعرض</td>
            </tr>
            @endif
        </tbody>
    </table>

    <footer>
        تم توليد التقرير في تاريخ: {{ now()->format('Y-m-d H:i') }}
    </footer>
</body>
</html>
