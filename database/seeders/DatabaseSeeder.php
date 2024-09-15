<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create the admin user
        $admin = User::create([
            'fname' => 'Admin',
            'lname' => 'User',
            'email' => 'admin@example.com',
            'username' => 'admin',
            'phone' => '1234567890',
            'user_type' => 'Admin',
            'status' => '1',
            'photo' => 'default.png',
            'my_ref_id' => $this->generateREF(),
            'description' => 'Administrator',
            'store_name' => null,
            'store_id' => null,
            'store_url' => null,
            'acct_name' => null,
            'acct_number' => null,
            'acct_type' => null,
            'bank' => null,
            'state' => null,
            'lga' => null,
            'address' => null,
            'ref_id' => null,
            'password' => Hash::make('password'), // Change this as needed
            'role_id' => null,
            'package_id' => null,
            'isActive' => true,
        ]);
    }

    private function generateREF()
    {
        return "PM-" . rand(100000, 999999);
    }
}
