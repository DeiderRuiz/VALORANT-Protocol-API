<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'description',
    ];

    // Misiones designadas a cada mapa
    public function missions(){
        return $this->hasMany(Mission::class);
    }
}
