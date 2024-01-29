<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;

    /**
     * login api
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
     * Register api
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
        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 400);
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['username'] = $user->name;
        return response()->json(['data' => $success, 'success' => ['message' => 'User created successfully']], $this->successStatus);
    }

    /**
     * me api
     *
     * @return JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        return response()->json(['data' => $user], $this->successStatus);
    }

    /**
     * update user api
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
     * destroy user api
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
     * logout api
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
