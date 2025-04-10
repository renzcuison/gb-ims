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
                'email' => 'admin@example.com',  
                'password' => bcrypt('adminpassword'), 
                'role' => 'admin',  
                'email_verified_at' => now(), 
            ]);
        }
    }
}

