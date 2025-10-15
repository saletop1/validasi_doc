<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;

class DeliveryOrderController extends Controller
{
    const PYTHON_API_BASE_URL = 'http://127.0.0.1:5002/api/sap';

    private function getSapCredentials()
    {
        return [
            'username' => env('SAP_USERNAME'),
            'password' => env('SAP_PASSWORD'),
        ];
    }

    public function verifyIndex()
    {
        return view('delivery-order.verify');
    }

    public function searchDO(Request $request)
    {
        $doNumber = $request->input('do_number');
        $credentials = $this->getSapCredentials();

        try {
            $response = Http::timeout(30)->post(self::PYTHON_API_BASE_URL . '/get_do_details', [
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'P_VBELN' => $doNumber,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Gagal terhubung ke API Python/SAP.'], 500);
            }

            $sapData = $response->json();

            if (!($sapData['success'] ?? false) || empty($sapData['data']['t_data'])) {
                return response()->json(['success' => false, 'message' => $sapData['message'] ?? 'Data tidak ditemukan di SAP.'], 404);
            }

            // Simpan data mentah ke database dengan struktur baru
            $this->saveToDatabase($sapData['data']);

            $rawData = $sapData['data'];
            $items = $rawData['t_data'];

            $findFirstValue = function($key, $itemArray) {
                foreach ($itemArray as $item) {
                    if (isset($item[$key]) && !empty(trim($item[$key]))) {
                        return trim($item[$key]);
                    }
                }
                return 'T/A';
            };

            $customerName   = $findFirstValue('NAME1', $items);
            $customerAddress = $findFirstValue('ADDRESS', $items);
            $shippingPoint  = $findFirstValue('VTEXT', $items);
            $shipTo         = $findFirstValue('SHIPTO', $items);
            $shipType       = $findFirstValue('BEZEI2', $items);

            $itemDetails = collect($rawData['t_data2'] ?? [])->groupBy(function($detail) {
                $delvNo = ltrim($detail['DELV'] ?? '', '0');
                $itemNo = ltrim($detail['ITEM'] ?? '', '0');
                return "{$delvNo}-{$itemNo}";
            });

            $formattedItems = collect($items)->map(function ($item) use ($itemDetails) {
                $material_code = ctype_digit($item['MATNR'] ?? '') ? ltrim($item['MATNR'], '0') : ($item['MATNR'] ?? '');
                $doKey = ltrim($item['VBELN'] ?? '', '0');
                $itemKey = ltrim($item['POSNR'] ?? '', '0');
                $compositeKey = "{$doKey}-{$itemKey}";
                $details = $itemDetails->get($compositeKey);
                $hu_details = [];

                if ($details) {
                    $hu_details = $details->map(function($detail) {
                        return [
                            'hu_no'    => ltrim($detail['EXIDV'] ?? 'N/A', '0'),
                            // PERUBAHAN: Batch untuk T_DATA2 diambil dari CHARG
                            'batch_no' => $detail['CHARG'] ?? 'N/A',
                            'do_no'    => ltrim($detail['DELV'] ?? 'N/A', '0'),
                            'item_no'  => ltrim($detail['ITEM'] ?? 'N/A', '0'),
                        ];
                    })->all();
                }

                return [
                    'material'    => $material_code,
                    'description' => $item['MAKTX'] ?? '',
                    'qty_order'   => (int)($item['LFIMG'] ?? 0),
                    'hu_details'  => $hu_details,
                    'do_no'       => $doKey,
                    'item_no'     => $itemKey,
                    // PERUBAHAN: Batch untuk T_DATA diambil dari CHARG2
                    'batch_no'    => $item['CHARG2'] ?? 'N/A',
                    'is_hu'       => !empty($hu_details)
                ];
            });

            $formattedData = [
                "customer"       => $customerName,
                "address"        => $customerAddress,
                "shipping_point" => $shippingPoint,
                "ship_to"        => $shipTo,
                "ship_type"      => $shipType,
                "items"          => $formattedItems
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diambil dari SAP dan disimpan.',
                'data' => $formattedData,
            ]);

        } catch (Throwable $e) {
            Log::error('Kesalahan fatal saat mencari DO: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan fatal di server: ' . $e->getMessage()], 500);
        }
    }

    private function saveToDatabase($data)
    {
        $doNumber = $data['t_data'][0]['VBELN'] ?? null;

        if (!$doNumber || DB::table('do_list')->where('VBELN', $doNumber)->exists()) {
            if ($doNumber) Log::info("Penyimpanan dilewati: DO {$doNumber} sudah ada.");
            return;
        }

        try {
            DB::transaction(function () use ($data, $doNumber) {
                $now = Carbon::now();

                foreach ($data['t_data'] as $item) {
                    DB::table('do_list')->insert([
                        'WERKS' => $item['WERKS'] ?? null,
                        'LGORT' => $item['LGORT'] ?? null,
                        'VBELN' => $item['VBELN'] ?? null,
                        'POSNR' => $item['POSNR'] ?? null,
                        'LFIMG' => $item['LFIMG'] ?? null,
                        'NAME1' => $item['NAME1'] ?? null,
                        'MATNR' => $item['MATNR'] ?? null,
                        'MAKTX' => $item['MAKTX'] ?? null,
                        'V_SO' => $item['V_SO'] ?? null,
                        'V_SOITEM' => $item['V_SOITEM'] ?? null,
                        'BSTNK' => $item['BSTNK'] ?? null,
                        'WADAT_IST' => $item['WADAT_IST'] ?? null,
                        // PERUBAHAN: Menyimpan dari CHARG2 ke kolom CHARG2
                        'CHARG2' => $item['CHARG2'] ?? null,
                        'ADDRESS' => $item['ADDRESS'] ?? null,
                        'BEZEI2' => $item['BEZEI2'] ?? null,
                        'VTEXT' => $item['VTEXT'] ?? null,
                        'SHIPTO' => $item['SHIPTO'] ?? null,
                        'SCANNED_QTY' => 0,
                        'VERIFIED_AT' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                // Logika penyimpanan untuk do_list_details tidak berubah karena sudah benar
                foreach ($data['t_data2'] as $detailItem) {
                    DB::table('do_list_details')->insert([
                        'EXIDV' => ltrim($detailItem['EXIDV'] ?? '', '0'),
                        'ITEM' => $detailItem['ITEM'] ?? null,
                        'VEMNG' => $detailItem['VEMNG'] ?? null,
                        'VEMEH' => $detailItem['VEMEH'] ?? null,
                        'DELV' => $detailItem['DELV'] ?? null,
                        'KDAUF' => $detailItem['KDAUF'] ?? null,
                        'KDPOS' => $detailItem['KDPOS'] ?? null,
                        'MATNR' => $detailItem['MATNR'] ?? null,
                        'MAKTX' => $detailItem['MAKTX'] ?? null,
                        'BSTKD' => $detailItem['BSTKD'] ?? null,
                        'CHARG' => $detailItem['CHARG'] ?? null,
                        'V_NO_CONT' => $detailItem['V_NO_CONT'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
            Log::info("DO {$doNumber} berhasil disimpan ke database.");
        } catch (Throwable $e) {
            Log::error("Gagal menyimpan DO {$doNumber} ke database: " . $e->getMessage());
            throw $e;
        }
    }
}
