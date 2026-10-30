<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'title',
        'objective',
        'description',
        'deadline',
        'type_id',
        'priority',
        'leader_id',
        'map_id',
        'statement',
        'result',
        'code'
    ];

    protected static function booted()
    {
        // Crear el codigo de una misión
        static::created(function ($mission) {
            $mission->code = 'M-' . str_pad($mission->id, 4, '0', STR_PAD_LEFT);
            $mission->saveQuietly();
        });
    }

    // Reportes realizados por cada misión
    public function report(){
        return $this->hasOne(Report::class);
    }

    // Eventos registrados por cada misión
    public function events(){
        return $this->hasMany(MissionEvent::class)->latest();
    }

    // Mapa en el que se realizan las misiones
    public function map(){
        return $this->belongsTo(Map::class);
    }

    // Agente lider de misión
    public function leader(){
        return $this->belongsTo(Agent::class, 'leader_id');
    }

    // Agentes asignados por cada misión
    public function agents(){
        return $this->belongsToMany(Agent::class, 'mission_agents', 'mission_id', 'agent_id');
    }

    // Tipo de misión
    public function type(){
        return $this->belongsTo(MissionType::class, 'type_id');
    }

    // Notitifaciones que involucran a la misión
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
