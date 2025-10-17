<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi DO Selesai</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background-color: #0d6efd; color: white; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .content p { margin-bottom: 10px; }
        .footer { margin-top: 20px; font-size: 0.8em; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notifikasi Verifikasi Selesai</h2>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Proses verifikasi dan scan untuk <strong>Delivery Order (DO)</strong> berikut telah selesai:</p>
            <ul>
                <li><strong>Nomor DO:</strong> {{ $doData['do_number'] }}</li>
                <li><strong>Pelanggan:</strong> {{ $doData['customer'] }}</li>
                <li><strong>Tujuan (Ship To):</strong> {{ $doData['ship_to'] }}</li>
                <li><strong>Nomor Kontainer:</strong> {{ $doData['container_no'] }}</li>
            </ul>
            <p>Semua item telah berhasil diverifikasi sesuai dengan kuantitas yang ada di dalam sistem.</p>
            <p>Terima kasih.</p>
        </div>
        <div class="footer">
            <p>Ini adalah email otomatis. Mohon untuk tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
