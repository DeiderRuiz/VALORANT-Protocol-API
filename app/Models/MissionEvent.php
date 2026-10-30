<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionEvent extends Model
{
    protected $fillable = [
        'type',
        'description',
        'details',
        'agent_id',
        'mission_id',
    ];

    // Misión en la que se registró el evento
    public function mission(){
        return $this->belongsTo(Mission::class);
    }

    // Agente que registró el evento
    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    // Para Relación auto-referenciada
    // Evento original
    public function parent(){
        return $this->belongsTo(MissionEvent::class, 'related_report_id');
    }
    // Respuesta el evento original
    public function responses(){
        return $this->hasMany(MissionEvent::class, 'related_report_id');
    }
}
