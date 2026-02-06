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
        // Opcional: eliminar las entradas importadas si se hace rollback
        // Esto requeriría identificar las entradas importadas, por ejemplo con un campo adicional
        // Por ahora, dejamos vacío para evitar eliminar datos accidentalmente
    }
};
