<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
{
    return Socialite::driver('google')->stateless()->redirect();
}

public function handleGoogleCallback()
{
    $googleUser = Socialite::driver('google')->stateless()->user();

    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName(),
            'password' => bcrypt(Str::random(16)),
            'email_verified_at' => now()
        ]
    );
    $token = $user->createToken('authToken')->plainTextToken;
    Auth::login($user);
    
    

    return redirect("http://localhost:5173/shop?token=$token");
}

}
