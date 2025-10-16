<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeliveryOrderController extends Controller
{
    /**
     * Menampilkan halaman verifikasi.
     *
     * @return \Illuminate\View\View
     */
    public function verifyIndex()
    {
        return view('delivery-order.verify');
    }

    /**
     * Mencari Delivery Order, mengambil data dari API, menyimpannya, dan mengembalikannya.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validated = $request->validate(['do_number' => 'required|string|max:20']);
        $doNumber = $validated['do_number'];

        // 1. Panggil API Python untuk mendapatkan data SAP terbaru
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

            // 2. Simpan data yang berhasil didapat ke database lokal
            $this->saveSapDataToLocal($doNumber, $tData, $tData2);

        } catch (Throwable $e) {
            Log::error('Gagal saat menghubungi API Python: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung ke server SAP/Python.'], 500);
        }

        // 3. Ambil data dari database lokal untuk ditampilkan
        try {
            $doHeader = DB::table('do_list')
                ->where('VBELN', $doNumber)
                ->select('NAME1 as customer', 'ADDRESS as address', 'VTEXT as shipping_point', 'SHIPTO as ship_to', 'BEZEI2 as ship_type')
                ->first();

            if (!$doHeader) {
                return response()->json(['success' => false, 'message' => "Delivery Order {$doNumber} tidak ditemukan di database lokal."]);
            }

            // --- PERBAIKAN: Ambil Nomor Kontainer ---
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

            $scannedCounts = $progressData->groupBy('material_number')->mapWithKeys(function ($group, $key) {
                 $cleanedKey = ctype_digit((string)$key) ? ltrim($key, '0') : $key;
                return [$cleanedKey => $group->count()];
            })->all();


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
                'container_no' => $containerInfo->V_NO_CONT ?? null, // Tambahkan nomor kontainer
                'items' => $formattedItems,
                'progress' => $progress,
            ];

            return response()->json(['success' => true, 'data' => $data]);

        } catch (Throwable $e) {
            Log::error('Error saat mengambil data dari DB lokal: ' . $e->getMessage() . ' pada baris ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal pada server.'], 500);
        }
    }

    /**
     * Menyimpan data dari SAP ke database lokal.
     *
     * @param  string $doNumber
     * @param  array  $tData
     * @param  array  $tData2
     * @return void
     */
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

    /**
     * Menyimpan data hasil scan ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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
}

