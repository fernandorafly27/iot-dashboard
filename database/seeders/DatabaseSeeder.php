<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'program']);

        // Create or update default user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'st@techupi.id'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Ddw9889##'),
                'email_verified_at' => now(),
            ]
        );

        // Assign role only if not already assigned
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
