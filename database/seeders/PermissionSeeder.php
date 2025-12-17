<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Teams
            ['name' => 'teams.view', 'slug' => 'teams.view', 'resource' => 'team', 'action' => 'view', 'description' => 'Ver equipos'],
            ['name' => 'teams.create', 'slug' => 'teams.create', 'resource' => 'team', 'action' => 'create', 'description' => 'Crear equipos'],
            ['name' => 'teams.update', 'slug' => 'teams.update', 'resource' => 'team', 'action' => 'update', 'description' => 'Actualizar equipos'],
            ['name' => 'teams.delete', 'slug' => 'teams.delete', 'resource' => 'team', 'action' => 'delete', 'description' => 'Eliminar equipos'],
            ['name' => 'teams.manage_members', 'slug' => 'teams.manage_members', 'resource' => 'team', 'action' => 'manage_members', 'description' => 'Gestionar miembros del equipo'],

            // Projects
            ['name' => 'projects.view', 'slug' => 'projects.view', 'resource' => 'project', 'action' => 'view', 'description' => 'Ver proyectos'],
            ['name' => 'projects.create', 'slug' => 'projects.create', 'resource' => 'project', 'action' => 'create', 'description' => 'Crear proyectos'],
            ['name' => 'projects.update', 'slug' => 'projects.update', 'resource' => 'project', 'action' => 'update', 'description' => 'Actualizar proyectos'],
            ['name' => 'projects.delete', 'slug' => 'projects.delete', 'resource' => 'project', 'action' => 'delete', 'description' => 'Eliminar proyectos'],
            ['name' => 'projects.manage_members', 'slug' => 'projects.manage_members', 'resource' => 'project', 'action' => 'manage_members', 'description' => 'Gestionar miembros del proyecto'],
            ['name' => 'projects.manage_settings', 'slug' => 'projects.manage_settings', 'resource' => 'project', 'action' => 'manage_settings', 'description' => 'Gestionar configuración del proyecto'],

            // Tasks
            ['name' => 'tasks.view', 'slug' => 'tasks.view', 'resource' => 'task', 'action' => 'view', 'description' => 'Ver tareas'],
            ['name' => 'tasks.create', 'slug' => 'tasks.create', 'resource' => 'task', 'action' => 'create', 'description' => 'Crear tareas'],
            ['name' => 'tasks.update', 'slug' => 'tasks.update', 'resource' => 'task', 'action' => 'update', 'description' => 'Actualizar tareas'],
            ['name' => 'tasks.delete', 'slug' => 'tasks.delete', 'resource' => 'task', 'action' => 'delete', 'description' => 'Eliminar tareas'],
            ['name' => 'tasks.assign', 'slug' => 'tasks.assign', 'resource' => 'task', 'action' => 'assign', 'description' => 'Asignar tareas'],
            ['name' => 'tasks.move', 'slug' => 'tasks.move', 'resource' => 'task', 'action' => 'move', 'description' => 'Mover tareas en Kanban'],

            // Comments
            ['name' => 'comments.view', 'slug' => 'comments.view', 'resource' => 'comment', 'action' => 'view', 'description' => 'Ver comentarios'],
            ['name' => 'comments.create', 'slug' => 'comments.create', 'resource' => 'comment', 'action' => 'create', 'description' => 'Crear comentarios'],
            ['name' => 'comments.update', 'slug' => 'comments.update', 'resource' => 'comment', 'action' => 'update', 'description' => 'Actualizar comentarios'],
            ['name' => 'comments.delete', 'slug' => 'comments.delete', 'resource' => 'comment', 'action' => 'delete', 'description' => 'Eliminar comentarios'],

            // Files
            ['name' => 'files.upload', 'slug' => 'files.upload', 'resource' => 'file', 'action' => 'upload', 'description' => 'Subir archivos'],
            ['name' => 'files.download', 'slug' => 'files.download', 'resource' => 'file', 'action' => 'download', 'description' => 'Descargar archivos'],
            ['name' => 'files.delete', 'slug' => 'files.delete', 'resource' => 'file', 'action' => 'delete', 'description' => 'Eliminar archivos'],

            // System
            ['name' => 'system.manage_users', 'slug' => 'system.manage_users', 'resource' => 'system', 'action' => 'manage_users', 'description' => 'Gestionar usuarios del sistema'],
            ['name' => 'system.manage_roles', 'slug' => 'system.manage_roles', 'resource' => 'system', 'action' => 'manage_roles', 'description' => 'Gestionar roles y permisos'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
