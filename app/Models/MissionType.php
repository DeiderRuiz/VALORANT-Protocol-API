<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissionType extends Model
{
    protected $fillable = [
        'name'
    ];

    // Misiones clasificadas en cada tipo de misión
    public function mission(){
        return $this->hasMany(Mission::class,'type_id');
    }

}
