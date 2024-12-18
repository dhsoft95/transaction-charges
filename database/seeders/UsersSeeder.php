<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'david@simbamoney.co.tz',
            'password' => Hash::make('@Dmin2021!'),
        ]);
        $superAdmin->assignRole('super_admin');

        // Team Members (Product Team)
        $teamMembers = [
            [
                'name' => 'John Product',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Jane Product',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($teamMembers as $member) {
            $user = User::create($member);
            $user->assignRole('team_member');
        }

        // Finance Team
        $financeTeam = [
            [
                'name' => 'Sarah Finance',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Mike Finance',
                'email' => 'mike@example.com',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($financeTeam as $member) {
            $user = User::create($member);
            $user->assignRole('finance_approver');
        }

        // CEO
        $ceo = User::create([
            'name' => 'David CEO',
            'email' => 'ceo@example.com',
            'password' => Hash::make('password123'),
        ]);
        $ceo->assignRole('ceo');
    }
}
