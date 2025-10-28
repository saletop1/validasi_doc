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
        Schema::create('outstanding_dos', function (Blueprint $table) {
            // $table->id(); // Kita tidak pakai ID, pakai composite primary key
            $table->string('location', 20)->nullable(); // surabaya / semarang
            $table->string('customer_name')->nullable();
            $table->string('plant', 10)->nullable();
            $table->string('delivery_number', 20);
            $table->string('item_number', 10);
            $table->string('material_number', 40)->nullable();
            $table->string('material_description')->nullable();
            $table->decimal('qty_do', 15, 3)->nullable();
            $table->decimal('stock', 15, 3)->nullable();
            $table->decimal('stock_non_hu', 15, 3)->nullable();
            $table->decimal('stock_hu', 15, 3)->nullable();
            $table->decimal('qty_outstanding', 15, 3)->nullable();
            $table->decimal('percent_shortage', 7, 3)->nullable();
            $table->decimal('percent_success', 7, 3)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            // Kunci utama agar setiap baris item DO unik
            $table->primary(['delivery_number', 'item_number']);
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outstanding_dos');
    }
};
