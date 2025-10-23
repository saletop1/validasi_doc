<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    // ... (fungsi verifyIndex, historyIndex, getScannedItemsForDO) ...
    // --- Bagian ini tidak berubah ---
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
            // Mengambil data dan mengelompokkannya
            $doListItemsGrouped = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR', 'MAKTX', DB::raw('SUM(LFIMG) as total_qty_order'))
                ->groupBy('MATNR', 'MAKTX')
                ->orderBy('MATNR', 'asc')
                ->get();

            // Ambil jumlah item yang sudah discan
            $scannedCounts = DB::table('scanned_items')
                ->where('do_number', $doNumber)
                ->select('material_number', DB::raw('SUM(qty_scanned) as total_qty_scan'))
                ->groupBy('material_number')
                ->pluck('total_qty_scan', 'material_number');

            $totalOrder = 0;
            $totalScan = 0;

            // Gabungkan data
            $results = $doListItemsGrouped->map(function ($item, $key) use ($scannedCounts, &$totalOrder, &$totalScan) {
                $materialKey = ctype_digit((string)$item->MATNR) ? ltrim($item->MATNR, '0') : $item->MATNR;
                $qtyScan = $scannedCounts->get($item->MATNR, 0); // Gunakan MATNR asli dari do_list untuk key

                $itemData = [
                    'no' => $key + 1,
                    'material_number' => $materialKey, // Tampilkan material yang sudah diformat
                    'description' => $item->MAKTX,
                    'qty_order' => (int)$item->total_qty_order,
                    'qty_scan' => (int)$qtyScan,
                ];

                $totalOrder += $itemData['qty_order'];
                $totalScan += $itemData['qty_scan'];

                return $itemData;
            });

            return response()->json([
                'items' => $results,
                'summary' => [
                    'total_order' => $totalOrder,
                    'total_scan' => $totalScan
                ]
            ]);

        } catch (Throwable $e) {
            Log::error('Gagal mengambil detail riwayat: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Gagal memuat data detail.'], 500);
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
            $response = Http::timeout(30)->post('http://127.0.0.1:5002/api/sap/get_do_details', [
                'username' => env('SAP_USERNAME'),
                'password' => env('SAP_PASSWORD'),
                'P_VBELN' => $doNumber,
            ]);

            if (!$response->successful()) {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? 'Gagal menghubungi API SAP/Python.';
                Log::error('Gagal saat menghubungi API Python: ' . $errorMessage);
                return response()->json(['success' => false, 'message' => $errorMessage], $response->status());
            }

            $sapData = $response->json();
            $tData = $sapData['data']['t_data'] ?? [];
            $tData2 = $sapData['data']['t_data2'] ?? [];

            if (empty($tData)) {
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
                return response()->json(['success' => false, 'message' => "Delivery Order {$doNumber} tidak ditemukan di database lokal."]);
            }

            $containerInfo = DB::table('do_list_details')
                ->where('DELV', $doNumber)
                ->whereNotNull('V_NO_CONT')
                ->select('V_NO_CONT')
                ->first();

            // Mengambil Item DO dan Mengelompokkannya
            $doItemsGrouped = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR', 'MAKTX', 'VBELN as do_no', 'POSNR as item_no', 'CHARG2 as batch_no', DB::raw('SUM(LFIMG) as qty_order'))
                ->groupBy('MATNR', 'MAKTX', 'VBELN', 'POSNR', 'CHARG2') // Group by all relevant non-aggregated columns
                ->get();


            $allHuDetails = DB::table('do_list_details')
                ->where('DELV', $doNumber)
                ->select('DELV', 'ITEM', 'EXIDV as hu_no', 'ITEM2 as item_hu', 'CHARG2 as charg2', 'VEMNG as qty_hu') // Ambil DELV juga
                ->get()
                ->groupBy('ITEM'); // Kelompokkan HU berdasarkan POSNR asli (ITEM)

            $formattedItems = $doItemsGrouped->map(function ($item) use ($allHuDetails) {
                $posnrKey = $item->item_no; // POSNR asli dari do_list

                $material = ctype_digit((string)$item->MATNR) ? ltrim($item->MATNR, '0') : $item->MATNR;
                $itemNo = ctype_digit((string)$posnrKey) ? ltrim($posnrKey, '0') : $posnrKey;


                $huDetailsForItem = $allHuDetails->get($posnrKey, collect())->map(function($detail) {
                    return [
                        'delivery' => $detail->DELV ?? null, // Gunakan DELV dari do_list_details
                        'item' => ctype_digit((string)$detail->ITEM) ? ltrim($detail->ITEM, '0') : $detail->ITEM,
                        'hu_no' => ctype_digit((string)$detail->hu_no) ? ltrim($detail->hu_no, '0') : $detail->hu_no,
                        'item_hu' => ctype_digit((string)$detail->item_hu) ? ltrim($detail->item_hu, '0') : $detail->item_hu,
                        'charg2' => $detail->charg2,
                        'qty_hu' => (int)$detail->qty_hu,
                    ];
                });

                return [
                    'material' => $material,
                    'description' => $item->MAKTX,
                    'do_no' => $item->do_no,
                    'item_no' => $itemNo,
                    'batch_no' => $item->batch_no,
                    'qty_order' => (int)$item->qty_order, // Gunakan qty_order hasil SUM
                    'is_hu' => $huDetailsForItem->isNotEmpty(),
                    'hu_details' => $huDetailsForItem->values()->all(),
                ];
            })->values();

             // Menghitung progress scan (SUM qty_scanned)
            $progressData = DB::table('scanned_items')->where('do_number', $doNumber)->get();

            // Ambil daftar HU unik yang sudah discan
            $scannedHus = $progressData->whereNotNull('batch_number') // Asumsi HU memiliki batch number, filter jika perlu
                                     ->pluck('scanned_code')
                                     ->filter()
                                     ->unique()
                                     ->values()
                                     ->map(function($hu){
                                         return ctype_digit((string)$hu) ? ltrim($hu, '0') : $hu;
                                     })
                                     ->all();

            // Hitung total qty_scanned per item (material + item_no/POSNR)
            $scannedCounts = $progressData->groupBy(function($item) {
                $material = ctype_digit((string)$item->material_number) ? ltrim($item->material_number, '0') : $item->material_number;
                $itemNo = ctype_digit((string)$item->item_number) ? ltrim($item->item_number, '0') : $item->item_number; // item_number adalah POSNR
                return $material . '-' . $itemNo;
            })->mapWithKeys(function ($group, $key) {
                return [$key => $group->sum('qty_scanned')]; // Jumlahkan qty_scanned
            });


            $progress = [
                'hus' => $scannedHus,
                'counts' => (object) $scannedCounts,
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
            // Hapus data lama hanya jika data baru dari SAP tidak kosong
             if (!empty($tData)) {
                 DB::table('do_list')->where('VBELN', $doNumber)->delete();
             }
             if (!empty($tData2)) {
                 DB::table('do_list_details')->where('DELV', $doNumber)->delete();
             }

            $doListToInsert = [];
            foreach ($tData as $item) {
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
                    $combinationKey = ($detail['EXIDV'] ?? '') . '-' . ($detail['ITEM'] ?? '');
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
                            'VEMNG' => (float)($detail['VEMNG'] ?? 0),
                            'VEMEH' => $detail['VEMEH'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $seenCombinations[$combinationKey] = true;
                    }
                }
                if (!empty($uniqueHuDetails)) {
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
                $totalQty = DB::table('do_list')->where('VBELN', $doNumber)->sum('LFIMG');

                // Update SEMUA baris do_list yang cocok
                DB::table('do_list')->where('VBELN', $doNumber)->update([
                    'VERIFIED_AT' => now(),
                    'SCANNED_QTY' => $totalQty // Asumsi selesai = semua qty order
                ]);


                $containerNo = DB::table('do_list_details')->where('DELV', $doNumber)->value('V_NO_CONT');
                $emailData = [
                    'do_number' => $doNumber,
                    'customer' => $doHeader->NAME1,
                    'ship_to' => $doHeader->SHIPTO,
                    'container_no' => $containerNo ?? 'N/A',
                ];

                $recipients = explode(',', env('MAIL_RECIPIENTS', 'default@example.com'));

                // --- PERBAIKAN: Memastikan Mail::to() berfungsi ---
                // Pastikan $recipients adalah array yang valid sebelum dikirim
                 if (!empty($recipients) && is_array($recipients)) {
                     Mail::to($recipients)->send(new VerificationCompleted($emailData));
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
} // --- Pastikan kurung kurawal penutup kelas ada ---

