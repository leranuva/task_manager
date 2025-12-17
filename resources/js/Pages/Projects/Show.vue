<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div
                                    v-if="project.color"
                                    class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-semibold text-lg"
                                    :style="{ backgroundColor: project.color }"
                                >
                                    {{ project.icon || project.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900">{{ project.name }}</h1>
                                    <p class="text-sm text-gray-500">
                                        {{ project.team?.name }} • Creado por {{ project.owner?.name }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('projects.kanban', project.id)"
                                    class="px-4 py-2 bg-blue-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-blue-700"
                                >
                                    Tablero Kanban
                                </Link>
                                <Link
                                    :href="route('projects.gallery', project.id)"
                                    class="px-4 py-2 bg-purple-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-purple-700"
                                >
                                    Galería
                                </Link>
                                <Link
                                    :href="route('projects.activity.index', project.id)"
                                    class="px-4 py-2 bg-gray-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-gray-700"
                                >
                                    Historial de Actividad
                                </Link>
                                <Link
                                    :href="route('projects.statuses.index', project.id)"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Gestionar Estados
                                </Link>
                                <Link
                                    :href="route('projects.edit', project.id)"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Editar
                                </Link>
                            </div>
                        </div>
                        <p v-if="project.description" class="mt-4 text-gray-600">
                            {{ project.description }}
                        </p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Tareas</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_tasks }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Completadas</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.completed_tasks }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pendientes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_tasks }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Miembros</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_members }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tareas por Estado -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Tareas por Estado</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <div
                                v-for="status in tasksByStatus"
                                :key="status.name"
                                class="p-4 border border-gray-200 rounded-lg"
                            >
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-4 h-4 rounded-full"
                                        :style="{ backgroundColor: status.color }"
                                    />
                                    <h3 class="font-semibold text-gray-900">{{ status.name }}</h3>
                                </div>
                                <p class="text-2xl font-bold text-gray-900">{{ status.count }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Miembros del Proyecto -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Miembros del Proyecto</h2>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold">
                                        {{ project.owner?.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ project.owner?.name }}</p>
                                    <p class="text-sm text-gray-500">{{ project.owner?.email }}</p>
                                </div>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    Owner
                                </span>
                            </div>
                            <div
                                v-for="user in project.users"
                                :key="user.id"
                                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg"
                            >
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-600 font-semibold">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ user.name }}</p>
                                    <p class="text-sm text-gray-500">{{ user.email }}</p>
                                </div>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                    {{ user.pivot.role || 'Member' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estados -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Estados del Proyecto</h2>
                        <div class="flex gap-2 flex-wrap">
                            <div
                                v-for="status in project.task_statuses"
                                :key="status.id"
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :style="{ backgroundColor: status.color + '20', color: status.color }"
                            >
                                {{ status.name }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tareas recientes -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Tareas Recientes</h2>
                        <div v-if="project.tasks && project.tasks.length > 0" class="space-y-3">
                            <div
                                v-for="task in project.tasks"
                                :key="task.id"
                                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">{{ task.title }}</h3>
                                        <p v-if="task.description" class="text-sm text-gray-600 mt-1">
                                            {{ task.description }}
                                        </p>
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                            <span v-if="task.status">
                                                Estado: {{ task.status.name }}
                                            </span>
                                            <span v-if="task.assigned_to">
                                                Asignado a: {{ task.assigned_to.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500">No hay tareas en este proyecto</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';

defineProps({
    project: Object,
    stats: Object,
    tasksByStatus: Array,
});
</script>

