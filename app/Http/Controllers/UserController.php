<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Validator;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Endpoints for managing users"
 * )
 */
class UserController extends Controller
{
    public $successStatus = 200;

    /**
     * @OA\Post(
     *     path="/api/users/login",
     *     summary="User Login",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="password", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successful login"),
     *     @OA\Response(response="400", description="Invalid request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Failed to login'], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('MyApp')->plainTextToken;

        return response()->json(['data' => ['token' => $token]]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/register",
     *     summary="User Registration",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="password", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="User created successfully"),
     *     @OA\Response(response="400", description="Invalid request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['username'] = $user->name;
        return response()->json(
            ['data' => $success, 'success' => ['message' => 'User created successfully']],
            $this->successStatus
        );
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get User by ID",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="User details retrieved successfully"),
     *     @OA\Response(response="404", description="User not found")
     * )
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getById(Request $request, int $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['data' => $user->only([
            'username',
            'first_name',
            'last_name',
            'profile_picture',
            'bio',
            'is_botanic',
            'is_garden'
        ])], $this->successStatus);
    }

    /**
     * @OA\Get(
     *     path="/api/users/me",
     *     summary="Get current user details",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="User details retrieved successfully")
     * )
     *
     * @return JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        return response()->json(['data' => $user], $this->successStatus);
    }

    /**
     * @OA\Put(
     *     path="/api/users/me",
     *     summary="Update current user details",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="User update data",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="profile_picture", type="string"),
     *             @OA\Property(property="bio", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="User updated successfully"),
     *     @OA\Response(response="400", description="Invalid request")
     * )
     *
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|unique:users,username,' . $user->id,
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'nullable',
            'phone' => 'nullable|digits_between:1,20',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'profile_picture' => 'nullable',
            'bio' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if ($request->has('is_botanic') && $user->is_botanic != $request->is_botanic) {
            return response()->json(['error' => 'Cannot edit is_botanic'], 400);
        }
        if ($request->has('is_garden') && $user->is_garden != $request->is_garden) {
            return response()->json(['error' => 'Cannot edit is_garden'], 400);
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->update($request->all());

        return response()->json(['success' => ['message' => 'User updated successfully']], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/me",
     *     summary="Delete current user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="User deleted successfully")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->delete();

        return response()->json(['success' => ['message' => 'User deleted successfully']], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users/logout",
     *     summary="Logout current user",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response="200", description="User logged out successfully"),
     *     @OA\Response(response="400", description="Invalid request")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        if (!$request->user()->currentAccessToken()) {
            return response()->json(['error' => 'User is not logged in'], 400);
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => ['message' => 'User logged out successfully']], 200);
    }
}
