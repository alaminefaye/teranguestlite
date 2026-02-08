<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Liste des véhicules de l'établissement (pour formulaire Location).
     * Filtres optionnels : vehicle_type, seats (nombre minimum de places).
     */
    public function index(Request $request)
    {
        $query = Vehicle::where('enterprise_id', $request->user()->enterprise_id)
            ->available()
            ->ordered();

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('seats') && is_numeric($request->seats)) {
            $query->where('number_of_seats', '>=', (int) $request->seats);
        }

        $vehicles = $query->get()->map(fn (Vehicle $v) => [
            'id' => $v->id,
            'name' => $v->name,
            'vehicle_type' => $v->vehicle_type,
            'vehicle_type_label' => $v->type_label,
            'number_of_seats' => $v->number_of_seats,
            'image' => $v->image ? url('storage/' . $v->image) : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }
}
