<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $memberRole = Role::where('slug', 'member')->first();
        $viewerRole = Role::where('slug', 'viewer')->first();

        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@taskmanager.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@taskmanager.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($superAdminRole && !DB::table('role_user')->where('user_id', $superAdmin->id)->where('role_id', $superAdminRole->id)->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $superAdmin->id,
                'role_id' => $superAdminRole->id,
                'roleable_type' => User::class,
                'roleable_id' => $superAdmin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'manager@taskmanager.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@taskmanager.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($adminRole && !DB::table('role_user')->where('user_id', $admin->id)->where('role_id', $adminRole->id)->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $admin->id,
                'role_id' => $adminRole->id,
                'roleable_type' => User::class,
                'roleable_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Manager
        $manager = User::updateOrCreate(
            ['email' => 'project.manager@taskmanager.com'],
            [
                'name' => 'Project Manager',
                'email' => 'project.manager@taskmanager.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($managerRole && !DB::table('role_user')->where('user_id', $manager->id)->where('role_id', $managerRole->id)->exists()) {
            DB::table('role_user')->insert([
                'user_id' => $manager->id,
                'role_id' => $managerRole->id,
                'roleable_type' => User::class,
                'roleable_id' => $manager->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Developers/Members
        $developers = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@taskmanager.com',
                'role' => $memberRole,
            ],
            [
                'name' => 'María García',
                'email' => 'maria.garcia@taskmanager.com',
                'role' => $memberRole,
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos.rodriguez@taskmanager.com',
                'role' => $memberRole,
            ],
            [
                'name' => 'Ana López',
                'email' => 'ana.lopez@taskmanager.com',
                'role' => $memberRole,
            ],
            [
                'name' => 'Pedro Martínez',
                'email' => 'pedro.martinez@taskmanager.com',
                'role' => $memberRole,
            ],
        ];

        foreach ($developers as $dev) {
            $user = User::updateOrCreate(
                ['email' => $dev['email']],
                [
                    'name' => $dev['name'],
                    'email' => $dev['email'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if ($dev['role'] && !DB::table('role_user')->where('user_id', $user->id)->where('role_id', $dev['role']->id)->exists()) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $dev['role']->id,
                    'roleable_type' => User::class,
                    'roleable_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Viewers
        $viewers = [
            [
                'name' => 'Cliente Demo',
                'email' => 'cliente@taskmanager.com',
                'role' => $viewerRole,
            ],
            [
                'name' => 'Stakeholder',
                'email' => 'stakeholder@taskmanager.com',
                'role' => $viewerRole,
            ],
        ];

        foreach ($viewers as $viewer) {
            $user = User::updateOrCreate(
                ['email' => $viewer['email']],
                [
                    'name' => $viewer['name'],
                    'email' => $viewer['email'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            if ($viewer['role'] && !DB::table('role_user')->where('user_id', $user->id)->where('role_id', $viewer['role']->id)->exists()) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $viewer['role']->id,
                    'roleable_type' => User::class,
                    'roleable_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Usuarios creados exitosamente!');
        $this->command->info('');
        $this->command->info('📋 Credenciales de acceso:');
        $this->command->info('   Email: admin@taskmanager.com | Password: password (Super Admin)');
        $this->command->info('   Email: manager@taskmanager.com | Password: password (Admin)');
        $this->command->info('   Email: project.manager@taskmanager.com | Password: password (Manager)');
        $this->command->info('   Email: juan.perez@taskmanager.com | Password: password (Member)');
        $this->command->info('   Email: cliente@taskmanager.com | Password: password (Viewer)');
        $this->command->info('');
        $this->command->info('💡 Todos los usuarios tienen la contraseña: password');
    }
}

