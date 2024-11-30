<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|in:admin,user', // Validate role input
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Set default role to 'user' if not provided
        $role = $request->role ?? 'user';

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role, // Include role
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
        ], 201);
    }

    // Login existing user
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt to find the user by name
        $user = User::where('name', $request->name)->first();

        // Check if user exists and password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create token using Sanctum (Laravel's default token-based authentication)
        $token = $user->createToken('AppToken')->plainTextToken;

        // Return response with the token
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $user->role, // Include role in response
        ]);
    }
}
