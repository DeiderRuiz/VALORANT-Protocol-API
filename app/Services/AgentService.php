<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentClasses;
use App\Models\Map;
use App\Models\MissionType;
use App\Models\Nationality;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AgentService
{
    // Obtener todos los agentes y sus estadisticas en un mapa y tipo de misión especifica
    public function getAgentsWithStats($map, $missionType){
        return DB::transaction(function () use ($map, $missionType){
            // Excluye a los agentes que son borrador
            $agents = Agent::with(['abilities', 'nationality', 'class'])->where('is_draft', false)->get();

            $selectedMap = Map::Where('id', $map)->first();
            $selectedMissionType = MissionType::Where('id', $missionType)->first();

            $agentAndStats = [];

            foreach ($agents as $agent) {
                // Estadisticas del agente en el mapa
                $mapMissionsCompleted = $agent->missions()->where('map_id', $map)->where('statement', 'completed')->count();
                $mapVictories = $agent->missions()->where('map_id', $map)->where('statement', 'completed')->where('result', 'success')->count();
                $mapVictoryAverage = $mapMissionsCompleted > 0 ? round(($mapVictories / $mapMissionsCompleted) * 100, 2) : 0;

                // Estadisticas del agente en el tipo de misión
                $missionTypesCompleted = $agent->missions()->where('type_id', $missionType)->where('statement', 'completed')->count();
                $missionTypesVictories = $agent->missions()->where('type_id', $missionType)->where('statement', 'completed')->where('result', 'success')->count();
                $missionTypesVictoryAverage = $missionTypesCompleted > 0 ? round(($missionTypesVictories / $missionTypesCompleted) * 100, 2) : 0;

                $agentAndStats[] = [
                    'agent' => $agent,
                    'mapName' => $selectedMap->name,
                    'mapMissionsCompleted' => $mapMissionsCompleted,
                    'mapVictories' => $mapVictories,
                    'mapVictoryAverage' => $mapVictoryAverage,
                    'missionTypeName' => $selectedMissionType->name,
                    'missionTypesCompleted' => $missionTypesCompleted,
                    'missionTypesVictories' => $missionTypesVictories,
                    'missionTypesVictoryAverage' => $missionTypesVictoryAverage,
                ];
            }

            return $agentAndStats;
        });
    }

    // Crear el agente como borrador (Deja de ser borrador cuando ya tiene sus 4 habilidades)
    public function createAgent(array $data){
        //DB::transaction evita datos huerfanos, si algo falla en agents revierte la creación del user
        return DB::transaction(function () use ($data){
            //Buscar el rol de usuario "agent"
            $userRole = Role::where('name', 'agent')->first();

            //Crear las credenciales del usuario
            $user = User::create([
                'email'=>$data['email'],
                'password'=>bcrypt($data['password']),
                'role_id'  => $userRole?->id,
            ]);
            
            $nationality = $this->getNationality($data['country_code'] ?? null);

            // Las imágenes van en una carpeta con el slug del agente
            $slug = Str::slug($data['name']);
            $profilePhotoPath = $data['profile_photo']->store("agents/$slug", 'public');
            $agentPhotoPath = $data['agent_photo']->store("agents/$slug", 'public');

            $agentClass = AgentClasses::where('id', $data['class'])->first();

            //Crear el perfil del agente y relacionarlo a sus credenciales recien creadas
            $agent = Agent::create([
                'user_id' => $user->id,
                'name'=>$data['name'],
                'bio' => $data['bio'],
                'profile_photo' => $profilePhotoPath,
                'agent_photo' => $agentPhotoPath,
                'nationality_id' => $nationality->id ?? null,
                'class_id' => $agentClass->id,
            ]);

            return $agent->load('nationality', 'class');
        });
    }

    // Crear el agente como borrador (Deja de ser borrador cuando ya tiene sus 4 habilidades)
    public function updateNewAgent(array $data, Agent $agent){
        //DB::transaction evita datos huerfanos, si algo falla en agents revierte la creación del user
        return DB::transaction(function () use ($data, $agent){
            if ($agent->is_draft === 0) {
                return [
                    'body' => [
                        'message' => 'Solo se pueden modificar agentes pre registrados.',
                    ],
                    'code' => 409,
                ];
            }

            $user = $agent->user;
            //Crear las credenciales del usuario
            $user->update([
                'email'=>$data['email'],
                'password'=>bcrypt($data['password']),
            ]);
            
            $nationality = $this->getNationality($data['country_code'] ?? null);

            // Las imagenes van en una carpeta con el slug del agente
            $newSlug = Str::slug($data['name']);
            $this->renameAgentImageFolder($agent, $newSlug);

            $agentClass = AgentClasses::where('id', $data['class'])->first();

            //Crear el perfil del agente y relacionarlo a sus credenciales recien creadas
            $updateData = [
                'name' => $data['name'],
                'slug' => $newSlug,
                'bio' => $data['bio'],
                'nationality_id' => $nationality->id ?? null,
                'class_id' => $agentClass->id,
            ];
            
            if (isset($data['profile_photo'])) {
                $updateData['profile_photo'] = $data['profile_photo']
                    ->store("agents/$newSlug", 'public');
            }
            
            if (isset($data['agent_photo'])) {
                $updateData['agent_photo'] = $data['agent_photo']
                    ->store("agents/$newSlug", 'public');
            }
            
            $agent->update($updateData);

            return $agent->load('nationality', 'class');
        });
    }

    // Actualizar foto de perfil
    public function updateProfilePhoto(array $data, Agent $agent){
        $path = $data['profile_photo']->store("agents/$agent->slug", 'public');
        $agent->update([
            'profile_photo' => $path
        ]);
        return $agent->load('abilities', 'nationality', 'class');
    }
    
    // Actualizar imagen del agente
    public function updateAgentPhoto(array $data, Agent $agent){
        $path = $data['agent_photo']->store("agents/$agent->slug", 'public');
        $agent->update([
            'agent_photo' => $path
        ]);
        return $agent->load('abilities', 'nationality', 'class');
    }

    // Actualizar perfil del agente
    public function updateAgentProfile(array $data, Agent $agent){
        return DB::transaction(function () use ($data, $agent){
            $nationality = $this->getNationality($data['country_code'] ?? null);

            $agentClass = AgentClasses::where('id', $data['class'])->first();

            // Renombrar carpeta para imagenes en caso de que el nombre del agente cambie
            $newSlug = Str::slug($data['name']);
            $this->renameAgentImageFolder($agent, $newSlug);

            $agent->update([
                'name' => $data['name'],
                'slug'=>$newSlug,
                'bio' => $data['bio'],
                'nationality_id' => $nationality->id ?? null,
                'class_id' => $agentClass->id
            ]);
            return $agent->load('abilities', 'nationality', 'class');
        });
    }

    // Obtener nacionalidad del agente
    private function getNationality(?string $countryCode): ?Nationality {
        if (!$countryCode) {
            return null;
        }

        $countries = json_decode(
            Storage::disk('public')->get('countries.json'),
            true
        );
                
        $country = collect($countries)->firstWhere('code', $countryCode);
                
        return Nationality::firstOrCreate([
            'country_code' => $country['code'],
            'country_name' => $country['name_es'],
        ]);
    }

    // Renombrar capreta de iamgenes si el slug del agente cambia y actualizar ruta en db
    private function renameAgentImageFolder(Agent $agent, string $newSlug): void {
        $oldSlug = Str::slug($agent->slug);

        if ($oldSlug === $newSlug) {
            return;
        }

        $oldPath = Storage::disk('public')->path("agents/{$oldSlug}");
        $newPath = Storage::disk('public')->path("agents/{$newSlug}");            
        
        rename($oldPath, $newPath);

        $agent->profile_photo = str_replace(
            "agents/{$oldSlug}/",
            "agents/{$newSlug}/",
            $agent->profile_photo
        );

        $agent->agent_photo = str_replace(
            "agents/{$oldSlug}/",
            "agents/{$newSlug}/",
            $agent->agent_photo
        );

        foreach ($agent->abilities as $ability) {
            $ability->icon = str_replace(
                "agents/{$oldSlug}/",
                "agents/{$newSlug}/",
                $ability->icon
            );
            $ability->save();
        }
    }
}