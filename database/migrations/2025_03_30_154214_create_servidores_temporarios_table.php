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
        Schema::create('servidores_temporarios', function (Blueprint $table) {
            $table->foreignId('pessoa_id')->constrained('pessoas')->onDelete('cascade');
            $table->date('data_admissao');
            $table->date('data_demissao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servidores_temporarios');
    }
};
