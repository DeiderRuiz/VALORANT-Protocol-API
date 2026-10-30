<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'mission_id',
        'type',
        'details',
    ];

    // Misión sobre la que se hace la notificación
    public function mission(){
        return $this->belongsTo(Mission::class);
    }

    // Agentesque reciben la notificación
    public function agents(){
        return $this->belongsToMany(Agent::class, 'notification_agents', 'notification_id', 'agent_id')
        ->withPivot('id', 'read', 'read_at')
        ->withTimestamps();
    }
}
