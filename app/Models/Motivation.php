<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motivation extends Model
{
    protected $fillable = [
        'user_id',
        'sent_to',
        'message',
        'image_path',
        'receiver_closed',
        'receiver_message',
        'sender_closed_response',
    ];

    protected $casts = [
        'receiver_closed' => 'boolean',
        'sender_closed_response' => 'boolean',
    ];

    // Usuario que envía la motivación
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Usuario que recibe la motivación
    public function receiver()
    {
        return $this->belongsTo(User::class, 'sent_to');
    }
}
