<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    // Permissos que tiene cada rol
    public function permissions(){
        return $this->belongsToMany(Role::class, 'role_permissions', 'role_id', 'permission_id');
    }

    // Usuarios pertenecientes a cada rol
    public function user(){
        return $this->hasMany(User::class);
    }
}
