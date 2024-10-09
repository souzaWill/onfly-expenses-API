<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('api')->plainTextToken;
        $success['name'] = $user->name;

        return response()->json([
            'success' => true,
            'data' => $success,
            'message' => 'User registered successfully.',
        ], 201);
    }

    /**
     * Login api
     */
    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $user->tokens()->delete();
            $success['token'] = $user->createToken('api')->plainTextToken;
            $success['name'] = $user->name;

            return response()->json([
                'success' => true,
                'data' => $success,
                'message' => 'User login successfully.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
        ], 200);
    }
}
