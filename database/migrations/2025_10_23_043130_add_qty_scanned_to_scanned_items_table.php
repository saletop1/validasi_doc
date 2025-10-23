<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scanned_items', function (Blueprint $table) {
            // Tambahkan kolom untuk menyimpan kuantitas per scan
            // Default 1 untuk data lama atau scan batch
            $table->integer('qty_scanned')->default(1)->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scanned_items', function (Blueprint $table) {
            $table->dropColumn('qty_scanned');
        });
    }
};
