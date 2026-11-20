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
            $file = $data['photo'];

            $path = 'maps/test-' . uniqid() . '.' . $file->extension();

            $contents = file_get_contents($file->getRealPath());
            try {
                $written = Storage::disk('public')->put($path, $contents);
            
                return response()->json([
                    'written' => $written,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'previous' => $e->getPrevious()?->getMessage(),
                ], 500);
            }
            /* $updateData['photo'] = $data['photo']->store('maps', 'public'); */
        }

        $map->update($updateData);
        return $map;
    }
}