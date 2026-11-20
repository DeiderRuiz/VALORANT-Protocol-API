<?php

namespace App\Services;

use App\Models\Map;
use Illuminate\Support\Facades\Storage;

class MapService
{
    // Crear mapa
    public function createMap(array $data){
        $path = $data['photo']->store('maps', 'public');
        $map = Map::create([
            'name' => $data['name'],
            'photo' => $path,
            'description' => $data['description'],
        ]);
        return $map;
    }

    // Actualizar mapa
    public function updateMap(array $data, Map $map){
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        if (isset($data['photo'])) {
            $path = $data['photo']->store('maps', 'public');
            if (isset($data['photo'])) {
                $path = $data['photo']->store('maps', 'public');
            
                return response()->json([
                    'path' => $path,
                    'exists' => $path !== false
                        ? Storage::disk('public')->exists($path)
                        : false,
                    'directory_exists' => Storage::disk('public')->exists('maps'),
                ]);
            }
            /* $updateData['photo'] = $data['photo']->store('maps', 'public'); */
        }

        $map->update($updateData);
        /* return $map; */
    }
}