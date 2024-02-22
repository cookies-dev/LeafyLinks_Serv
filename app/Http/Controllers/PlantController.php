<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Plants",
 *     description="Endpoints for managing plants"
 * )
 */
class PlantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/plants/{id}",
     *     summary="Get plant by ID",
     *     tags={"Plants"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Plant ID"
     *     ),
     *     @OA\Response(response="200", description="Plant details"),
     *     @OA\Response(response="404", description="Plant not found")
     * )
     */
    public function get(int $id)
    {
        $plant = Plant::findOrFail($id);
        return response()->json(['data' => $plant]);
    }

    /**
     * @OA\Get(
     *     path="/plants/search/query={query}&limit={limit}",
     *     summary="Search plants",
     *     tags={"Plants"},
     *     @OA\Parameter(
     *         name="query",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Search query"
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Limit for search results"
     *     ),
     *     @OA\Response(response="200", description="List of matching plants"),
     *     @OA\Response(response="404", description="No plants found")
     * )
     */
    public function search(string $query, int $limit)
    {
        $plants = Plant::where('name', 'like', '%' . $query . '%')->take($limit)->get();
        return response()->json(['data' => $plants]);
    }

    /**
     * @OA\Get(
     *     path="/plants/{idLocation}/plants",
     *     summary="Get plants by location ID",
     *     tags={"Plants"},
     *     @OA\Parameter(
     *         name="idLocation",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Location ID"
     *     ),
     *     @OA\Response(response="200", description="List of plants at the location"),
     *     @OA\Response(response="404", description="No plants found at the location")
     * )
     */
    public function plants(int $idLocation)
    {
        $plants = Plant::where('location_id', $idLocation)->get();
        return response()->json(['data' => $plants]);
    }

    /**
     * @OA\Post(
     *     path="/plants",
     *     summary="Create a new plant",
     *     tags={"Plants"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Plant data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="location_id", type="integer"),
     *                 @OA\Property(property="trefle_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="desc", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Plant created successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Location not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/plants/{id}",
     *     summary="Edit a plant",
     *     tags={"Plants"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Updated plant data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="location_id", type="integer"),
     *                 @OA\Property(property="trefle_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="desc", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Plant ID"
     *     ),
     *     @OA\Response(response="200", description="Plant updated successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Plant not found"),
     *     @OA\Response(response="422", description="Validation error")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/plants/{id}",
     *     summary="Delete a plant",
     *     tags={"Plants"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Plant ID"
     *     ),
     *     @OA\Response(response="200", description="Plant deleted successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     *     @OA\Response(response="404", description="Plant not found")
     * )
     */
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
