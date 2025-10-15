<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('do_list_details', function (Blueprint $table) {
            $table->id();
            $table->string('DELV')->nullable();
            $table->string('KDAUF')->nullable();
            $table->string('KDPOS')->nullable();
            $table->string('MATNR')->nullable();
            $table->string('MAKTX')->nullable();
            $table->string('BSTKD')->nullable();
            $table->string('CHARG')->nullable();
            $table->string('V_NO_CONT')->nullable();
            $table->string('EXIDV')->nullable();
            $table->string('ITEM')->nullable();
            $table->decimal('VEMNG', 13, 3)->nullable();
            $table->string('VEMEH')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('do_list_details');
    }
};
