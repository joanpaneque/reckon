<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ImportWorkOrderEntries extends Command
{
    protected $signature = 'work-order:import {email=joanpd0@gmail.com} {work_order_id=4} {file=datos.json}';

    protected $description = 'Importa entradas de work order desde un archivo JSON';

    public function handle()
    {
        $email = $this->argument('email');
        $workOrderId = $this->argument('work_order_id');
        $filePath = $this->argument('file');

        // Verificar que el archivo existe
        if (!File::exists($filePath)) {
            $this->error("El archivo {$filePath} no existe.");
            return 1;
        }

        // Obtener el usuario
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("Usuario con email {$email} no encontrado.");
            return 1;
        }

        // Verificar que el work order existe
        $workOrder = WorkOrder::find($workOrderId);
        if (!$workOrder) {
            $this->error("Work order con ID {$workOrderId} no encontrado.");
            return 1;
        }

        // Leer el archivo JSON
        $jsonContent = File::get($filePath);
        $data = json_decode($jsonContent, true);

        if (!isset($data['items']) || !is_array($data['items'])) {
            $this->error("El archivo JSON no contiene un array 'items' válido.");
            return 1;
        }

        $this->info("Importando " . count($data['items']) . " entradas...");

        $imported = 0;
        $skipped = 0;

        foreach ($data['items'] as $item) {
            // Validar campos requeridos
            if (!isset($item['date']) || !isset($item['start_time']) || !isset($item['end_time']) || !isset($item['title'])) {
                $this->warn("Item sin campos requeridos, saltando...");
                $skipped++;
                continue;
            }

            // Crear timestamps
            $startedAt = Carbon::createFromFormat('Y-m-d H:i', $item['date'] . ' ' . $item['start_time']);
            $endedAt = Carbon::createFromFormat('Y-m-d H:i', $item['date'] . ' ' . $item['end_time']);

            // Crear la entrada
            WorkOrderEntry::create([
                'work_order_id' => $workOrderId,
                'created_by' => $user->id,
                'name' => $item['title'],
                'description' => $item['description'] ?? null,
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
            ]);

            $imported++;
        }

        $this->info("✓ Importadas: {$imported} entradas");
        if ($skipped > 0) {
            $this->warn("⚠ Saltadas: {$skipped} entradas");
        }

        return 0;
    }
}
