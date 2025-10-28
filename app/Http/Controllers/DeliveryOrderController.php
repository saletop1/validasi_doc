<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Pastikan Log diimpor
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCompleted;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Throwable;
use Illuminate\Support\Collection; // Import Collection
// Import Controller baru
use App\Http\Controllers\OutstandingDoController;


class DeliveryOrderController extends Controller
{
    // ... (fungsi verifyIndex tidak berubah) ...
     public function verifyIndex()
    {
        return view('delivery-order.verify');
    }

    public function historyIndex()
    {
        // 1. Ambil DO yang sudah selesai
        $completedDos = DB::table('do_list')
            ->whereNotNull('VERIFIED_AT')
            ->orderBy('VERIFIED_AT', 'desc')
            ->select('VBELN', 'NAME1', 'VERIFIED_AT') // Hanya ambil kolom yang dibutuhkan
            ->distinct('VBELN') // Pastikan unik
            ->get();

        // 2. Ambil DO yang sedang dalam proses (sudah ada scan tapi belum selesai)
        $inProgressDoNumbers = DB::table('scanned_items')
            ->whereNotIn('do_number', function ($query) {
                $query->select('VBELN')->from('do_list')->whereNotNull('VERIFIED_AT');
            })
            ->distinct('do_number')
            ->pluck('do_number');

        $inProgressDos = DB::table('do_list')
            ->whereIn('VBELN', $inProgressDoNumbers)
            ->orderBy('updated_at', 'desc') // Urutkan berdasarkan aktivitas terakhir
            ->select('VBELN', 'NAME1', 'updated_at') // Hanya ambil kolom yang dibutuhkan
            ->distinct('VBELN') // Pastikan unik
            ->get();


        // --- PERUBAHAN: Ambil data Menunggu dari tabel outstanding_dos ---
        $waitingDosData = DB::table('outstanding_dos')
            ->select('plant', 'delivery_number', 'customer_name', 'created_at') // Ambil kolom plant
             // ->orderBy('plant', 'asc') // Urutkan berdasarkan plant jika perlu
            ->orderBy('created_at', 'desc') // Urutkan berdasarkan waktu data dibuat
            ->distinct('delivery_number') // Hanya satu entri per DO
            ->get();

        // Kelompokkan berdasarkan plant
        $waitingDosGrouped = $waitingDosData->groupBy('plant');
        // --- AKHIR PERUBAHAN ---


        // 3. Kirim semua data ke view
        return view('delivery-order.history', [
            'completedDos' => $completedDos,
            'inProgressDos' => $inProgressDos,
            'waitingDos' => $waitingDosGrouped // Kirim data yang sudah dikelompokkan
        ]);
    }

    /**
     * Mengambil detail item yang sudah discan untuk sebuah DO, termasuk header.
     * Ditampilkan per baris item (POSNR) dan diurutkan berdasarkan POSNR.
     * Menangani kasus jika DO hanya ada di outstanding_dos.
     *
     * @param string $doNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function getScannedItemsForDO($doNumber)
    {
        try {
            // Coba cari header di do_list (untuk Selesai/Proses/Menunggu yg sudah masuk)
            $doHeader = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('NAME1 as customer', 'ADDRESS as address', 'SHIPTO as ship_to', 'VTEXT as shipping_point', 'BEZEI2 as ship_type', 'VERIFIED_AT')
                ->first();

            // --- PERUBAHAN LOGIKA: Handle jika tidak ada di do_list ---
            if (!$doHeader) {
                // Coba cari di outstanding_dos
                $outstandingHeader = DB::table('outstanding_dos')
                    ->where('delivery_number', $doNumber)
                    ->select('customer_name as customer')
                    ->first();

                if ($outstandingHeader) {
                    // Jika ditemukan di outstanding_dos, berarti statusnya "Menunggu" dan belum diproses
                    Log::info('Header DO tidak ditemukan di do_list, tapi ditemukan di outstanding_dos untuk DO: ' . $doNumber);
                    return response()->json([
                        'header' => [ // Buat header minimal
                            'customer' => $outstandingHeader->customer,
                            'address' => 'N/A', // Data lain tidak tersedia di outstanding_dos
                            'ship_to' => 'N/A',
                            'shipping_point' => 'N/A',
                            'ship_type' => 'N/A',
                            'VERIFIED_AT' => null,
                            'container_no' => 'N/A',
                            'status' => 'waiting' // Flag khusus untuk frontend
                        ],
                        'items' => [], // Tidak ada item scan
                        'summary' => ['total_order' => 0, 'total_scan' => 0] // Summary default
                    ]);
                } else {
                    // Jika tidak ditemukan di mana pun
                    Log::warning('Header DO tidak ditemukan di do_list maupun outstanding_dos untuk DO: ' . $doNumber);
                    return response()->json(['error' => 'Data Delivery Order tidak ditemukan.'], 404);
                }
            }
            // --- AKHIR PERUBAHAN LOGIKA ---

            // Jika $doHeader ditemukan di do_list, lanjutkan logika normal
            $containerInfo = DB::table('do_list_details')
                 ->where('DELV', $doNumber)
                 ->whereNotNull('V_NO_CONT')
                 ->select('V_NO_CONT')
                 ->first();
             $doHeader->container_no = $containerInfo->V_NO_CONT ?? 'N/A';
             // Tambahkan status 'processed' jika berasal dari do_list
             $doHeader->status = 'processed';


            // Ambil setiap baris item unik dari do_list, urutkan by POSNR numerik
            $doListItems = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR', 'MAKTX as description', 'POSNR', DB::raw('CAST(POSNR AS UNSIGNED) as posnr_int'), 'LFIMG as qty_order')
                ->orderBy('posnr_int', 'asc') // Urutkan berdasarkan POSNR numerik
                ->get();


             if ($doListItems->isEmpty()) {
                 Log::warning('Tidak ada item ditemukan di do_list untuk DO: ' . $doNumber);
                 // Kirim header yang sudah ada, tapi item dan summary kosong
                 return response()->json([
                     'header' => $doHeader,
                     'items' => [],
                     'summary' => ['total_order' => 0, 'total_scan' => 0]
                 ]);
             }

            // Ambil total scan per item (material + posnr)
            $scannedData = DB::table('scanned_items')
                ->where('do_number', $doNumber)
                ->whereNotNull('material_number')
                ->whereNotNull('item_number')
                ->select('material_number', 'item_number', DB::raw('SUM(qty_scanned) as total_qty_scan'))
                ->groupBy('material_number', 'item_number')
                ->get();

            // Buat peta scan dengan kunci "MATNR(formatted)-POSNR(formatted)"
            $scannedCountsMap = collect($scannedData)->mapWithKeys(function ($item) {
                $materialNumber = (string)($item->material_number ?? '');
                $itemNumber = (string)($item->item_number ?? ''); // POSNR
                if ($materialNumber === '' || $itemNumber === '') return [];

                // Format key sebelum lookup (hapus leading zero jika numerik)
                $matKey = ctype_digit($materialNumber) ? ltrim($materialNumber, '0') : $materialNumber;
                $itemKey = ctype_digit($itemNumber) ? ltrim($itemNumber, '0') : $itemNumber;
                return [$matKey . '-' . $itemKey => (int)$item->total_qty_scan];
            });


            $totalOrder = 0;

            // Gabungkan data dan berikan nomor urut berdasarkan index setelah diurutkan
            $finalResults = $doListItems->map(function ($item, $key) use ($scannedCountsMap, &$totalOrder, $doNumber) { // $key adalah index setelah orderBy
                $matnr = (string)($item->MATNR ?? '');
                $posnr = (string)($item->POSNR ?? ''); // Gunakan POSNR asli (string)

                if ($matnr === '' || $posnr === '') {
                    Log::warning('MATNR atau POSNR kosong ditemukan.', ['do_number' => $doNumber, 'index' => $key]);
                    return null; // Tandai untuk difilter
                }

                 // Format key untuk lookup (hapus leading zero jika numerik)
                $materialKeyFormatted = ctype_digit($matnr) ? ltrim($matnr, '0') : $matnr;
                $itemKeyFormatted = ctype_digit($posnr) ? ltrim($posnr, '0') : $posnr;
                $lookupKey = $materialKeyFormatted . '-' . $itemKeyFormatted;


                $qtyScan = $scannedCountsMap->get($lookupKey, 0);

                $itemData = [
                     // Aktifkan kembali 'no'
                    'no' => $key + 1, // Nomor urut berdasarkan index collection ($key)
                    'material_number' => $materialKeyFormatted, // Kirim yang sudah diformat
                    'description' => $item->description ?? 'N/A',
                    'qty_order' => (int)$item->qty_order,
                    'qty_scan' => $qtyScan,
                ];

                $totalOrder += $itemData['qty_order'];

                return $itemData;
            })->filter() // Hapus item null jika ada
              ->values(); // Reindex collection setelah filter


            $grandTotalScan = DB::table('scanned_items')
                                ->where('do_number', $doNumber)
                                ->sum('qty_scanned');

            if ($finalResults->isEmpty() && !$doListItems->isEmpty()) {
                 Log::warning('Hasil penggabungan detail riwayat kosong.', ['do_number' => $doNumber]);
            }

            return response()->json([
                'header' => $doHeader,
                'items' => $finalResults, // Kirim hasil yang sudah diurutkan per POSNR dan diberi nomor urut
                'summary' => [
                    'total_order' => $totalOrder,
                    'total_scan' => (int)$grandTotalScan
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Gagal mengambil detail riwayat: ' . $e->getMessage(), [
                'do_number' => $doNumber,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString() // Sertakan trace untuk debug
            ]);
            // Kirim pesan error yang lebih informatif ke frontend
            return response()->json(['error' => 'Gagal memuat data detail: ' . $e->getMessage()], 500);
        }
    }


    // ... (fungsi search, saveSapDataToLocal, scan, sendCompletionEmail tidak berubah) ...
    public function search(Request $request)
    {
        $validated = $request->validate(['do_number' => 'required|string|max:20']);
        $doNumber = $validated['do_number'];

        $existingDO = DB::table('do_list')->where('VBELN', $doNumber)->first();
        if ($existingDO && !is_null($existingDO->VERIFIED_AT)) {
            return response()->json([
                'success' => false,
                'status' => 'completed',
                'message' => "Verifikasi untuk DO {$doNumber} sudah selesai pada " . Carbon::parse($existingDO->VERIFIED_AT)->timezone('Asia/Jakarta')->format('d-m-Y H:i')
            ], 200);
        }

        try {
            $sapUsername = 'auto_email'; // Langsung gunakan value jika tidak dari .env
            $sapPassword = '11223344'; // Langsung gunakan value jika tidak dari .env
            Log::info('Mengirim permintaan ke API Python', [
                'username' => $sapUsername, // Gunakan variabel lokal
                'P_VBELN' => $doNumber
            ]);


            $pythonApiUrl = 'http://127.0.0.1:8009/api/sap/get_do_details';
            Log::info('Menghubungi API Python di: ' . $pythonApiUrl);

            $response = Http::timeout(30)->post($pythonApiUrl, [ // Gunakan URL dari variabel
                'username' => $sapUsername,
                'password' => $sapPassword,
                'P_VBELN' => $doNumber,
            ]);

             if (!$response->successful()) {
                 $errorData = $response->json();
                 $errorMessage = $errorData['message'] ?? 'Gagal menghubungi API SAP/Python.';
                 Log::error('Gagal saat menghubungi API Python: ' . $errorMessage, ['status_code' => $response->status()]);
                 return response()->json(['success' => false, 'message' => $errorMessage], $response->status());
             }

             $sapData = $response->json();
             $tData = $sapData['data']['t_data'] ?? [];
             $tData2 = $sapData['data']['t_data2'] ?? [];

             if (empty($tData)) {
                 Log::warning("Data T_DATA kosong dari SAP untuk DO: " . $doNumber);
                 return response()->json(['success' => false, 'message' => "Data untuk DO {$doNumber} tidak ditemukan di SAP."], 404);
             }

             $this->saveSapDataToLocal($doNumber, $tData, $tData2);


        } catch (Throwable $e) {
            Log::error('Gagal saat menghubungi API Python: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung ke server SAP/Python.'], 500);
        }

         try {
             $doHeader = DB::table('do_list')
                 ->where('VBELN', $doNumber)
                 ->select('NAME1 as customer', 'ADDRESS as address', 'VTEXT as shipping_point', 'SHIPTO as ship_to', 'BEZEI2 as ship_type')
                 ->first();

             if (!$doHeader) {
                 Log::error("Data Header DO tidak ditemukan di DB Lokal setelah save.", ['do_number' => $doNumber]);
                 return response()->json(['success' => false, 'message' => "Delivery Order {$doNumber} tidak ditemukan di database lokal."]);
             }

             $containerInfo = DB::table('do_list_details')
                 ->where('DELV', $doNumber)
                 ->whereNotNull('V_NO_CONT')
                 ->select('V_NO_CONT')
                 ->first();

             // Mengambil Item DO per baris POSNR
             $doItems = DB::table('do_list')
                 ->where('VBELN', $doNumber)
                 ->select('MATNR', 'MAKTX', 'VBELN as do_no', 'POSNR', DB::raw('CAST(POSNR AS UNSIGNED) as posnr_int'), 'CHARG2 as batch_no', 'LFIMG as qty_order')
                 ->orderBy('posnr_int', 'asc') // Urutkan berdasarkan POSNR numerik
                 ->get();


              if ($doItems->isEmpty()) {
                   Log::warning("Data Item DO kosong di DB Lokal setelah save.", ['do_number' => $doNumber]);
              }

             $allHuDetails = DB::table('do_list_details')
                 ->where('DELV', $doNumber)
                 ->select('DELV', 'ITEM', 'EXIDV as hu_no', 'ITEM2 as item_hu', 'CHARG2 as charg2', 'VEMNG as qty_hu')
                 ->get();

             $huMapByPosnr = $allHuDetails->groupBy('ITEM'); // Kunci adalah POSNR asli (string)

             $multiItemHuMap = collect($allHuDetails)
                ->groupBy('hu_no')
                ->filter(function ($details) {
                    return $details->pluck('ITEM')->unique()->count() > 1;
                })
                ->map(function ($details) {
                    // Ambil POSNR unik, format (hapus leading zero), kembalikan sbg array
                    return $details->pluck('ITEM')->map(function($posnr){
                         // Pastikan POSNR adalah string sebelum ctype_digit
                         $posnrStr = (string)$posnr;
                         return ctype_digit($posnrStr) ? ltrim($posnrStr, '0') : $posnrStr;
                    })->unique()->values()->all();
                });



             // Mapping langsung dari $doItems (tidak perlu grouping lagi)
             $formattedItems = $doItems->map(function ($item) use ($huMapByPosnr) {
                 $posnrKey = $item->POSNR; // Gunakan POSNR asli (string) untuk lookup HU
                 if ($posnrKey === null) return null; // Lewati jika POSNR null

                 // Pastikan MATNR adalah string sebelum ctype_digit
                 $matnrStr = (string)$item->MATNR;
                 $material = ctype_digit($matnrStr) ? ltrim($matnrStr, '0') : $matnrStr;

                 // Format POSNR untuk TAMPILAN
                 // Pastikan POSNR adalah string sebelum ctype_digit
                 $posnrStrDisplay = (string)$posnrKey;
                 $itemNoDisplay = ctype_digit($posnrStrDisplay) ? ltrim($posnrStrDisplay, '0') : $posnrStrDisplay;



                 $huDetailsForItem = $huMapByPosnr->get($posnrKey, collect())->map(function($detail) {
                     // Format semua nomor yang relevan (hapus leading zero jika numerik)
                     $itemPosnr = (string)($detail->ITEM ?? '');
                     $huNo = (string)($detail->hu_no ?? '');
                     $itemHu = (string)($detail->item_hu ?? '');

                     return [
                         'delivery' => $detail->DELV ?? null,
                         'item' => ctype_digit($itemPosnr) ? ltrim($itemPosnr, '0') : $itemPosnr, // POSNR terkait HU
                         'hu_no' => ctype_digit($huNo) ? ltrim($huNo, '0') : $huNo,
                         'item_hu' => ctype_digit($itemHu) ? ltrim($itemHu, '0') : $itemHu,
                         'charg2' => $detail->charg2,
                         'qty_hu' => (int)$detail->qty_hu,
                     ];
                 });

                 return [
                     'material' => $material,
                     'description' => $item->MAKTX ?? 'N/A',
                     'do_no' => $item->do_no,
                     'item_no' => $itemNoDisplay, // POSNR yang sudah diformat untuk tampilan
                     'batch_no' => $item->batch_no,
                     'qty_order' => (int)$item->qty_order, // Qty untuk baris POSNR ini
                     'is_hu' => $huDetailsForItem->isNotEmpty(),
                     'hu_details' => $huDetailsForItem->values()->all(),
                 ];
             })->filter()->values(); // Hapus null dan reindex


             $progressData = DB::table('scanned_items')->where('do_number', $doNumber)->get();

            $scannedHus = $progressData->filter(function ($item) {
                 // Periksa apakah batch_number ada dan tidak kosong ATAU scanned_code ada, tidak kosong, dan panjangnya > 10
                 return (!empty($item->batch_number)) || (!empty($item->scanned_code) && strlen((string)$item->scanned_code) > 10);
             })
             ->pluck('scanned_code') // Ambil scanned_code
             ->filter() // Hapus nilai null atau kosong
             ->unique() // Ambil yang unik
             ->values() // Reindex
             ->map(function($hu){
                 // Format HU (hapus leading zero jika numerik)
                 $huStr = (string)$hu;
                 return ctype_digit($huStr) ? ltrim($huStr, '0') : $huStr;
             })
             ->all();


             // Kunci group = MATNR(formatted)-POSNR(formatted)
             $scannedCounts = $progressData->groupBy(function($item) {
                 // Pastikan material_number dan item_number adalah string sebelum ctype_digit
                 $materialStr = (string)($item->material_number ?? null);
                 $itemNoStr = (string)($item->item_number ?? null); // POSNR dari scan

                 if ($materialStr === '' || $itemNoStr === '') return null; // Jika salah satu kosong, abaikan

                 $material = ctype_digit($materialStr) ? ltrim($materialStr, '0') : $materialStr;
                 $itemNo = ctype_digit($itemNoStr) ? ltrim($itemNoStr, '0') : $itemNoStr;

                 return $material . '-' . $itemNo;
             })
             ->filter(function($group, $key) { return $key !== null; }) // Hapus group yang kuncinya null
             ->mapWithKeys(function ($group, $key) {
                 // Jumlahkan qty_scanned untuk group ini
                 return [$key => $group->sum('qty_scanned')];
             });



             $progress = [
                 'hus' => $scannedHus,
                 'counts' => (object) $scannedCounts->toArray(), // Pastikan dikirim sbg objek
             ];


             $data = [
                 'do_number' => $doNumber,
                 'customer' => $doHeader->customer,
                 'address' => $doHeader->address,
                 'shipping_point' => $doHeader->shipping_point,
                 'ship_to' => $doHeader->ship_to,
                 'ship_type' => $doHeader->ship_type,
                 'container_no' => $containerInfo->V_NO_CONT ?? null,
                 'items' => $formattedItems, // Data item sekarang per baris POSNR
                 'progress' => $progress,
                 // Format HU di multiItemHuMap sebelum mengirim ke frontend
                 'multiItemHuMap' => $multiItemHuMap->mapWithKeys(function ($itemNos, $huNo) {
                      $huNoStr = (string)$huNo;
                      $formattedHu = ctype_digit($huNoStr) ? ltrim($huNoStr, '0') : $huNoStr;
                      return [$formattedHu => $itemNos]; // Kunci adalah HU yang diformat
                  })->toArray(),

             ];

             return response()->json(['success' => true, 'data' => $data]);

         } catch (Throwable $e) {
             Log::error('Error saat mengambil data dari DB lokal: ' . $e->getMessage() . ' pada baris ' . $e->getLine() . ' Trace: ' . $e->getTraceAsString());
             return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal pada server.'], 500);
         }
    }

    private function saveSapDataToLocal(string $doNumber, array $tData, array $tData2): void
    {
         DB::transaction(function () use ($doNumber, $tData, $tData2) {
             // Hapus data lama HANYA jika data baru tidak kosong
             if (!empty($tData)) {
                 DB::table('do_list')->where('VBELN', $doNumber)->delete();
             }
             if (!empty($tData2)) {
                 DB::table('do_list_details')->where('DELV', $doNumber)->delete();
             }

            $doListToInsert = [];
            foreach ($tData as $item) {
                 if (!isset($item['MATNR']) || !isset($item['POSNR'])) {
                     Log::warning('Data T_DATA dari SAP tidak lengkap (MATNR/POSNR kosong). Baris dilewati.', ['do_number' => $doNumber, 'item' => $item]);
                     continue; // Lewati baris ini jika MATNR atau POSNR kosong
                 }
                $doListToInsert[] = [
                    'WERKS' => $item['WERKS'] ?? null,
                    'VBELN' => $item['VBELN'] ?? null,
                    'POSNR' => $item['POSNR'] ?? null,
                    'LFIMG' => isset($item['LFIMG']) ? (float)$item['LFIMG'] : 0, // Pastikan float
                    'NAME1' => $item['NAME1'] ?? null,
                    'MATNR' => $item['MATNR'] ?? null,
                    'MAKTX' => $item['MAKTX'] ?? null,
                    'V_SO' => $item['V_SO'] ?? null,
                    'V_SOITEM' => $item['V_SOITEM'] ?? null,
                    'BSTNK' => $item['BSTNK'] ?? null,
                    'WADAT_IST' => $item['WADAT_IST'] ?? null,
                    'CHARG2' => $item['CHARG2'] ?? null,
                    'ADDRESS' => $item['ADDRESS'] ?? null,
                    'BEZEI2' => $item['BEZEI2'] ?? null,
                    'VTEXT' => $item['VTEXT'] ?? null,
                    'SHIPTO' => $item['SHIPTO'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    // Kolom VERIFIED_AT dan SCANNED_QTY tidak diisi di sini
                ];
            }
            if (!empty($doListToInsert)) {
                // Gunakan insert alih-alih upsert jika Anda selalu menghapus dulu
                DB::table('do_list')->insert($doListToInsert);
            }


            if (!empty($tData2)) {
                $uniqueHuDetails = [];
                $seenCombinations = [];

                foreach ($tData2 as $detail) {
                     // Periksa kunci penting
                     if (!isset($detail['EXIDV']) || !isset($detail['ITEM'])) {
                         Log::warning('Data T_DATA2 dari SAP tidak lengkap (EXIDV/ITEM kosong). Baris dilewati.', ['do_number' => $doNumber, 'detail' => $detail]);
                         continue; // Lewati baris ini
                     }
                    // Kunci unik sekarang HU + Item + Batch
                    $combinationKey = ($detail['EXIDV'] ?? '') . '-' . ($detail['ITEM'] ?? '') . '-' . ($detail['CHARG2'] ?? '');
                    if (!isset($seenCombinations[$combinationKey])) {
                        $uniqueHuDetails[] = [
                            'DELV' => $detail['DELV'] ?? null,
                            'ITEM' => $detail['ITEM'] ?? null,
                            'ITEM2' => $detail['ITEM2'] ?? null,
                            'KDAUF' => $detail['KDAUF'] ?? null,
                            'KDPOS' => $detail['KDPOS'] ?? null,
                            'MATNR' => $detail['MATNR'] ?? null,
                            'MAKTX' => $detail['MAKTX'] ?? null,
                            'BSTKD' => $detail['BSTKD'] ?? null,
                            'V_NO_CONT' => $detail['V_NO_CONT'] ?? null,
                            'CHARG2' => $detail['CHARG2'] ?? null,
                            'EXIDV' => $detail['EXIDV'] ?? null,
                            'VEMNG' => isset($detail['VEMNG']) ? (float)$detail['VEMNG'] : 0, // Pastikan float
                            'VEMEH' => $detail['VEMEH'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $seenCombinations[$combinationKey] = true;
                    } else {
                        // Log sebagai debug jika duplikat ditemukan (opsional)
                        Log::debug('Kombinasi HU-Item-Batch duplikat terdeteksi dan dilewati saat menyimpan T_DATA2.', [
                            'do_number' => $doNumber,
                            'hu' => $detail['EXIDV'] ?? 'N/A',
                            'item' => $detail['ITEM'] ?? 'N/A',
                            'batch' => $detail['CHARG2'] ?? 'N/A'
                        ]);
                    }
                }
                if (!empty($uniqueHuDetails)) {
                     // Gunakan insert karena sudah dihapus sebelumnya
                     DB::table('do_list_details')->insert($uniqueHuDetails);
                }
            }
        });
    }

    public function scan(Request $request)
    {
        try {
            $validated = $request->validate([
                'do_number' => 'required|string',
                'material_number' => 'required|string',
                'scanned_code' => 'required|string',
                'batch_number' => 'nullable|string',
                'item_number' => 'required|string', // Hanya string
                'qty_scanned' => 'required|integer|min:0' // Izinkan 0 untuk multi-item
            ]);

            DB::table('scanned_items')->insert([
                'do_number' => $validated['do_number'],
                'item_number' => $validated['item_number'], // Simpan POSNR
                'material_number' => $validated['material_number'],
                'scanned_code' => $validated['scanned_code'],
                'batch_number' => $validated['batch_number'],
                'qty_scanned' => $validated['qty_scanned'], // Qty dari scan
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Scan berhasil disimpan.']);
        } catch (Throwable $e) {
            Log::error('Error saat menyimpan scan: ' . $e->getMessage(), [
                'request_data' => $request->all() // Log data request jika error
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data scan.'], 500);
        }
    }


    public function sendCompletionEmail(Request $request)
    {
        $validated = $request->validate(['do_number' => 'required|string']);
        $doNumber = $validated['do_number'];

        try {
            // Ambil header dari do_list
            $doHeader = DB::table('do_list')->where('VBELN', $doNumber)->first();

            if ($doHeader) {
                // Hitung total qty order dari do_list untuk DO ini
                $totalQtyOrder = DB::table('do_list')->where('VBELN', $doNumber)->sum('LFIMG');
                // Hitung total qty scan dari scanned_items untuk DO ini
                $totalQtyScan = DB::table('scanned_items')->where('do_number', $doNumber)->sum('qty_scanned');


                // Update status di do_list
                DB::table('do_list')->where('VBELN', $doNumber)->update([
                    'VERIFIED_AT' => now(),
                    'SCANNED_QTY' => $totalQtyScan // Simpan total scan
                ]);


                // Ambil nomor kontainer (opsional, jika diperlukan)
                $containerNo = DB::table('do_list_details')->where('DELV', $doNumber)->value('V_NO_CONT');

                // Siapkan data untuk email
                $emailData = [
                    'do_number' => $doNumber,
                    'customer' => $doHeader->NAME1,
                    'ship_to' => $doHeader->SHIPTO,
                    'container_no' => $containerNo ?? 'N/A', // Sertakan nomor kontainer jika ada
                ];

                // Ambil daftar penerima dari .env
                $recipients = explode(',', env('MAIL_RECIPIENTS', 'default@example.com'));

                 // Kirim email jika ada penerima yang valid
                 if (!empty($recipients) && is_array($recipients)) {
                     $cleanedRecipients = array_map('trim', $recipients);
                     // Pastikan $cleanedRecipients tidak kosong setelah trim
                     $validRecipients = array_filter($cleanedRecipients, function($email) {
                         return filter_var($email, FILTER_VALIDATE_EMAIL);
                     });

                     if (!empty($validRecipients)) {
                         Mail::to($validRecipients)->send(new VerificationCompleted($emailData));
                         Log::info("Email notifikasi untuk DO {$doNumber} telah dimasukkan ke dalam antrian.");
                     } else {
                          Log::warning("Tidak ada alamat email penerima yang valid setelah dibersihkan untuk DO {$doNumber}.");
                     }
                 } else {
                     Log::warning("Tidak ada penerima email yang valid diatur di MAIL_RECIPIENTS untuk DO {$doNumber}.");
                 }

                return response()->json(['success' => true, 'message' => 'Permintaan pengiriman email diterima.']);
            } else {
                // Jika header DO tidak ditemukan
                Log::error("Gagal mengirim email: Data header DO tidak ditemukan untuk {$doNumber}.");
                return response()->json(['success' => false, 'message' => 'Data DO tidak ditemukan.'], 404);
            }

        } catch (Throwable $e) {
            // Tangkap error umum saat proses
            Log::error("Gagal memicu email untuk DO {$doNumber}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memicu pengiriman email.'], 500);
        }
    }
}

