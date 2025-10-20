<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan Peminjaman Ruangan</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
        }
        .info-box {
            background: #f5f5f5;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SEKOLAH PALEMBANG HARAPAN</h1>
        <p>Laporan Ringkasan Peminjaman Ruangan</p>
        <p>Periode: {{ $date_from }} - {{ $date_to }}</p>
    </div>

    <div class="info-box">
        <table style="border: none;">
            <tr>
                <td style="border: none;"><strong>Total Booking:</strong></td>
                <td style="border: none;">{{ $summary['total_bookings'] }}</td>
                <td style="border: none;"><strong>Disetujui:</strong></td>
                <td style="border: none;">{{ $summary['approved'] }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Ditolak:</strong></td>
                <td style="border: none;">{{ $summary['rejected'] }}</td>
                <td style="border: none;"><strong>Dibatalkan:</strong></td>
                <td style="border: none;">{{ $summary['cancelled'] }}</td>
            </tr>
        </table>
    </div>

    <h3>Detail Per Ruangan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Ruangan</th>
                <th>Kategori</th>
                <th>Jumlah Booking</th>
                <th>Total Jam</th>
                <th>Utilisasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roomStats as $index => $stat)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $stat['room']->name }}</td>
                <td>{{ \App\Http\Controllers\ReportController::ROOM_TYPE_LABELS[$stat['room']->type] ?? '-' }}</td>
                <td>{{ $stat['total_bookings'] }}</td>
                <td>{{ $stat['total_hours'] }} jam</td>
                <td>{{ $stat['utilization'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generated_at }}</p>
        <p>Dicetak oleh: {{ $generated_by }}</p>
    </div>
</body>
</html>
