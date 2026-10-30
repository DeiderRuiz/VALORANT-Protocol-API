<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentClasses extends Model
{
    protected $fillable = [
        'name',    
        'icon',
        'description',
    ];

    // Agentes pertenecientes a cada clase o rol (Duelista, Centinela, Iniciador o Controlador)
    public function agents(){
        return $this->hasMany(Agent::class);
    }
}
