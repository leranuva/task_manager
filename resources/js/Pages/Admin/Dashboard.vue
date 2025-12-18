<template>
    <AppLayout title="Panel de Super Administrador">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Panel de Super Administrador
                </h2>
                <div class="flex items-center gap-2 px-4 py-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">
                        Super Admin
                    </span>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Estadísticas Generales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <MetricCard
                    title="Total Usuarios"
                    :value="stats.total_users"
                    icon="users"
                    color="blue"
                    :subtitle="`${stats.super_admins} Super Admins, ${stats.regular_users} Usuarios`"
                />
                <MetricCard
                    title="Equipos"
                    :value="stats.total_teams"
                    icon="user-group"
                    color="green"
                    :subtitle="`${stats.active_teams} Activos`"
                />
                <MetricCard
                    title="Proyectos"
                    :value="stats.total_projects"
                    icon="folder"
                    color="purple"
                    :subtitle="`${stats.active_projects} Activos, ${stats.archived_projects} Archivados`"
                />
                <MetricCard
                    title="Tareas"
                    :value="stats.total_tasks"
                    icon="check-circle"
                    color="orange"
                    :subtitle="`${stats.completed_tasks} Completadas`"
                />
            </div>

            <!-- Gráficos de Crecimiento -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Usuarios por Mes
                    </h3>
                    <LineChart
                        :data="{
                            labels: usersByMonth.map(u => u.month),
                            datasets: [{
                                label: 'Usuarios',
                                data: usersByMonth.map(u => u.count),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            }]
                        }"
                    />
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Equipos por Mes
                    </h3>
                    <LineChart
                        :data="{
                            labels: teamsByMonth.map(t => t.month),
                            datasets: [{
                                label: 'Equipos',
                                data: teamsByMonth.map(t => t.count),
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            }]
                        }"
                    />
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Proyectos por Mes
                    </h3>
                    <LineChart
                        :data="{
                            labels: projectsByMonth.map(p => p.month),
                            datasets: [{
                                label: 'Proyectos',
                                data: projectsByMonth.map(p => p.count),
                                borderColor: 'rgb(168, 85, 247)',
                                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            }]
                        }"
                    />
                </div>
            </div>

            <!-- Tablas de Actividad -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Usuarios Recientes -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Usuarios Recientes
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="user in recentUsers"
                            :key="user.id"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ user.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ user.email }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="user.is_super_admin"
                                    class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded"
                                >
                                    Super Admin
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ formatDate(user.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipos Más Activos -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Equipos Más Activos
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="team in mostActiveTeams"
                            :key="team.id"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-semibold">
                                    {{ team.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ team.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ team.projects_count }} proyectos
                                    </div>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded',
                                    team.is_active
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                        : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                ]"
                            >
                                {{ team.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Proyectos Más Activos -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Proyectos Más Activos
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Proyecto
                                </th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Equipo
                                </th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Tareas
                                </th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="project in mostActiveProjects"
                                :key="project.id"
                                class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ project.name }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-400">
                                    Equipo #{{ project.team_id }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded">
                                        {{ project.tasks_count }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs font-medium rounded',
                                            project.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                        ]"
                                    >
                                        {{ project.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import LineChart from '@/Components/Charts/LineChart.vue';

defineProps({
    stats: Object,
    recentUsers: Array,
    mostActiveTeams: Array,
    mostActiveProjects: Array,
    usersByMonth: Array,
    teamsByMonth: Array,
    projectsByMonth: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

