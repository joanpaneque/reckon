<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'frequency',
        'color',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userHabits()
    {
        return $this->hasMany(UserHabit::class);
    }

    public function completions()
    {
        return $this->hasMany(UserHabit::class)->where('completed', true);
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'shared_habits', 'habit_id', 'shared_with_id')
            ->withPivot('status')->withTimestamps();
    }

    public function acceptedSharedWith()
    {
        return $this->belongsToMany(User::class, 'shared_habits', 'habit_id', 'shared_with_id')
            ->wherePivot('status', 'accepted')
            ->withPivot('status')->withTimestamps();
    }
}
