<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersProductsSeeder extends Seeder
{
    private $usersPerBranch = 3; // Number of users per branch at each level
    private $token = "PM-116010";

    public function run()
    {
        // Create 3 top affiliates
        for ($i = 0; $i < 3; $i++) {
            $affiliate = User::create([
                'fname' => 'Affiliate' . ($i + 10),
                'lname' => 'User',
                'email' => 'affiliate' . ($i + 10) . '@example.com',
                'username' => 'affiliate' . ($i + 10),
                'phone' => '12345678' . $i,
                'user_type' => 'Affiliate',
                'status' => '1',
                'photo' => "assets.pmall.ng/user/default.png",
                'my_ref_id' => $this->generateREF(),
                'description' => 'Affiliate User',
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
                'ref_id' => $this->token,
                'password' => Hash::make('password'),
                'role_id' => null,
                'package_id' => null,
                'isActive' => true,
            ]);

            // Start creating the referral chain from this affiliate
            $this->createUsers($affiliate);
        }
    }

    private function createUsers(User $referrer)
    {
        $userType = 'Affiliate'; // You can set this to a random value if needed
        for ($j = 0; $j < ($this->usersPerBranch); $j++) {

            $userType = $userType == 'Affiliate' ? "Vendor" : "Affiliate";

            $store_name = null;
            $store_id = null;
            $store_url = null;
            if ($userType == "Vendor") {
                $store_name = $this->generateRandomName();
                $store_id = "PMS-" . rand(100000, 999999);
                $store_url = "https://assets.pmall.ng";
            }
            $user = User::create([
                'fname' => $this->generateRandomName(),
                'lname' => $this->generateRandomName(),
                'email' => $this->generateRandomEmail(),
                'username' => $this->generateRandomUsername(),
                'phone' => $this->generateRandomPhone(),
                'user_type' => $userType,
                'status' => '1',
                'photo' => "assets.pmall.ng/user/default.png",
                'my_ref_id' => $this->generateREF(),
                'description' => 'User',

                'store_name' => $store_name,
                'store_id' => $store_id,
                'store_url' => $store_url,
                'acct_name' => null,
                'acct_number' => null,
                'acct_type' => null,
                'bank' => null,
                'state' => null,
                'lga' => null,
                'address' => null,
                'ref_id' => $referrer->my_ref_id,
                'password' => Hash::make('password'),
                'role_id' => null,
                'package_id' => null,
                'isActive' => true,
            ]);
        }
    }

    private function generateRandomName()
    {
        return Str::random(6);
    }   

    private function generateRandomEmail()
    {
        return Str::random(10) . '@example.com';
    }

    private function generateRandomUsername()
    {
        return Str::random(8);
    }

    private function generateRandomPhone()
    {
        return '091' . rand(10000000, 99999999);
    }

    private function generateREF()
    {
        return "PM-" . rand(100000, 999999);
    }
}
