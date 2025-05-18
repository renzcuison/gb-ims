<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered; 
use App\Notifications\VerifyEmailWithCustomUrl;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => [
                'required',
                'regex:/^((\+63)|0)\d{10}$/',
                'unique:users',
            ],
            'role' => 'nullable|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = $request->role ?? 'user';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'phone_number' => $request->phone_number,
        ]);

        $user->notify(new VerifyEmailWithCustomUrl($user));

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('name', $request->name)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $user->role,
            'phone_number' => $user->phone_number,
        ]);
    }

    public function index()
    {
        $users = User::where('role', 'user')->get(['id', 'name', 'email', 'phone_number', 'role', 'email_verified_at']);
        return response()->json($users);
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    }

    public function checkRole(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return response()->json(['message' => 'User is an admin']);
        } else {
            return response()->json(['message' => 'User is not an admin']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone_number' => 'required|string|unique:users,phone_number,' . $id,
                'role' => 'required|in:user,admin',
            ]);

            $user->update($validated);

            return response()->json(['message' => 'User updated successfully!', 'user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found or could not be updated.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json(['message' => 'User deleted successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User not found or could not be deleted.'], 500);
        }
    }
}
