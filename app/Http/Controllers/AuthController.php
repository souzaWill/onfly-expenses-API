<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Registra um novo usuario
     *
     * @unauthenticated
     *
     * @response 200
     * {
     *       "success": true,
     *       "data": {
     *           "token": "1|b4HnecmVF2PdLXO8fFK5ZgkfxmNULocubD6EKoYDdbc0aca3",
     *           "name": "natus"
     *       },
     *       "message": "User registered successfully."
     *   }
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
     * Login
     *
     * Permite que um usuário faça login na aplicação.
     *
     * @bodyParam email string required O endereço de e-mail do usuário. Exemplo: user@example.com
     * @bodyParam password string required A senha do usuário. Exemplo: secret
     *
     * @response 200 {
     *    "success": true,
     *    "data": {
     *        "token": "1|abc1234567...",
     *        "name": "John Doe"
     *    },
     *    "message": "User login successfully."
     * }
     * @response 401 {
     *    "success": false,
     *    "message": "Unauthorized"
     * }
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

    /**
     * Logout do usuário
     *
     * Revoga o token de autenticação atual do usuário, efetuando o logout.
     *
     *
     * @authenticated
     *
     * @response 200 {
     *    "status": "success",
     *    "message": "User logged out successfully"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
        ], 200);
    }
}
