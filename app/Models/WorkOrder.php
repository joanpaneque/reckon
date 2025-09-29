<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'name',
        'hour_price',
    ];

    public function entries()
    {
        return $this->hasMany(WorkOrderEntry::class);
    }

    public function getTotalTimeAndCost()
    {
        $completedEntries = $this->entries()
            ->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->get();

        $totalSeconds = 0;
        foreach ($completedEntries as $entry) {
            $start = new \DateTime($entry->started_at);
            $end = new \DateTime($entry->ended_at);
            $diff = $end->diff($start);
            $totalSeconds += $diff->days * 86400 + $diff->h * 3600 + $diff->i * 60 + $diff->s;
        }

        $totalHours = $totalSeconds / 3600;
        $totalCost = $totalHours * $this->hour_price;

        return [
            'totalSeconds' => $totalSeconds,
            'totalCost' => $totalCost,
        ];
    }
}
