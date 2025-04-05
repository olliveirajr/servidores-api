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
        $cidades = [
            'Cáceres',
            'Cuiabá',
            'Varzea Grande',
            'Rondonópolis',
            'Sinop',
        ];

        foreach ($cidades as $cidade) {
            DB::table('cidades')->insert([
                'nome' => $cidade,
                'uf' => 'MT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cidades');
    }
};
