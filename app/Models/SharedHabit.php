<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedHabit extends Model
{
    protected $fillable = [
        'habit_id',
        'shared_with_id',
        'status',
    ];

    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }
}
