<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    public function getNearestLocations($lat, $lng, $dist = 50)
    {
        $locations = Location::all()
            ->map(function ($location) use ($lat, $lng) {
                $distance = $this->haversineGreatCircleDistance($lat, $lng, $location->lat, $location->lng);
                return $location->toArray() + ['distance' => $distance];
            })
            ->filter(function ($location) use ($dist) {
                return $location['distance'] <= $dist;
            })
            ->sortBy('distance')
            ->take(10);

        return response()->json(['data' => $locations]);
    }

    public function getLocationById($id)
    {
        $location = Location::findOrFail($id);
        return response()->json(['data' => $location]);
    }

    public function getUserLocations(?int $userId = null)
    {
        if ($userId === null && Auth::id() === null) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'You are not logged in'], 403);
        }
        if ($userId === null) {
            $userId = Auth::id();
        }
        $locations = Location::where('user_id', $userId)->get();
        return response()->json(['data' => $locations]);
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

        return response()->json(['message' => 'Location created successfully', 'data' => $location], 201);
    }

    public function edit(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'You are not logged in as the owner of this location'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required|string',
            'public' => 'boolean',
        ]);

        $location->update($request->all());
        return response()->json(['message' => 'Location updated successfully', 'data' => $location]);
    }

    public function delete($id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'You are not logged in as the owner of this location'], 403);
        }

        $location->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
