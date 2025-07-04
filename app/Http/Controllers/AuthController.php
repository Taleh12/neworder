<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('API Token')->plainTextToken;

            event(new Login(auth()->guard(), $user, false));

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => new UserResource($user),
            ], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request): JsonResponse
    {
        // Registration logic here
        // This is a placeholder for the actual registration implementation

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        // Create the user
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        // Create an API token for the user
        $token = $user->createToken('API Token')->plainTextToken;

        //spatie role
        $user->assignRole('Team Lead'); // Assign a default role, e.g., 'Team Lead'
        // You can also assign roles based on request data if needed
        // Return the response
        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new \App\Http\Resources\UserResource($user),
        ], 201);
        // Placeholder response for unimplemented registration
        return response()->json(['message' => 'Registration not implemented'], 501);
    }


    public function user(Request $request): JsonResponse
    {
        // Return the authenticated user
        return response()->json(new UserResource($request->user()));
    }
}
