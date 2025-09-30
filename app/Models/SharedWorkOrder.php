<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedWorkOrder extends Model
{
    protected $fillable = [
        'work_order_id',
        'shared_with_id',
        'permission',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }
}
