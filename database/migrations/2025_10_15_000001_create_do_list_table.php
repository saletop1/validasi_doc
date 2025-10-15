<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('do_list', function (Blueprint $table) {
            $table->id();
            $table->string('WERKS')->nullable();
            $table->string('LGORT')->nullable();
            $table->string('VBELN')->nullable()->index();
            $table->string('POSNR')->nullable();
            $table->decimal('LFIMG', 13, 3)->nullable();
            $table->string('NAME1')->nullable();
            $table->string('MATNR')->nullable();
            $table->string('MAKTX')->nullable();
            $table->string('V_SO')->nullable();
            $table->string('V_SOITEM')->nullable();
            $table->string('BSTNK')->nullable();
            $table->string('WADAT_IST')->nullable();
            $table->string('CHARG')->nullable();
            $table->text('ADDRESS')->nullable();
            $table->string('BEZEI2')->nullable();
            $table->string('VTEXT')->nullable();
            $table->string('SHIPTO')->nullable();
            $table->integer('SCANNED_QTY')->nullable();
            $table->timestamp('VERIFIED_AT')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('do_list');
    }
};
