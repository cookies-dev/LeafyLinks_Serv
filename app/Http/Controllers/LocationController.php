<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Locations",
 *     description="Endpoints for managing locations"
 * )
 */
class LocationController extends Controller
{
    private function haversineGreatCircleDistance($latFrom, $longFrom, $latTo, $longTo)
    {
        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($longFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($longTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * 6371;
    }

    /**
     * @OA\Get(
     *     path="/api/locations/{lat}&{lng}/{dist?}",
     *     summary="Get nearest locations",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="lat",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="number"),
     *         description="Latitude"
     *     ),
     *     @OA\Parameter(
     *         name="lng",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="number"),
     *         description="Longitude"
     *     ),
     *     @OA\Parameter(
     *         name="dist",
     *         in="path",
     *         @OA\Schema(type="number"),
     *         description="Distance in kilometers (optional)"
     *     ),
     *     @OA\Response(response="200", description="List of nearest locations"),
     *     @OA\Response(response="404", description="No locations found")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/locations/{id}",
     *     summary="Get location by ID",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Location ID"
     *     ),
     *     @OA\Response(response="200", description="Location details"),
     *     @OA\Response(response="404", description="Location not found")
     * )
     */
    public function getLocationById($id)
    {
        $location = Location::findOrFail($id);
        return response()->json(['data' => $location]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations/user/{userId}",
     *     summary="Get locations by user ID",
     *     tags={"Locations"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(response="200", description="User's locations"),
     *     @OA\Response(response="403", description="Unauthorized")
     * )
     */
    public function getUserLocations(int $userId)
    {
        $locations = Location::where('user_id', $userId)->get();
        return response()->json(['data' => $locations]);
    }

    /**
     * @OA\Get(
     *     path="/api/locations",
     *     summary="Get locations of the logged in user",
     *     tags={"Locations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="User's locations"),
     *     @OA\Response(response="403", description="Unauthorized")
     * )
     */
    public function getLocations(): JsonResponse
    {
        $locations = Location::where('user_id', Auth::id())->get();
        return response()->json(['data' => $locations]);
    }


    /**
     * @OA\Post(
     *     path="/api/locations",
     *     summary="Create a new location",
     *     tags={"Locations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Location data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="public", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Location created successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'string',
            'public' => 'boolean'
        ]);

        $location = new Location($request->all());
        $location->user_id = Auth::id();
        $location->save();

        return response()->json(
            ['message' => 'Location created successfully', 'data' => $location],
            201
        );
    }

    /**
     * @OA\Put(
     *     path="/api/locations/{id}",
     *     summary="Edit a location",
     *     tags={"Locations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Updated location data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="lat", type="number"),
     *                 @OA\Property(property="lng", type="number"),
     *                 @OA\Property(property="address", type="string"),
     *                 @OA\Property(property="public", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Location ID"
     *     ),
     *     @OA\Response(response="200", description="Location updated successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Location not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
    public function edit(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not logged in as the owner of this location'],
                403
            );
        }

        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'string',
            'public' => 'boolean',
        ]);

        $location->update($request->all());
        return response()->json(['message' => 'Location updated successfully', 'data' => $location]);
    }

    /**
     * @OA\Delete(
     *     path="/api/locations/{id}",
     *     summary="Delete a location",
     *     tags={"Locations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Location ID"
     *     ),
     *     @OA\Response(response="200", description="Location deleted successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Location not found")
     * )
     */
    public function delete($id)
    {
        $location = Location::findOrFail($id);

        if (Auth::id() !== $location->user_id) {
            return response()->json(
                ['error' => 'Unauthorized', 'message' => 'You are not logged in as the owner of this location'],
                403
            );
        }

        $location->delete();
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
