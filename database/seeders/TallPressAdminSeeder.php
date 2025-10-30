<?php

namespace Sajdoko\TallPress\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TallPressAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userModel = config('tallpress.author_model', 'App\\Models\\User');
        $roleField = config('tallpress.roles.role_field', 'role');
        $addRoleField = config('tallpress.roles.add_role_field', true);

        // Prepare user data
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'email' => 'editor@example.com',
                'name' => 'Editor User',
                'password' => Hash::make('password'),
                'role' => 'editor',
            ],
            [
                'email' => 'author@example.com',
                'name' => 'Author User',
                'password' => Hash::make('password'),
                'role' => 'author',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];

            // Prepare data for creation/update
            $createData = [
                'name' => $userData['name'],
                'password' => $userData['password'],
            ];

            // Only add role field if package manages roles
            if ($addRoleField) {
                $createData[$roleField] = $role;
            }

            $user = $userModel::firstOrCreate(
                ['email' => $userData['email']],
                $createData
            );

            // Always update role to ensure it's set correctly (database default might override)
            // Use forceFill to bypass mass assignment protection
            if ($addRoleField) {
                $user->forceFill([$roleField => $role])->save();
            }

            if ($this->command) {
                $this->command->warn("Created user: {$userData['email']} with role: ".($addRoleField ? $role : 'N/A'));
            }

            // If using external ACL, provide instructions
            if (! $addRoleField && $this->command) {
                $this->command->warn("⚠️  User {$userData['email']} created without role.");
                $this->command->line("   Assign '{$role}' role using your ACL package.");
            }
        }

        if ($this->command) {
            $this->command->info('Blog admin users created successfully!');
            $this->command->error('Admin: admin@example.com / password');
            $this->command->error('Editor: editor@example.com / password');
            $this->command->error('Author: author@example.com / password');

            if (! $addRoleField) {
                $this->command->line('');
                $this->command->warn('Remember to assign roles using your ACL package!');
            }
        }
    }
}
