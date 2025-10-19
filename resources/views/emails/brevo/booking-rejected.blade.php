<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Status Pengajuan</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dddddd;
        }
        .header {
            background-color: #005A9E;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content h1 {
            color: #333333;
            font-size: 24px;
        }
        .details {
            background-color: #f9f9f9;
            border-left: 4px solid #FF9800;
            padding: 15px;
            margin: 20px 0;
        }
        .details ul {
            padding-left: 18px;
        }
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            background-color: #777777;
            color: #ffffff !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://static.wixstatic.com/media/07639e_83549958900b44ad9fea05d99e380dd5~mv2.png/v1/fill/w_559,h_512,al_c/07639e_83549958900b44ad9fea05d99e380dd5~mv2.png" alt="Logo Sekolah Palembang Harapan">
        </div>
        <div class="content">
            <h1>Pembaruan Status Pengajuan Anda</h1>
            <p>Yth. <strong>{{ $nama_user }}</strong>,</p>
            <p>Terima kasih banyak atas pengajuan yang telah Anda kirimkan. Kami sangat mengapresiasi waktu dan usaha yang Anda curahkan.</p>
            <p>Setelah melalui proses peninjauan yang teliti, kami harus menginformasikan bahwa pengajuan Anda belum dapat kami setujui untuk saat ini.</p>
            
            <div class="details">
                <strong>Rincian Pengajuan:</strong><br>
                <ul>
                    <li><strong>Nama Pengajuan:</strong> {{ $nama_proyek }}</li>
                    <li><strong>Tanggal Diajukan:</strong> {{ $tanggal_pengajuan }}</li>
                    @isset($alasan_penolakan)
                        <li><strong>Alasan Penolakan:</strong> {{ $alasan_penolakan }}</li>
                    @endisset
                </ul>
            </div>

            <p>Kami memahami bahwa ini mungkin bukan berita yang Anda harapkan. Kami mendorong Anda untuk terus berinovasi dan menantikan partisipasi Anda pada kesempatan berikutnya.</p>

            <div class="button-container">
                <a href="{{ $button_url }}" class="button">Hubungi Kami</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2025 Sekolah Palembang Harapan. Semua Hak Cipta Dilindungi.</p>
            <p>Ini adalah email otomatis, mohon untuk tidak membalas secara langsung.</p>
        </div>
    </div>
</body>
</html>
