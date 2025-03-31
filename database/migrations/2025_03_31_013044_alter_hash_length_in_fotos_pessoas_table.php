<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('fotos_pessoas', function (Blueprint $table) {
            $table->string('hash', 64)->change(); // Aumente para 64 caracteres
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('fotos_pessoas', function (Blueprint $table) {
            $table->string('hash', 50)->change(); // Reverte para 50
        });
    }
};
