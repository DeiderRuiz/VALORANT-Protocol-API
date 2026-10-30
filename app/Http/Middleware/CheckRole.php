<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    /* 
       REGISTRAR EL MIDDLEWARE EN withMiddleware() DE bootstrap/app.php 
       ANTES DE USARLO EN LAS RUTAS
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        //Obtener el usuario
        $user = $request->user();

        //Error 401 si no hay usuario
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        //Error 403 si el usuario no tiene el rol correspondiente
        if (!in_array($user->role->name, $roles)) {
            return response()->json(['message' => 'ROL NO AUTORIZADO'], 403);
        }

        return $next($request);
    }
}
