<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ejecutar el comando de importación automáticamente
        // Esto borrará las entradas (excepto las 3 primeras) y volverá a importar el JSON con el formato de fecha corregido
        Artisan::call('work-order:import', [
            'email' => 'joanpd0@gmail.com',
            'work_order_id' => 4,
            'file' => 'datos.json',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
