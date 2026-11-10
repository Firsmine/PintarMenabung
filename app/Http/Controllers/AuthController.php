<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:225',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => array_merge(
                    $user->toArray(),
                    ['token' => $token]
                )
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username or password incorrect'
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => array_merge(
                    $user->toArray(),
                    ['token' => $token]
                )
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid field',
                'errors' => $e->errors()
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], 401);
        }
        $user->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ], 200);
    }
}
