<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCompleted;
use Throwable;
use Carbon\Carbon;

class DeliveryOrderController extends Controller
{
    // ... fungsi lain tidak berubah ...
    public function verifyIndex()
    {
        return view('delivery-order.verify');
    }

    public function historyIndex()
    {
        $completedDos = DB::table('do_list')
            ->whereNotNull('VERIFIED_AT')
            ->orderBy('VERIFIED_AT', 'desc')
            ->get()
            ->groupBy('VBELN');

        return view('delivery-order.history', ['completedDos' => $completedDos]);
    }

    public function getScannedItemsForDO($doNumber)
    {
        try {
            $doListItems = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR as material_number', 'MAKTX as description', 'POSNR as item_number', 'LFIMG as qty_order')
                ->orderBy('POSNR', 'asc')
                ->get();

            $scannedCounts = DB::table('scanned_items')
                ->where('do_number', $doNumber)
                ->get()
                ->groupBy(function ($item) {
                    return $item->material_number . '-' . $item->item_number;
                })
                ->map(function ($group) {
                    return $group->count();
                });

            // --- PERBAIKAN: Menambahkan nomor urut ---
            $results = $doListItems->map(function ($item, $key) use ($scannedCounts) {
                $materialKey = ctype_digit((string)$item->material_number) ? ltrim($item->material_number, '0') : $item->material_number;
                $itemKey = ltrim($item->item_number, '0');
                $uniqueKey = $materialKey . '-' . $itemKey;

                $item->no = $key + 1; // Menambahkan nomor urut
                $item->qty_scan = $scannedCounts->get($uniqueKey, 0);
                $item->qty_order = (int)$item->qty_order;

                return $item;
            });

            return response()->json($results);

        } catch (Throwable $e) {
            Log::error('Gagal mengambil detail riwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat data detail.'], 500);
        }
    }


    public function search(Request $request)
    {
        $validated = $request->validate(['do_number' => 'required|string|max:20']);
        $doNumber = $validated['do_number'];

        $existingDO = DB::table('do_list')->where('VBELN', $doNumber)->first();
        if ($existingDO && !is_null($existingDO->VERIFIED_AT)) {
            // --- PERBAIKAN: Mengubah zona waktu ke 'Asia/Jakarta' saat menampilkan ---
            $verifiedAt = Carbon::parse($existingDO->VERIFIED_AT)->timezone('Asia/Jakarta')->format('d-m-Y H:i');
            return response()->json([
                'success' => false,
                'status' => 'completed',
                'message' => "Verifikasi untuk DO {$doNumber} sudah selesai pada " . $verifiedAt
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

            $doItems = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('MATNR as material', 'MAKTX as description', 'VBELN as do_no', 'POSNR as item_no', 'CHARG2 as batch_no', 'LFIMG as qty_order')
                ->get();

            $allHuDetails = DB::table('do_list_details')
                ->where('DELV', $doNumber)
                ->select('DELV as delivery', 'ITEM', 'EXIDV as hu_no', 'ITEM2 as item_hu', 'CHARG2 as charg2', 'VEMNG as qty_hu')
                ->get()
                ->groupBy('ITEM');

            $formattedItems = $doItems->map(function ($item) use ($allHuDetails) {
                $material = ctype_digit((string)$item->material) ? ltrim($item->material, '0') : $item->material;
                $itemNo = ctype_digit((string)$item->item_no) ? ltrim($item->item_no, '0') : $item->item_no;

                $huDetailsForItem = $allHuDetails->get($item->item_no, collect())->map(function($detail) {
                    return [
                        'delivery' => $detail->delivery,
                        'item' => ctype_digit((string)$detail->ITEM) ? ltrim($detail->ITEM, '0') : $detail->ITEM,
                        'hu_no' => ctype_digit((string)$detail->hu_no) ? ltrim($detail->hu_no, '0') : $detail->hu_no,
                        'item_hu' => ctype_digit((string)$detail->item_hu) ? ltrim($detail->item_hu, '0') : $detail->item_hu,
                        'charg2' => $detail->charg2,
                        'qty_hu' => (int)$detail->qty_hu,
                    ];
                });

                return [
                    'material' => $material,
                    'description' => $item->description,
                    'do_no' => $item->do_no,
                    'item_no' => $itemNo,
                    'batch_no' => $item->batch_no,
                    'qty_order' => (int)$item->qty_order,
                    'is_hu' => $huDetailsForItem->isNotEmpty(),
                    'hu_details' => $huDetailsForItem->values()->all(),
                ];
            })->values();

            $progressData = DB::table('scanned_items')->where('do_number', $doNumber)->get();
            $scannedHus = $progressData->pluck('scanned_code')->filter()->unique()->values()->map(function($hu){
                 return ctype_digit((string)$hu) ? ltrim($hu, '0') : $hu;
            })->all();

            $scannedCounts = $progressData->groupBy(function($item) {
                $material = ctype_digit((string)$item->material_number) ? ltrim($item->material_number, '0') : $item->material_number;
                $itemNo = ctype_digit((string)$item->item_number) ? ltrim($item->item_number, '0') : $item->item_number;
                return $material . '-' . $itemNo;
            })->mapWithKeys(function ($group, $key) {
                return [$key => $group->count()];
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
            Log::error('Error saat mengambil data dari DB lokal: ' . $e->getMessage() . ' pada baris ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal pada server.'], 500);
        }
    }

    private function saveSapDataToLocal(string $doNumber, array $tData, array $tData2): void
    {
        DB::transaction(function () use ($doNumber, $tData, $tData2) {
            DB::table('do_list')->where('VBELN', $doNumber)->delete();
            DB::table('do_list_details')->where('DELV', $doNumber)->delete();

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
            DB::table('do_list')->insert($doListToInsert);

            if (!empty($tData2)) {
                $uniqueHuDetails = [];
                $seenCombinations = [];

                foreach ($tData2 as $detail) {
                    $combinationKey = ($detail['EXIDV'] ?? '') . '-' . ($detail['CHARG2'] ?? '');
                    if (!isset($seenCombinations[$combinationKey])) {
                        $uniqueHuDetails[] = [
                            'DELV' => $detail['DELV'] ?? null,
                            'ITEM2' => $detail['ITEM2'] ?? null,
                            'KDAUF' => $detail['KDAUF'] ?? null,
                            'KDPOS' => $detail['KDPOS'] ?? null,
                            'MATNR' => $detail['MATNR'] ?? null,
                            'MAKTX' => $detail['MAKTX'] ?? null,
                            'BSTKD' => $detail['BSTKD'] ?? null,
                            'V_NO_CONT' => $detail['V_NO_CONT'] ?? null,
                            'CHARG2' => $detail['CHARG2'] ?? null,
                            'EXIDV' => $detail['EXIDV'] ?? null,
                            'ITEM' => $detail['ITEM'] ?? null,
                            'VEMNG' => (float)($detail['VEMNG'] ?? 0),
                            'VEMEH' => $detail['VEMEH'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $seenCombinations[$combinationKey] = true;
                    }
                }
                DB::table('do_list_details')->insert($uniqueHuDetails);
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
                'item_number' => 'nullable|string',
            ]);

            DB::table('scanned_items')->insert([
                'do_number' => $validated['do_number'],
                'item_number' => $validated['item_number'] ?? null,
                'material_number' => $validated['material_number'],
                'scanned_code' => $validated['scanned_code'],
                'batch_number' => $validated['batch_number'],
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
                // --- PERBAIKAN DIMULAI DI SINI ---
                // 1. Hitung total kuantitas pesanan (LFIMG) untuk DO ini.
                $totalQty = DB::table('do_list')->where('VBELN', $doNumber)->sum('LFIMG');

                // 2. Update VERIFIED_AT dan SCANNED_QTY secara bersamaan.
                DB::table('do_list')->where('VBELN', $doNumber)->update([
                    'VERIFIED_AT' => now(),
                    'SCANNED_QTY' => $totalQty
                ]);
                // --- PERBAIKAN SELESAI ---

                $containerNo = DB::table('do_list_details')->where('DELV', $doNumber)->value('V_NO_CONT');

                $emailData = [
                    'do_number' => $doNumber,
                    'customer' => $doHeader->NAME1,
                    'ship_to' => $doHeader->SHIPTO,
                    'container_no' => $containerNo ?? 'N/A',
                ];

                $recipients = explode(',', env('MAIL_RECIPIENTS', 'default@example.com'));

                Mail::to($recipients)->send(new VerificationCompleted($emailData));
                Log::info("Email notifikasi untuk DO {$doNumber} telah dimasukkan ke dalam antrian.");

                return response()->json(['success' => true, 'message' => 'Permintaan pengiriman email diterima.']);
            }
            return response()->json(['success' => false, 'message' => 'Data DO tidak ditemukan.'], 404);

        } catch (Throwable $e) {
            Log::error("Gagal memicu email untuk DO {$doNumber}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memicu pengiriman email.'], 500);
        }
    }
}

