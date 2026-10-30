<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Agent extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'profile_photo',
        'agent_photo',
        'nationality_id',
        'class_id',
        'available',
        'is_draft',
    ];

    protected static function booted()
    {
        // Crear slug a cada agente que se crea
        static::creating(function ($agent) {
            $agent->slug = Str::slug($agent->name);
        });

        // Actualizar el slug de un agente si el nombre cambia
        static::updating(function ($agent) {
            $agent->slug = Str::slug($agent->name);
        });
    }

    // Usuario asociado al agente
    public function user(){
        return $this->belongsTo(User::class);
    }

    // Habilidades del agente
    public function abilities(){
        return $this->hasMany(Ability::class);
    }

    // Nacionalidad del agente
    public function nationality(){
        return $this->belongsTo(Nationality::class);
    }

    // Clase o rol del agente (Duelista, Centinela, Iniciador o Controlador)
    public function class(){
        return $this->belongsTo(AgentClasses::class);
    }

    // Misión a la que fue asignado como lider
    public function ledMission(){
        return $this->hasOne(Mission::class);
    }

    // Misiones en las que el agente ha participado
    public function missions(){
        return $this->belongsToMany(Mission::class, 'mission_agents', 'agent_id', 'mission_id');
    }

    // Notificaciones en las que está invlucrado el agente
    public function notifications(){
        return $this->belongsToMany(Notification::class, 'notification_agents', 'agent_id', 'notification_id')
        ->withPivot('id', 'read', 'read_at')
        ->withTimestamps();
    }

    // Eventos de misión registrados por el agente
    public function events(){
        return $this->hasMany(MissionEvent::class);
    }
}
