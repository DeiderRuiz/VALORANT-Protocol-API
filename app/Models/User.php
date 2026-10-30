<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'role_id'
    ];

    protected static function booted(){
        static::creating(function ($user) {
            $user->uuid = Str::uuid();
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Obtiene el identificador que se almacenará en la reclamación del sujeto del JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    // Devuelve una matriz de valores clave que contiene todos los reclamos personalizados que se agregarán al JWT.
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Agente asociado a cada usuario
    public function agent(){
        return $this->hasOne(Agent::class);
    }

    // Agente asociado a cada usuario
    public function tokens(){
        return $this->hasMany(PasswordResetToken::class);
    }

    // Rol al que pertenece cada usuario
    public function role(){
        return $this->belongsTo(Role::class);
    }
}
