<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Jay-ar Zamora',
            'email' => 'jpzamora@dswd.gov.ph',
            'password' => Hash::make('123456789'), // default password
            'usergroup' => 'sysadmin',
            'approvalstatus' => 'A',
            'active' => true,
            'user_id' => 'jpzamora',
            'gender' => 'male',
            'address' => '',
        ]);
    }
}
