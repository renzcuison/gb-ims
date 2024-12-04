<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',  // Use your desired admin email
                'password' => bcrypt('adminpassword'),  // Use your desired password
                'role' => 'admin',  // Assuming you have a 'role' field in your users table
                'email_verified_at' => now(), 
            ]);
        }
    }
}

