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
        Schema::table('do_list', function (Blueprint $table) {
            $table->string('CHARG2')->nullable()->after('WADAT_IST');
            $table->dropColumn('CHARG');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('do_list', function (Blueprint $table) {
            $table->dropColumn('CHARG2');
            $table->string('CHARG')->nullable()->after('WADAT_IST');
        });
    }
};
