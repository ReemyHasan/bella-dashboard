<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report Export</title>
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


    <h2>تقرير الفرق</h2>

    <div class="section-title">تفاصيل التقرير</div>

   @foreach($data as $team)
    <div class="team">
        <h3>📌 {{ $team['team_name'] }}</h3>
        <p>المدير: {{ $team['manager_name'] }}</p>

        <div class="box">
            <strong>الفريق المباشر:</strong>
            @foreach($team['direct_team'] as $sub)
                <div class="box">
                    <p>👤 {{ $sub['sub_team_name'] }} ({{ $sub['leader'] }})</p>
                    <ul>
                        @foreach($sub['users'] as $user)
                            <li>{{ $user['name'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="box">
            <strong>الفرق الفرعية:</strong>
            @foreach($team['sub_teams'] as $sub)
                <div class="box">
                    <p>👤 {{ $sub['sub_team_name'] }} ({{ $sub['leader'] }})</p>
                    <ul>
                        @foreach($sub['users'] as $user)
                            <li>{{ $user['name'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

    <footer>
        تم توليد التقرير في تاريخ: {{ now()->format('Y-m-d H:i') }}
    </footer>
</body>
</html>
