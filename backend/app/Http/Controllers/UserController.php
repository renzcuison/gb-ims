<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered; // Import the event for email verification
use App\Notifications\VerifyEmailWithCustomUrl;
class UserController extends Controller
{

    public function register(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => [
                'required',
                'regex:/^((\+63)|0)\d{10}$/',
                'unique:users',
            ],
            'role' => 'nullable|in:user,admin', // Role must be either 'user' or 'admin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $role = $request->role ?? 'user';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'phone_number' => $request->phone_number, // Add phone number
        ]);

        $user->notify(new VerifyEmailWithCustomUrl($user)); // Send the custom verification notification

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'user' => $user,
        ], 201);
    }


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

        // Create a token using Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        // Return response with the token and additional user details
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'role' => $user->role, // Include role in response
            'phone_number' => $user->phone_number, // Include phone number
        ]);
    }


    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    // This method returns the authenticated user's details
    public function getUser(Request $request)
    {
        // Fetch the currently authenticated user
        $user = Auth::user();

        // Return the user data as JSON
        return response()->json($user);
    }

    // Optional: This method checks the user's role
    public function checkRole(Request $request)
    {
        $user = Auth::user();

        // Check if the user has 'admin' role
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
                'phone_number' => 'nullable|string',
                'role' => 'nullable|in:user,admin', // Role is optional for update
            ]);


            $user->update($validated); // This will update the role along with other fields

            return response()->json(['message' => 'User updated successfully!', 'user' => $user], 200);
        } catch (\Exception $e) {



            return response()->json(['error' => 'User not found or could not be updated.'], 500);
        }
    }


    public function destroy($id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Delete the user
            $user->delete();

            return response()->json(['message' => 'User deleted successfully!'], 200);
        } catch (\Exception $e) {



            return response()->json(['error' => 'User not found or could not be deleted.'], 500);
        }
    }
}