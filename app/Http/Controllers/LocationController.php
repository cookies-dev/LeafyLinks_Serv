<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function getNearestLocations(Request $request, $x)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        $nearestLocations = Location::orderByRaw('SQRT(POW(lat - ?, 2) + POW(lng - ?, 2))', [$latitude, $longitude])
            ->limit($x)
            ->get();

        return response()->json($nearestLocations);
    }

    public function getUserLocations($userId)
    {
        $locations = Location::where('user_id', $userId)->get();
        return response()->json($locations);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required|string',
            'public' => 'boolean',
        ]);

        $location = new Location($request->all());
        $location->user_id = Auth::id();
        $location->save();

        return response()->json($location, 201);
    }

    public function edit(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required|string',
            'public' => 'boolean',
        ]);

        $location->update($request->all());
        return response()->json($location);
    }

    public function delete($id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $location->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
