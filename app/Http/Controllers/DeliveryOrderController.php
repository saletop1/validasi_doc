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

class DeliveryOrderController extends Controller
{
    // ... (fungsi verifyIndex, historyIndex, getScannedItemsForDO, search tidak berubah) ...
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


        // 3. Kirim kedua data ke view
        return view('delivery-order.history', [
            'completedDos' => $completedDos,
            'inProgressDos' => $inProgressDos
        ]);
    }
    public function getScannedItemsForDO($doNumber)
    {
        try {
            // 1. Ambil item DO unik berdasarkan MATNR, sum LFIMG, ambil deskripsi pertama
            $doListItemsGrouped = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR', DB::raw('MIN(MAKTX) as description'), DB::raw('SUM(LFIMG) as total_qty_order'))
                ->groupBy('MATNR') // Group hanya berdasarkan Material Number
                ->orderBy('MATNR', 'asc')
                ->get();

            // 2. Ambil total scan per material_number
            $scannedData = DB::table('scanned_items')
                ->where('do_number', $doNumber)
                ->select('material_number', DB::raw('SUM(qty_scanned) as total_qty_scan'))
                ->groupBy('material_number')
                ->get();

            // 3. Buat peta (Map) dari data scan untuk pencocokan cepat
            $scannedCountsMap = collect($scannedData)->mapWithKeys(function ($item) {
                $materialNumber = $item->material_number ?? null;
                if ($materialNumber === null) return [];
                $key = ctype_digit((string)$materialNumber) ? ltrim($materialNumber, '0') : $materialNumber;
                return [$key => (int)$item->total_qty_scan];
            });


            $totalOrder = 0;
            $totalScan = 0;

            // 4. Gabungkan data
            $results = $doListItemsGrouped->map(function ($item, $key) use ($scannedCountsMap, &$totalOrder, &$totalScan, $doNumber) { // Tambahkan $doNumber untuk logging
                $matnr = $item->MATNR ?? null;
                if ($matnr === null) {
                    Log::warning('MATNR null ditemukan di do_list saat grouping.', ['do_number' => $doNumber]);
                    return null;
                }
                $materialKeyFormatted = ctype_digit((string)$matnr) ? ltrim($matnr, '0') : $matnr;
                $qtyScan = $scannedCountsMap->get($materialKeyFormatted, 0);

                $itemData = [
                    'no' => $key + 1,
                    'material_number' => $materialKeyFormatted,
                    'description' => $item->description ?? 'N/A',
                    'qty_order' => (int)$item->total_qty_order,
                    'qty_scan' => $qtyScan,
                ];

                $totalOrder += $itemData['qty_order'];
                $totalScan += $itemData['qty_scan'];

                return $itemData;
            })->filter(); // Hapus item yang null

            if ($results->isEmpty() && !$doListItemsGrouped->isEmpty()) {
                 Log::warning('Hasil penggabungan detail riwayat kosong.', ['do_number' => $doNumber]);
            }

            return response()->json([
                'items' => $results->values(),
                'summary' => [
                    'total_order' => $totalOrder,
                    'total_scan' => $totalScan
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Gagal mengambil detail riwayat: ' . $e->getMessage(), [
                'do_number' => $doNumber,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memuat data detail. Silakan cek log server.'], 500);
        }
    }


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
            $sapUsername = 'auto_email';
            $sapPassword = '11223344';
            Log::info('Mengirim permintaan ke API Python', [
                'username' => $sapUsername,
                'P_VBELN' => $doNumber
            ]);

            $response = Http::timeout(30)->post('http://127.0.0.1:8009/api/sap/get_do_details', [
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

             // Simpan data ke lokal SETELAH memastikan data SAP tidak kosong
             $this->saveSapDataToLocal($doNumber, $tData, $tData2);


        } catch (Throwable $e) {
            Log::error('Gagal saat menghubungi API Python: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung ke server SAP/Python.'], 500);
        }

         // --- Bagian Pengambilan Data Lokal (setelah data SAP disimpan) ---
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
                 ->select('MATNR', 'MAKTX', 'VBELN as do_no', 'POSNR as item_no', 'CHARG2 as batch_no', 'LFIMG as qty_order')
                 ->orderBy('POSNR', 'asc')
                 ->get();

              if ($doItems->isEmpty()) {
                   Log::warning("Data Item DO kosong di DB Lokal setelah save.", ['do_number' => $doNumber]);
              }

             $allHuDetails = DB::table('do_list_details')
                 ->where('DELV', $doNumber)
                 ->select('DELV', 'ITEM', 'EXIDV as hu_no', 'ITEM2 as item_hu', 'CHARG2 as charg2', 'VEMNG as qty_hu')
                 ->get();

             $huMapByPosnr = $allHuDetails->groupBy('ITEM');

             $formattedItems = $doItems->map(function ($item) use ($huMapByPosnr) {
                 $posnrKey = $item->item_no;
                 if ($posnrKey === null) return null;

                 $material = ctype_digit((string)$item->MATNR) ? ltrim($item->MATNR, '0') : $item->MATNR;
                 $itemNo = ctype_digit((string)$posnrKey) ? ltrim($posnrKey, '0') : $posnrKey;


                 $huDetailsForItem = $huMapByPosnr->get($posnrKey, collect())->map(function($detail) {
                     return [
                         'delivery' => $detail->DELV ?? null,
                         'item' => ctype_digit((string)$detail->ITEM) ? ltrim($detail->ITEM, '0') : $detail->ITEM,
                         'hu_no' => ctype_digit((string)$detail->hu_no) ? ltrim($detail->hu_no, '0') : $detail->hu_no,
                         'item_hu' => ctype_digit((string)$detail->item_hu) ? ltrim($detail->item_hu, '0') : $detail->item_hu,
                         'charg2' => $detail->charg2,
                         'qty_hu' => (int)$detail->qty_hu,
                     ];
                 });

                 return [
                     'material' => $material,
                     'description' => $item->MAKTX ?? 'N/A',
                     'do_no' => $item->do_no,
                     'item_no' => $itemNo,
                     'batch_no' => $item->batch_no,
                     'qty_order' => (int)$item->qty_order,
                     'is_hu' => $huDetailsForItem->isNotEmpty(),
                     'hu_details' => $huDetailsForItem->values()->all(),
                 ];
             })->filter()->values();

             $progressData = DB::table('scanned_items')->where('do_number', $doNumber)->get();

             $scannedHus = $progressData->filter(function ($item) {
                                          return !empty($item->batch_number) || (isset($item->scanned_code) && strlen((string)$item->scanned_code) > 10);
                                      })
                                      ->pluck('scanned_code')
                                      ->filter()
                                      ->unique()
                                      ->values()
                                      ->map(function($hu){
                                          return ctype_digit((string)$hu) ? ltrim($hu, '0') : $hu;
                                      })
                                      ->all();

             $scannedCounts = $progressData->groupBy(function($item) {
                 $material = isset($item->material_number) && ctype_digit((string)$item->material_number) ? ltrim($item->material_number, '0') : $item->material_number;
                 $itemNo = isset($item->item_number) && ctype_digit((string)$item->item_number) ? ltrim($item->item_number, '0') : $item->item_number;
                 if ($material === null || $itemNo === null) return null;
                 return $material . '-' . $itemNo;
             })
             ->filter(function($group, $key) { return $key !== null; })
             ->mapWithKeys(function ($group, $key) {
                 return [$key => $group->sum('qty_scanned')];
             });


             $progress = [
                 'hus' => $scannedHus,
                 'counts' => (object) $scannedCounts->toArray(),
             ];


             $data = [
                 'do_number' => $doNumber,
                 'customer' => $doHeader->customer,
                 'address' => $doHeader->address,
                 'shipping_point' => $doHeader->shipping_point,
                 'ship_to' => $doHeader->ship_to,
                 'ship_type' => $doHeader->ship_type,
                 'container_no' => $containerInfo->V_NO_CONT ?? null,
                 'items' => $formattedItems,
                 'progress' => $progress,
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
                     continue;
                 }
                $doListToInsert[] = [
                    'WERKS' => $item['WERKS'] ?? null,
                    'VBELN' => $item['VBELN'] ?? null,
                    'POSNR' => $item['POSNR'] ?? null,
                    'LFIMG' => (float)($item['LFIMG'] ?? 0),
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
                ];
            }
            if (!empty($doListToInsert)) {
                DB::table('do_list')->insert($doListToInsert);
            }


            if (!empty($tData2)) {
                $uniqueHuDetails = [];
                $seenCombinations = [];

                foreach ($tData2 as $detail) {
                     if (!isset($detail['EXIDV']) || !isset($detail['ITEM'])) {
                         Log::warning('Data T_DATA2 dari SAP tidak lengkap (EXIDV/ITEM kosong). Baris dilewati.', ['do_number' => $doNumber, 'detail' => $detail]);
                         continue;
                     }
                    // --- PERBAIKAN: Menambahkan CHARG2 (Batch) ke kunci unik ---
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
                            'CHARG2' => $detail['CHARG2'] ?? null, // Nomor Batch
                            'EXIDV' => $detail['EXIDV'] ?? null,   // Nomor HU
                            'VEMNG' => (float)($detail['VEMNG'] ?? 0), // Qty HU (mungkin perlu disesuaikan jika per batch)
                            'VEMEH' => $detail['VEMEH'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $seenCombinations[$combinationKey] = true;
                    } else {
                        // Log jika ada duplikasi berdasarkan kunci baru (opsional)
                        Log::debug('Kombinasi HU-Item-Batch duplikat terdeteksi dan dilewati saat menyimpan T_DATA2.', [
                            'do_number' => $doNumber,
                            'hu' => $detail['EXIDV'] ?? 'N/A',
                            'item' => $detail['ITEM'] ?? 'N/A',
                            'batch' => $detail['CHARG2'] ?? 'N/A'
                        ]);
                    }
                }
                if (!empty($uniqueHuDetails)) {
                     DB::table('do_list_details')->insert($uniqueHuDetails);
                }
            }
        });
    }

    // ... (fungsi scan, sendCompletionEmail tidak berubah) ...
     public function scan(Request $request)
    {
        try {
            $validated = $request->validate([
                'do_number' => 'required|string',
                'material_number' => 'required|string',
                'scanned_code' => 'required|string',
                'batch_number' => 'nullable|string',
                'item_number' => 'required|string',
                'qty_scanned' => 'required|integer|min:1'
            ]);

            DB::table('scanned_items')->insert([
                'do_number' => $validated['do_number'],
                'item_number' => $validated['item_number'],
                'material_number' => $validated['material_number'],
                'scanned_code' => $validated['scanned_code'],
                'batch_number' => $validated['batch_number'],
                'qty_scanned' => $validated['qty_scanned'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Scan berhasil disimpan.']);
        } catch (Throwable $e) {
            Log::error('Error saat menyimpan scan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data scan.'], 500);
        }
    }
    public function sendCompletionEmail(Request $request)
    {
        $validated = $request->validate(['do_number' => 'required|string']);
        $doNumber = $validated['do_number'];

        try {
            $doHeader = DB::table('do_list')->where('VBELN', $doNumber)->first();

            if ($doHeader) {
                $totalQtyOrder = DB::table('do_list')->where('VBELN', $doNumber)->sum('LFIMG');
                $totalQtyScan = DB::table('scanned_items')->where('do_number', $doNumber)->sum('qty_scanned');


                DB::table('do_list')->where('VBELN', $doNumber)->update([
                    'VERIFIED_AT' => now(),
                    'SCANNED_QTY' => $totalQtyScan
                ]);


                $containerNo = DB::table('do_list_details')->where('DELV', $doNumber)->value('V_NO_CONT');

                $emailData = [
                    'do_number' => $doNumber,
                    'customer' => $doHeader->NAME1,
                    'ship_to' => $doHeader->SHIPTO,
                    'container_no' => $containerNo ?? 'N/A',
                ];

                $recipients = explode(',', env('MAIL_RECIPIENTS', 'default@example.com'));

                 if (!empty($recipients) && is_array($recipients)) {
                     $cleanedRecipients = array_map('trim', $recipients);
                     Mail::to($cleanedRecipients)->send(new VerificationCompleted($emailData));
                     Log::info("Email notifikasi untuk DO {$doNumber} telah dimasukkan ke dalam antrian.");
                 } else {
                     Log::warning("Tidak ada penerima email yang valid untuk DO {$doNumber}. Cek MAIL_RECIPIENTS di .env");
                 }

                return response()->json(['success' => true, 'message' => 'Permintaan pengiriman email diterima.']);
            }
            return response()->json(['success' => false, 'message' => 'Data DO tidak ditemukan.'], 404);

        } catch (Throwable $e) {
            Log::error("Gagal memicu email untuk DO {$doNumber}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memicu pengiriman email.'], 500);
        }
    }

} // Akhir Class Controller
