<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles globales
        $globalRoles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Acceso completo al sistema',
                'scope' => 'global',
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrador del sistema',
                'scope' => 'global',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Gestor de proyectos y equipos',
                'scope' => 'global',
            ],
            [
                'name' => 'Member',
                'slug' => 'member',
                'description' => 'Miembro estándar',
                'scope' => 'global',
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Solo lectura',
                'scope' => 'global',
            ],
        ];

        // Roles por equipo
        $teamRoles = [
            [
                'name' => 'Team Admin',
                'slug' => 'team-admin',
                'description' => 'Administrador del equipo',
                'scope' => 'team',
            ],
            [
                'name' => 'Team Editor',
                'slug' => 'team-editor',
                'description' => 'Editor del equipo',
                'scope' => 'team',
            ],
            [
                'name' => 'Team Viewer',
                'slug' => 'team-viewer',
                'description' => 'Visualizador del equipo',
                'scope' => 'team',
            ],
        ];

        // Roles por proyecto
        $projectRoles = [
            [
                'name' => 'Project Admin',
                'slug' => 'project-admin',
                'description' => 'Administrador del proyecto',
                'scope' => 'project',
            ],
            [
                'name' => 'Project Editor',
                'slug' => 'project-editor',
                'description' => 'Editor del proyecto',
                'scope' => 'project',
            ],
            [
                'name' => 'Project Viewer',
                'slug' => 'project-viewer',
                'description' => 'Visualizador del proyecto',
                'scope' => 'project',
            ],
        ];

        foreach (array_merge($globalRoles, $teamRoles, $projectRoles) as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
