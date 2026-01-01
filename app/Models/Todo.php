<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Todo extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'title',
        'description',
        'completed',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a date can have todos created/edited
     * Todos can only be created/edited before 2 AM of that day
     */
    public static function canEditTodosForDate($date): bool
    {
        $now = Carbon::now();
        $targetDate = Carbon::parse($date)->startOfDay();
        $today = $now->copy()->startOfDay();
        
        // If target date is in the future, always allow
        if ($targetDate->gt($today)) {
            return true;
        }
        
        // If target date is today, check if it's before 2 AM
        if ($targetDate->equalTo($today)) {
            return $now->hour < 2;
        }
        
        // If target date is in the past, don't allow
        return false;
    }

    /**
     * Get the label for the "Add todos" button based on current time and target date
     */
    public static function getAddButtonLabel($targetDate): string
    {
        $now = Carbon::now();
        $target = Carbon::parse($targetDate)->startOfDay();
        $today = $now->copy()->startOfDay();
        
        if ($target->equalTo($today)) {
            return "Añadir todo's para hoy";
        }
        
        if ($target->equalTo($today->copy()->addDay())) {
            return "Añadir todo's para mañana";
        }
        
        return "Añadir todo's para " . $target->format('d/m/Y');
    }
}
