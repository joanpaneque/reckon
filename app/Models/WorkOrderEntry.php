<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderEntry extends Model
{
    protected $fillable = [
        'work_order_id',
        'created_by',
        'name',
        'description',
        'started_at',
        'ended_at',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
