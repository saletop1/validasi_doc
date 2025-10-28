@echo off
echo Menjalankan Laravel Scheduler. Jangan tutup window ini.

:: Ganti path ini jika lokasi proyek Anda berbeda
cd D:\PROJECT1\laragon\www\validasi-doc

:loop
:: Menjalankan perintah artisan
php artisan schedule:run

:: Menunggu 60 detik sebelum mengulang
timeout /t 60 > nul

goto loop
