<?php

namespace App\Http\Controllers;

use App\Http\Requests\MapRequest;
use App\Http\Requests\UpdateMapRequest;
use App\Models\Map;
use App\Services\MapService;

class MapController extends Controller
{
    protected $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    // Obtener todos los mapas
    public function getMaps(){
        $maps = Map::get();
        return response()->json($maps, 200);
    }

    // Crear nuevo mapa
    public function newMap(MapRequest $request){
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        $map = $this->mapService->createMap($data);

        return response()->json([
            'message' => 'Nuevo mapa agregado coorrectamente.',
            'map' => $map
        ], 201);
    }

    // Editar mapa
    public function editMap(UpdateMapRequest $request, Map $map){
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo');
        }

        $mapUpdated = $this->mapService->updateMap($data, $map);

        return response()->json([
            'message' => 'Mapa actualizado correctamente.',
            'map' => $mapUpdated
        ], 201);
    }
}
