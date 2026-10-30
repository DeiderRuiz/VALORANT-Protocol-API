<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    protected $fillable = [
        'agent_id',
        'name',
        'icon',
        'description',
    ];

    // Agente al que pertenece la habilidad (1 a m)
    public function agent(){
        return $this->belongsTo(Agent::class);
    }
}
