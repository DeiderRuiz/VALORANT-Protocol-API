<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAgent extends Model
{
    protected $fillable = [
        'agent_id',
        'notification_id',
        'read',
        'read_at',
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];
}
