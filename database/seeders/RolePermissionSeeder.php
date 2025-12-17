<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Todos los permisos
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id'));
        }

        // Admin - Permisos de gestión
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereIn('slug', [
                'teams.view', 'teams.create', 'teams.update', 'teams.manage_members',
                'projects.view', 'projects.create', 'projects.update', 'projects.manage_members', 'projects.manage_settings',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.assign', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update', 'comments.delete',
                'files.upload', 'files.download', 'files.delete',
            ])->pluck('id');
            $admin->permissions()->sync($adminPermissions);
        }

        // Manager - Permisos de gestión de proyectos
        $manager = Role::where('slug', 'manager')->first();
        if ($manager) {
            $managerPermissions = Permission::whereIn('slug', [
                'teams.view',
                'projects.view', 'projects.create', 'projects.update', 'projects.manage_members',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.assign', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update',
                'files.upload', 'files.download',
            ])->pluck('id');
            $manager->permissions()->sync($managerPermissions);
        }

        // Member - Permisos básicos
        $member = Role::where('slug', 'member')->first();
        if ($member) {
            $memberPermissions = Permission::whereIn('slug', [
                'teams.view',
                'projects.view',
                'tasks.view', 'tasks.create', 'tasks.update',
                'comments.view', 'comments.create', 'comments.update',
                'files.upload', 'files.download',
            ])->pluck('id');
            $member->permissions()->sync($memberPermissions);
        }

        // Viewer - Solo lectura
        $viewer = Role::where('slug', 'viewer')->first();
        if ($viewer) {
            $viewerPermissions = Permission::whereIn('slug', [
                'teams.view',
                'projects.view',
                'tasks.view',
                'comments.view',
                'files.download',
            ])->pluck('id');
            $viewer->permissions()->sync($viewerPermissions);
        }

        // Team Admin - Permisos de equipo
        $teamAdmin = Role::where('slug', 'team-admin')->first();
        if ($teamAdmin) {
            $teamAdminPermissions = Permission::whereIn('slug', [
                'teams.view', 'teams.update', 'teams.manage_members',
                'projects.view', 'projects.create', 'projects.update', 'projects.manage_members',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.assign', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update', 'comments.delete',
                'files.upload', 'files.download', 'files.delete',
            ])->pluck('id');
            $teamAdmin->permissions()->sync($teamAdminPermissions);
        }

        // Team Editor - Permisos de edición
        $teamEditor = Role::where('slug', 'team-editor')->first();
        if ($teamEditor) {
            $teamEditorPermissions = Permission::whereIn('slug', [
                'teams.view',
                'projects.view', 'projects.create', 'projects.update',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update',
                'files.upload', 'files.download',
            ])->pluck('id');
            $teamEditor->permissions()->sync($teamEditorPermissions);
        }

        // Team Viewer - Solo lectura
        $teamViewer = Role::where('slug', 'team-viewer')->first();
        if ($teamViewer) {
            $teamViewerPermissions = Permission::whereIn('slug', [
                'teams.view',
                'projects.view',
                'tasks.view',
                'comments.view',
                'files.download',
            ])->pluck('id');
            $teamViewer->permissions()->sync($teamViewerPermissions);
        }

        // Project Admin - Permisos de proyecto
        $projectAdmin = Role::where('slug', 'project-admin')->first();
        if ($projectAdmin) {
            $projectAdminPermissions = Permission::whereIn('slug', [
                'projects.view', 'projects.update', 'projects.manage_members', 'projects.manage_settings',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.assign', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update', 'comments.delete',
                'files.upload', 'files.download', 'files.delete',
            ])->pluck('id');
            $projectAdmin->permissions()->sync($projectAdminPermissions);
        }

        // Project Editor - Permisos de edición
        $projectEditor = Role::where('slug', 'project-editor')->first();
        if ($projectEditor) {
            $projectEditorPermissions = Permission::whereIn('slug', [
                'projects.view',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.move',
                'comments.view', 'comments.create', 'comments.update',
                'files.upload', 'files.download',
            ])->pluck('id');
            $projectEditor->permissions()->sync($projectEditorPermissions);
        }

        // Project Viewer - Solo lectura
        $projectViewer = Role::where('slug', 'project-viewer')->first();
        if ($projectViewer) {
            $projectViewerPermissions = Permission::whereIn('slug', [
                'projects.view',
                'tasks.view',
                'comments.view',
                'files.download',
            ])->pluck('id');
            $projectViewer->permissions()->sync($projectViewerPermissions);
        }
    }
}
