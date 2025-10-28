<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\OutstandingDoController;
use Illuminate\Support\Facades\Log;

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
        $this->info('Memulai pengambilan data outstanding DO dari SAP...');

        try {
            // Kita panggil controller yang sudah ada logikanya
            $controller = app(OutstandingDoController::class);
            $response = $controller->fetchAndStoreOutstandingDos();

            // Ambil konten respons untuk dicek
            $data = json_decode($response->getContent(), true);

            if (isset($data['success']) && $data['success']) {
                $message = $data['message'] ?? 'Data outstanding DO berhasil diambil dan disimpan.';
                Log::info('[Cron Job] ' . $message);
                $this->info($message);
            } else {
                $errorMessage = $data['message'] ?? 'Gagal mengambil data outstanding DO.';
                Log::error('[Cron Job] ' . $errorMessage);
                $this->error($errorMessage);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error('[Cron Job] Terjadi error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('Terjadi error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
