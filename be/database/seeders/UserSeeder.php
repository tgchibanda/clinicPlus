<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserRole;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
        	'name' => 'Payfast Admin', 
        	'email' => 'admin@clinicPlus.co.zw',
        	'password' => bcrypt('clinicPluszim'),
        ]);
  
        $userRole = UserRole::create([
        	'user_id' => $user->id, 
        	'role' => 'admin',
        	'status' => 'active',
        ]);
    }
}
