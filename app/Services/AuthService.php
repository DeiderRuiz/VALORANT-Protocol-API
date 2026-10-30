<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthService
{
    public function login(array $data){
        // Hacer el token para el inicio de sesión
        if (!$token = JWTAuth::attempt($data)) {
            return [
                'body' => [
                    'ok' => false,
                    'message' => 'El correo o la contraseña son incorrectos.'
                ],
                'code' => 401,
            ];
        }

        // Cargar todo el agente (credenciales, perfil y rol en el sistema)
        $user = auth()->user()->load(['agent', 'role']);

        // Si el agente es un borrador no lo deja logearse e invalida el token
        if (!$user->agent || $user->agent->is_draft) {
            // Invalidar token
            JWTAuth::setToken($token)->invalidate();
            return [
                'body' => [
                    'ok' => false,
                    'error' => 'Debes completar el perfil antes de acceder'
                ],
                'code' => 403,
            ];
        }
        
        return [
            'body' => [
                'ok' => true,
                'token' => $token,
                'user' => [
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'id' => $user->agent->id ?? null,
                    'name' => $user->agent->name ?? null,
                    'slug' => $user->agent->slug ?? null,
                    'role' => $user->role->name ?? null,
                ],
            ],
            'code' => 200,
        ];
    }

    public function logout(){
        JWTAuth::invalidate(JWTAuth::getToken());
        return [
            'ok' => true,
        ];
    }

    public function forgotPassword(array $data) {
        return DB::transaction(function () use ($data){
            $user = User::where('email', $data['email'])->first();

            if (!$user->agent || $user->agent->is_draft) {
                return [
                    'body' => [
                        'ok' => false,
                        'error' => 'Debes completar el perfil antes de acceder'
                    ],
                    'code' => 403,
                ];
            }

            // Desactivar todos los tokens anteriores del usuario
            PasswordResetToken::where('user_id', $user->id)
            ->where('is_active', true)->update([
                'is_active' => false,
            ]);
    
            // Hacer el token para recuperar la contraseña
            $token = Str::random(64);
            
            PasswordResetToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(30),
            ]);
    
            // Crear enlace con token para reestablecmineto de contraseña
            $url = config('app.frontend_url') . '/reset-password?token=' . urlencode($token);
            Mail::to($user->email)->send(new PasswordResetMail($url));
            
            return [
                'body' => [
                    'message' => 'Hemos enviado un enlace para restablecer tu contraseña. Revisa tu correo electrónico.',
                ],
                'code' => 200,
            ];
        });
    }

    public function resetPassword(array $data) {
        return DB::transaction(function () use ($data){
            $tokenHash = hash('sha256', $data['token']);
            $resetToken = PasswordResetToken::where('token', $tokenHash)->first();

            if (!$resetToken) {
                return [
                    'body' => [
                        'message' => 'El enlace de recuperación no es válido.',
                    ],
                    'code' => 400,
                ];
            }
    
            if (!$resetToken->is_active || $resetToken->expires_at < now()) {
                return [
                    'body' => [
                        'message' => 'El enlace de recuperación ha expirado o ya fue utilizado.',
                    ],
                    'code' => 400,
                ];
            }
    
            $user = $resetToken->user;
    
            if (!$user->agent || $user->agent->is_draft) {
                return [
                    'body' => [
                        'message' => 'Debes completar el perfil antes de restablecer tu contraseña.',
                    ],
                    'code' => 403,
                ];
            }
    
            $user->update([
                'password' => $data['password'],
            ]);
    
            $resetToken->update([
                'is_active' => false,
            ]);
    
            return [
                'body' => [
                    'message' => 'Contraseña restablecida correctamente.',
                ],
                'code' => 200,
            ];
        });
    }
}