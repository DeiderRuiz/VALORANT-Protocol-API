<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\AuthService;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Logearse con Token JWT
    public function login(LoginRequest $request){
        try {
            $result = $this->authService->login($request->only(['email', 'password']));
            return response()->json($result['body'], $result['code']);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // Renovar el token y obtener los datos de la sesión
    public function renew() {
        try {
            // Obtener y autenticar el usuario a partir del token JWT
            $token = JWTAuth::parseToken();
            $user = $token->authenticate();

            $user->load(['agent', 'role']);
            $newToken = $token->refresh();

            return response()->json([
                'ok' => true,
                'token' => $newToken,
                'user' => [
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'id' => $user->agent->id ?? null,
                    'name' => $user->agent->name ?? null,
                    'slug' => $user->agent->slug ?? null,
                    'role' => $user->role->name ?? null,
                ],
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Token expirado'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Token inválido'
            ], 401);
        }
    }

    // Cierre de sesión
    public function logout(){
        try {
            $response = $this->authService->logout();
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
            ], 401);
        }
    }

    // Crear token para reestablecimiento de contraseña
    public function forgotPassword(ForgotPasswordRequest $request) {
        try {
            $result = $this->authService->forgotPassword($request->only('email'));
            return response()->json($result['body'], $result['code']);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    // Cambiar contraseña mediante token
    public function resetPassword(ResetPasswordRequest $request) {
        $data = $request->validated();
        $result = $this->authService->resetPassword($data);
        return response()->json($result['body'], $result['code']);
    }

}
