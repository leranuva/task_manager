<?php

namespace Database\Seeders;

use App\Models\ProjectTemplate;
use Illuminate\Database\Seeder;

class ProjectTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Proyecto Básico',
                'description' => 'Plantilla simple con estados básicos para cualquier tipo de proyecto',
                'icon' => '📋',
                'color' => '#3b82f6',
                'is_system' => true,
                'is_active' => true,
                'default_statuses' => [
                    ['name' => 'Por hacer', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
                    ['name' => 'En progreso', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Completado', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
                ],
                'default_settings' => [
                    'enable_comments' => true,
                    'enable_attachments' => true,
                    'enable_time_tracking' => false,
                ],
            ],
            [
                'name' => 'Desarrollo de Software',
                'description' => 'Plantilla optimizada para proyectos de desarrollo de software',
                'icon' => '💻',
                'color' => '#8b5cf6',
                'is_system' => true,
                'is_active' => true,
                'default_statuses' => [
                    ['name' => 'Backlog', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
                    ['name' => 'En desarrollo', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
                    ['name' => 'En revisión', 'color' => '#f59e0b', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Testing', 'color' => '#ec4899', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Completado', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
                ],
                'default_settings' => [
                    'enable_comments' => true,
                    'enable_attachments' => true,
                    'enable_time_tracking' => true,
                    'enable_code_review' => true,
                ],
            ],
            [
                'name' => 'Marketing',
                'description' => 'Plantilla para proyectos de marketing y campañas',
                'icon' => '📢',
                'color' => '#ef4444',
                'is_system' => true,
                'is_active' => true,
                'default_statuses' => [
                    ['name' => 'Planificación', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
                    ['name' => 'En ejecución', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
                    ['name' => 'En revisión', 'color' => '#f59e0b', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Publicado', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
                ],
                'default_settings' => [
                    'enable_comments' => true,
                    'enable_attachments' => true,
                    'enable_time_tracking' => false,
                    'enable_analytics' => true,
                ],
            ],
            [
                'name' => 'Diseño',
                'description' => 'Plantilla para proyectos de diseño gráfico y UX/UI',
                'icon' => '🎨',
                'color' => '#f97316',
                'is_system' => true,
                'is_active' => true,
                'default_statuses' => [
                    ['name' => 'Briefing', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
                    ['name' => 'Diseño inicial', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Revisión cliente', 'color' => '#f59e0b', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Ajustes', 'color' => '#ec4899', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Aprobado', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
                ],
                'default_settings' => [
                    'enable_comments' => true,
                    'enable_attachments' => true,
                    'enable_time_tracking' => true,
                    'enable_version_control' => true,
                ],
            ],
            [
                'name' => 'Soporte Técnico',
                'description' => 'Plantilla para gestión de tickets y soporte técnico',
                'icon' => '🎫',
                'color' => '#06b6d4',
                'is_system' => true,
                'is_active' => true,
                'default_statuses' => [
                    ['name' => 'Nuevo', 'color' => '#6b7280', 'is_default' => true, 'is_final' => false],
                    ['name' => 'En investigación', 'color' => '#3b82f6', 'is_default' => false, 'is_final' => false],
                    ['name' => 'En espera', 'color' => '#f59e0b', 'is_default' => false, 'is_final' => false],
                    ['name' => 'Resuelto', 'color' => '#10b981', 'is_default' => false, 'is_final' => true],
                    ['name' => 'Cerrado', 'color' => '#374151', 'is_default' => false, 'is_final' => true],
                ],
                'default_settings' => [
                    'enable_comments' => true,
                    'enable_attachments' => true,
                    'enable_time_tracking' => true,
                    'enable_priority' => true,
                ],
            ],
        ];

        foreach ($templates as $template) {
            ProjectTemplate::updateOrCreate(
                ['name' => $template['name'], 'is_system' => true],
                $template
            );
        }
    }
}
