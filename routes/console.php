<?php

    use Illuminate\Foundation\Inspiring;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Schedule;
    use Illuminate\Support\Facades\Log;

    Artisan::command('inspire', function () {
        $this->comment(Inspiring::quote());
    })->purpose('Display an inspiring quote');

    Schedule::command('outstanding:fetch')
        ->cron('0 */5 * * *')
        ->timezone('Asia/Jakarta')
        ->before(function () {
             // Log sederhana saat dimulai
             Log::info('[Scheduler] Memulai outstanding:fetch...');
             echo now()->format('Y-m-d H:i:s') . ' Running ["artisan" outstanding:fetch]' . PHP_EOL;
        })
        ->onSuccess(function () {
             Log::info('[Scheduler] outstanding:fetch selesai dengan sukses.');
        })
        ->onFailure(function () {
             Log::error('[Scheduler] outstanding:fetch gagal.');
        })
        ->withoutOverlapping();
