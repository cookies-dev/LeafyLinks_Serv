<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlantController extends Controller
{
    public function get(int $id)
    {
        $plant = Plant::findOrFail($id);
        return response()->json(['data' => $plant]);
    }

    public function search(string $query, int $limit)
    {
        $plants = Plant::where('name', 'like', '%' . $query . '%')->take($limit)->get();
        return response()->json(['data' => $plants]);
    }

    public function plants(int $idLocation)
    {
        $plants = Plant::where('location_id', $idLocation)->get();
        return response()->json(['data' => $plants]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'location_id' => 'required|integer',
            'trefle_id' => 'required|integer',
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);

        $location = Location::findOrFail($request->location_id);
        if ($location->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of this location'],
                403
            );
        }

        $plant = new Plant($request->all());
        $plant->save();
        return response()->json(['message' => 'Plant created successfully', 'data' => $plant], 201);
    }

    public function edit(Request $request, int $id)
    {
        $request->validate([
            'location_id' => 'required|integer',
            'trefle_id' => 'required|integer',
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);
        $plant = Plant::findOrFail($id);
        if ($plant->location->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of current location'],
                403
            );
        }

        $location = Location::findOrFail($request->location_id);
        if ($location->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of new location'],
                403
            );
        }

        $plant->update($request->all());
        return response()->json(['message' => 'Plant updated successfully', 'data' => $plant]);
    }

    public function delete(int $id)
    {
        $plant = Plant::findOrFail($id);

        if ($plant->location->user_id !== Auth::id()) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not the owner of this location'],
                403
            );
        }

        $plant->delete();
        return response()->json(['message' => 'Plant deleted successfully']);
    }
}
