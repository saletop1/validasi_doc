<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OutstandingDoController extends Controller
{
    /**
     * Mengambil data DO yang menunggu dari tabel lokal.
     * Dibuat statis agar mudah dipanggil oleh DeliveryOrderController.
     */
    public static function getWaitingDos()
    {
        try {
            // Ambil data unik per DO, diurutkan dari yang paling baru
            return DB::table('outstanding_dos')
                ->select('delivery_number as VBELN', 'customer_name as NAME1', DB::raw('MIN(created_at) as created_at'))
                ->groupBy('delivery_number', 'customer_name')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (Throwable $e) {
            // Jika tabel belum ada, kembalikan koleksi kosong
            Log::warning('Gagal mengambil data waitingDos (kemungkinan tabel outstanding_dos belum ada): ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Menghubungi API Python untuk RFC Z_FM_YSDR039 dan menyimpan hasilnya.
     * Ini bisa dipanggil oleh cron job atau tombol manual.
     */
    public function fetchAndStoreOutstandingDos()
    {
        $locations = [
            'surabaya' => 1,
            'semarang' => 2,
        ];

        $sapUsername = 'auto_email'; // Sesuaikan
        $sapPassword = '11223344'; // Sesuaikan

        // PERHATIAN: Asumsi URL endpoint di Python. Sesuaikan jika berbeda.
        $pythonApiUrl = 'http://127.0.0.1:8009/api/sap/get_outstanding_dos'; // <-- GANTI INI JIKA PERLU

        $allResults = [];
        $fetchErrors = false;

        foreach ($locations as $locationName => $bagian) {
            try {
                Log::info("Memulai fetch outstanding DO untuk: $locationName (P_BAGIAN: $bagian)");

                $response = Http::timeout(60)->post($pythonApiUrl, [
                    'rfc_name' => 'Z_FM_YSDR039', // Mengirim nama RFC
                    'username' => $sapUsername,
                    'password' => $sapPassword,
                    'P_BAGIAN' => $bagian, // Parameter khusus RFC ini
                ]);

                if (!$response->successful()) {
                    Log::error("Gagal menghubungi API Python untuk $locationName", ['status' => $response->status(), 'body' => $response->body()]);
                    $fetchErrors = true;
                    continue;
                }

                $sapData = $response->json();
                $tData1 = $sapData['data']['T_DATA1'] ?? [];

                if (empty($tData1)) {
                    Log::warning("Data T_DATA1 kosong dari SAP untuk $locationName.");
                    continue;
                }

                // Format data untuk database
                foreach ($tData1 as $item) {
                    $allResults[] = [
                        'location' => $locationName,
                        'customer_name' => $item['NAME1'] ?? null,
                        'plant' => $item['WERKS'] ?? null,
                        'delivery_number' => $item['VBELN'] ?? null,
                        'item_number' => $item['POSNR'] ?? null,
                        'material_number' => $item['MATNR'] ?? null,
                        'material_description' => $item['MAKTX'] ?? null,
                        'qty_do' => (float)($item['LFIMG'] ?? 0),
                        'stock' => (float)($item['MENGEX'] ?? 0),
                        'stock_non_hu' => (float)($item['MENGEX1'] ?? 0),
                        'stock_hu' => (float)($item['MENGEX2'] ?? 0),
                        'qty_outstanding' => (float)($item['MENGEY'] ?? 0),
                        'percent_shortage' => (float)($item['MENGET'] ?? 0),
                        'percent_success' => (float)($item['MENGEU'] ?? 0),
                        'description' => $item['VTEXT'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } catch (Throwable $e) {
                Log::error("Exception saat fetch outstanding DO for $locationName: " . $e->getMessage());
                $fetchErrors = true;
            }
        }

        if (empty($allResults)) {
             // Hapus data lama jika fetch berhasil tapi tidak ada data baru
             if (!$fetchErrors) {
                 Log::info('Tidak ada data outstanding DO baru. Menghapus data lama...');
                 DB::table('outstanding_dos')->truncate();
             }
             return response()->json(['success' => !$fetchErrors, 'message' => 'Tidak ada data outstanding DO yang diterima.']);
        }

        // Simpan ke database menggunakan Upsert
        // Ini akan update jika VBELN+POSNR sudah ada, atau insert baru
        try {
            DB::transaction(function () use ($allResults) {
                // Hapus semua data lama terlebih dahulu
                DB::table('outstanding_dos')->truncate();
                // Insert data baru
                // (Gunakan insert biasa karena kita sudah truncate)
                DB::table('outstanding_dos')->insert($allResults);
            });

            Log::info('Berhasil menyimpan ' . count($allResults) . ' baris outstanding DO.');
            return response()->json(['success' => true, 'message' => 'Data outstanding DO berhasil diperbarui.']);

        } catch (Throwable $e) {
            Log::error('Gagal menyimpan outstanding DO ke DB: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data ke database.'], 500);
        }
    }
}
