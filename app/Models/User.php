<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function sharedWithMeWorkOrders()
    {
        return $this->belongsToMany(
            WorkOrder::class,
            'shared_work_orders',
            'shared_with_id',
            'work_order_id'
        )->withPivot('permission')->withTimestamps();
    }

    public function workOrderEntries()
    {
        return $this->hasMany(WorkOrderEntry::class);
    }

    public function sentFriendships()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendships()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    // Scope para obtener todas las friendships (enviadas y recibidas)
    public function scopeAllFriendships($query, $userId)
    {
        return Friendship::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId);
    }

    // Método para obtener friendships pendientes recibidas y aceptadas
    public function incomingAndFriends()
    {
        return Friendship::query()
            ->where(function ($query) {
                // Pendientes recibidas
                $query->where('receiver_id', $this->id)
                      ->where('status', 'pending');
            })
            ->orWhere(function ($query) {
                // Aceptadas (enviadas o recibidas)
                $query->where('status', 'accepted')
                      ->where(function ($q) {
                          $q->where('sender_id', $this->id)
                            ->orWhere('receiver_id', $this->id);
                      });
            })
            ->orderBy('created_at', 'desc');
    }


    public function cleanFriends()
    {
        return Friendship::where('status', 'accepted')
            ->where(function ($query) {
                $query->where('sender_id', $this->id)
                      ->orWhere('receiver_id', $this->id);
            })
            ->get()
            ->map(function ($friendship) {
                return $friendship->sender_id === $this->id
                    ? $friendship->receiver
                    : $friendship->sender;
            });
    }

}
