<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $fillable = [
        'country_name',
        'country_code',
    ];

    // Agentes pertenecientes a cada nacionalidad
    public function agent(){
        return $this->hasMany(Agent::class);
    }
}
