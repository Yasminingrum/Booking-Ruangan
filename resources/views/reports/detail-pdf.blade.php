<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Detail Peminjaman</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #4a5568; color: white; font-size: 9px; }
        .footer { margin-top: 30px; text-align: right; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DETAIL PEMINJAMAN RUANGAN</h1>
        <p>Periode: {{ $date_from }} - {{ $date_to }}</p>
        <p>Total: {{ $total }} peminjaman</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Ruangan</th>
                <th>Peminjam</th>
                <th>Waktu</th>
                <th>Tujuan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $index => $booking)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</td>
                <td>{{ $booking->room->name }}</td>
                <td>{{ $booking->user->name }}</td>
                <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                <td>{{ Str::limit($booking->purpose, 30) }}</td>
                <td>{{ ucfirst($booking->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak: {{ $generated_at }} oleh {{ $generated_by }}</p>
    </div>
</body>
</html>
