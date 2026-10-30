<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserEmailRequest;
use App\Http\Requests\UserPasswordRequest;
use App\Models\User;
use App\Services\StatisticService;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;
    protected $statisticService;

    public function __construct(UserService $userService, StatisticService $statisticService)
    {
        $this->userService = $userService;
        $this->statisticService = $statisticService;
    }

    // Obener perfil de usuario logeado
    public function showProfile(){
        $agent = auth()->user()->agent()->with(['abilities', 'nationality', 'class'])->first();
        return response()->json($agent, 200);
    }

    // Obtener estadisticaas del agente logeado
    public function showStatistics(){
        $agent = auth()->user()->agent()->with(['abilities', 'nationality', 'class'])->first();
        $statistics = $this->statisticService->getAgentsStatistics($agent);
        return response()->json($statistics, 200);
    }

    // Cambiar email del usuario
    public function updateUserEmail(UserEmailRequest $request){
        $data = $request->validated();

        $email = $this->userService->editEmail($data);

        return response()->json([
            'message' => 'Email actualizado.',
        ], 200);
    }

    // Cambiar la contraseña del usuario
    public function updateUserPassword(UserPasswordRequest $request){
        $data = $request->validated();

        $password = $this->userService->editPassword($data);

        return response()->json([
            'message' => 'Contraseña actualizada.',
        ], 200);
    }
}
