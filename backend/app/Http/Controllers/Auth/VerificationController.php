<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        // Find the user by ID
        $user = User::findOrFail($id);

        // Check if the hash matches the user's email hash
        if (sha1($user->email) !== $hash) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        // Manually update the email_verified_at field
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Email successfully verified.']);
    }
}


