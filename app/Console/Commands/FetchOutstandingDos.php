<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// Hapus controller, kita tidak membutuhkannya lagi
// use App\Http\Controllers\OutstandingDoController;
use Illuminate\Support\Facades\Log;
// Tambahkan library yang kita butuhkan
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchOutstandingDos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outstanding:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch outstanding DOs from SAP RFC (Z_FM_YSDR039) and store them in the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('[Cron Job] Memulai pengambilan data outstanding DO...');
        $this->info('Memulai pengambilan data outstanding DO...');

        // --- SEMUA LOGIKA PINDAH KE SINI ---
        $pythonApiUrl = 'http://127.0.0.1:8009/api/sap/get_outstanding_dos';
        $sapUsername = 'auto_email';
        $sapPassword = '11223344';

        try {
            // 1. Panggil API Python
            $response = Http::timeout(120)->post($pythonApiUrl, [ // Beri waktu 2 menit
                'username' => $sapUsername,
                'password' => $sapPassword,
            ]);

            if (!$response->successful()) {
                $errorMsg = 'Gagal menghubungi API Python: ' . $response->status();
                Log::error('[Cron Job] ' . $errorMsg, ['response' => $response->body()]);
                $this->error($errorMsg);
                return Command::FAILURE;
            }

            // Log seluruh body respons untuk melihat apa yang sebenarnya diterima
            Log::info('[Cron Job] Respons mentah dari Python: ' . $response->body());


            // 2. Ambil data dari respons - DENGAN LOGIKA BARU
            $data = $response->json();
            $tData1 = []; // Default kosong

            if (isset($data['data']['t_data1']) && is_array($data['data']['t_data1'])) {
                 // Struktur yang diharapkan: {"data": {"t_data1": [...]}}
                $tData1 = $data['data']['t_data1'];
                Log::info('[Cron Job] Menemukan data di $data["data"]["t_data1"]');

            } elseif (isset($data['data']) && is_array($data['data'])) {
                // Struktur fallback (sesuai log Anda): {"data": [...]}
                 // Cek apakah elemen pertama punya kunci SAP (misal 'VBELN') untuk memastikan ini bukan array kosong biasa
                 if (!empty($data['data']) && isset($data['data'][0]['VBELN'])) {
                     $tData1 = $data['data'];
                     Log::info('[Cron Job] Menemukan data langsung di $data["data"]');
                 } else {
                     Log::warning('[Cron Job] Menemukan $data["data"] tapi isinya bukan array item SAP yang valid.');
                 }
            } else {
                 Log::warning('[Cron Job] Struktur data JSON tidak dikenali atau data utama tidak ditemukan.', $data ?? ['error' => 'Data JSON tidak valid']);
            }


            if (empty($tData1)) {
                // Log data yang sudah diparsing jika tData1 kosong
                Log::warning('[Cron Job] tData1 kosong setelah pengecekan struktur. Data yang diparsing:', $data ?? ['error' => 'Data JSON tidak valid']);
                $this->warn('Tidak ada data outstanding DO yang diterima dari Python/SAP.');
                Log::warning('[Cron Job] Tidak ada data outstanding DO yang diterima dari Python/SAP.');
                // --- PERUBAHAN --- Kosongkan tabel meskipun tidak ada data baru
                Log::info('[Cron Job] Mengosongkan tabel outstanding_dos karena tidak ada data baru.');
                DB::table('outstanding_dos')->truncate();
                // --- AKHIR PERUBAHAN ---
                return Command::SUCCESS; // Sukses, tapi tidak ada data
            }

            $totalData = count($tData1);
            Log::info("[Cron Job] Menerima {$totalData} baris. Memulai penyimpanan ke database...");
            $this->info("Menerima {$totalData} baris dari Python/SAP..."); // Info ke terminal

            // --- PERUBAHAN: TRUNCATE DI LUAR TRANSAKSI ---
            // Kosongkan tabel dulu di luar transaksi
            Log::info('[Cron Job] Mengosongkan tabel outstanding_dos...');
            DB::table('outstanding_dos')->truncate();
            Log::info('[Cron Job] Tabel outstanding_dos berhasil dikosongkan.');
            // --- AKHIR PERUBAHAN ---

            // 3. Simpan ke Database (Hanya Insert)
            DB::transaction(function () use ($tData1) {
                // Hapus truncate dari sini

                $insertData = [];
                foreach ($tData1 as $item) {
                    // --- PERBAIKAN NAMA KOLOM SESUAI SCREENSHOT DATABASE ---
                    $insertData[] = [
                        // 'name' => ...  -> Nama kolom di DB adalah 'customer_name', bukan 'name'
                        'customer_name' => $item['NAME1'] ?? null,
                        'plant'         => $item['WERKS'] ?? null,
                        'delivery_number' => $item['VBELN'] ?? null, // Di DB 'delivery_number'
                        'item_number'     => $item['POSNR'] ?? null, // Di DB 'item_number'
                        'material_number' => $item['MATNR'] ?? null, // Di DB 'material_number'
                        'material_description' => $item['MAKTX'] ?? null, // Di DB 'material_description'
                        'qty_do'        => isset($item['LFIMG']) ? (float)$item['LFIMG'] : 0,
                        'stock'         => isset($item['MENGEX']) ? (float)$item['MENGEX'] : 0,
                        'stock_non_hu'  => isset($item['MENGEX1']) ? (float)$item['MENGEX1'] : 0,
                        'stock_hu'      => isset($item['MENGEX2']) ? (float)$item['MENGEX2'] : 0,
                        'qty_outstanding' => isset($item['MENGEY']) ? (float)$item['MENGEY'] : 0,
                        'percent_shortage' => isset($item['MENGET']) ? (float)$item['MENGET'] : 0,
                        'percent_success'  => isset($item['MENGEU']) ? (float)$item['MENGEU'] : 0,
                        // 'shipping_point_desc' => ... -> Nama kolom di DB adalah 'description', bukan 'shipping_point_desc'
                        'description' => $item['VTEXT'] ?? null,
                        // Kolom 'location' tidak ada di data SAP, jadi biarkan NULL
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                    // --- AKHIR PERBAIKAN NAMA KOLOM ---
                }

                // Insert data dalam batch (lebih efisien)
                Log::info('[Cron Job] Memulai insert data batch...');
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('outstanding_dos')->insert($chunk);
                }
                 Log::info('[Cron Job] Selesai insert data batch.');
            }); // Akhir DB::transaction

            $message = "Data outstanding DO berhasil diambil dan disimpan. Total: {$totalData}";
            Log::info('[Cron Job] ' . $message);
            $this->info($message); // Pesan sukses baru
            // --- AKHIR DARI LOGIKA ---

            return Command::SUCCESS;

        } catch (Throwable $e) { // Gunakan Throwable untuk menangkap semua error
            Log::error('[Cron Job] Terjadi error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('Terjadi error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

