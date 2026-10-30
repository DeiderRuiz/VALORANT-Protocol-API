<?php

use App\Http\Controllers\AbilityController;
use App\Http\Controllers\AbortController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

//Rutas publicas
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::patch('/reset-password', [AuthController::class, 'resetPassword']);

//Rutas privadas
//Rutas solo para rol admin
Route::middleware(['auth:api', 'role:admin'])->group(function(){
    Route::get('/agents/draft', [AgentController::class, 'getAgentsDraft']);
    Route::get('/agents/draft/{agent:slug}', [AgentController::class, 'agentDraftExists']);
    
    Route::post('/agents/draft', [AgentController::class, 'createDraftAgent']);
    Route::patch('/agents/draft/{agent:slug}', [AgentController::class, 'updateDraftAgent']);
    Route::post('/agents/draft/{agent:slug}/abilities', [AbilityController::class, 'addAbilities']);

    Route::get('/agents/classes', [AgentController::class, 'getAgentClasses']);

    Route::patch('/agents/{agent}', [AgentController::class, 'updateAgentProfile']);
    Route::patch('/agents/{agent}/profile-photo', [AgentController::class, 'updateProfilePhoto']);
    Route::patch('/agents/{agent}/agent-photo', [AgentController::class, 'updateAgentPhoto']);
    Route::patch('/agents/abilities/{ability}', [AbilityController::class, 'updateAbility']);

    Route::post('/maps', [MapController::class, 'newMap']);
    Route::patch('/maps/{map}', [MapController::class, 'editMap']);

    Route::get('/countries', function () {
    return response()->json(
        json_decode(
            Storage::disk('public')->get('countries.json'),
            true
        )
    );
});
});

//Rutas para roles admin y assistant
Route::middleware(['auth:api', 'role:admin,assistant'])->group(function(){
    Route::get('/agents/statistics/{map}/{missionType}', [AgentController::class, 'getAgentsWithStats']);

    Route::get('/missions/search', [MissionController::class, 'searchMissions']);
    
    Route::get('/missions', [MissionController::class, 'getMissions']);
    Route::get('/missions/types', [MissionController::class, 'missionTypes']);
    Route::post('/missions', [MissionController::class, 'newMission']);
    Route::post('/missions/validate', [MissionController::class, 'validateMission']);
    Route::patch('/missions/{mission:code}/details', [MissionController::class, 'updateMissionDetails']);
    Route::patch('/missions/{mission:code}/agents', [MissionController::class, 'updateMissionAgents']);

    Route::patch('/missions/{mission:code}/resolve', [ReportController::class, 'resolveMission']);

    Route::post('/missions/{mission:code}/abort/approve', [AbortController::class, 'approveAbort']);
    Route::post('/missions/{mission:code}/abort/reject', [AbortController::class, 'rejectAbort']);

    Route::get('/statistics', [StatisticController::class, 'getStatistics']);
});

//Rutas para los 3 roles (admin, assistan y agent)
Route::middleware(['auth:api', 'role:admin,assistant,agent'])->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/my-missions', [MissionController::class, 'getMyMissions']);
    Route::get('/missions/{mission:code}', [MissionController::class, 'showMission']);
    Route::patch('/missions/{mission:code}/status', [MissionController::class, 'editMissionStatus']);

    Route::get('/maps', [MapController::class, 'getMaps']);

    Route::get('/agents', [AgentController::class, 'getAgents']);
    Route::get('/agents/{agent:slug}', [AgentController::class, 'showAgent']);
    Route::get('/agents/{agent:slug}/statistics', [StatisticController::class, 'getAgentStatistics']);

    Route::get('/profile', [UserController::class, 'showProfile']);
    Route::get('/profile/statistics', [UserController::class, 'showStatistics']);
    Route::patch('/credentials/email', [UserController::class, 'updateUserEmail']);
    Route::patch('/credentials/password', [UserController::class, 'updateUserPassword']);

    Route::post('/missions/{mission:code}/report', [ReportController::class, 'newReport']);
    Route::post('/missions/{mission:code}/abort/request', [AbortController::class, 'requestAbort']);
    Route::post('/missions/{mission:code}/abort', [AbortController::class, 'abortMissionDirectly']);

    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::patch('/notifications/{notificationPivot}/read', [NotificationController::class, 'readNotification']);
});

// Esta ruta necesita racibir siempre el token 
// cuando recibe un token expirado, el middleware auth:api no deja que 
// renew tome el token y evita que se responda de forma apropiada (con un error 401)
Route::middleware(['auth:api'])->group(function(){
    Route::post('/renew', [AuthController::class, 'renew']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

/* Ruta para errores 401, cuando ocurre errores de autenticación, 
auth:api espera una ruta inexistente con name login y acaba en error 500 */
Route::get('/unauthenticated', function () {
return response()->json(['message' => 'No autenticado'], 401);
})->name('login');