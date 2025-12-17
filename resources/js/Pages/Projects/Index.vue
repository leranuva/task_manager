<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Proyectos</h2>
                            <Link
                                :href="route('projects.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Nuevo Proyecto
                            </Link>
                        </div>

                        <!-- Filtros -->
                        <div class="mb-6 flex gap-4">
                            <div class="flex-1">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Buscar proyectos..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @input="search"
                                />
                            </div>
                            <select
                                v-model="filters.team_id"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="filter"
                            >
                                <option value="">Todos los equipos</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <select
                                v-model="filters.status"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="filter"
                            >
                                <option value="">Todos</option>
                                <option value="active">Activos</option>
                                <option value="archived">Archivados</option>
                                <option value="inactive">Inactivos</option>
                            </select>
                        </div>

                        <!-- Grid de proyectos -->
                        <div v-if="projects.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="project in projects.data"
                                :key="project.id"
                                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                @click="$inertia.visit(route('projects.show', project.id))"
                            >
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                v-if="project.color"
                                                class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                                                :style="{ backgroundColor: project.color }"
                                            >
                                                {{ project.icon || project.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    {{ project.name }}
                                                </h3>
                                                <p class="text-sm text-gray-500">
                                                    {{ project.team?.name }}
                                                </p>
                                            </div>
                                        </div>
                                        <span
                                            v-if="project.is_archived"
                                            class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded"
                                        >
                                            Archivado
                                        </span>
                                    </div>
                                    
                                    <p v-if="project.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {{ project.description }}
                                    </p>

                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <span>{{ project.tasks_count || 0 }} tareas</span>
                                        <span>{{ project.task_statuses_count || 0 }} estados</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 mb-4">No se encontraron proyectos</p>
                            <Link
                                :href="route('projects.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Crear primer proyecto
                            </Link>
                        </div>

                        <!-- Paginación -->
                        <div v-if="projects.links && projects.links.length > 3" class="mt-6">
                            <div class="flex justify-center">
                                <Link
                                    v-for="link in projects.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-50',
                                        'px-4 py-2 border border-gray-300 rounded-md text-sm font-medium mx-1'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';

// Simple debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

const props = defineProps({
    projects: Object,
    teams: Array,
    filters: {
        type: Object,
        default: () => ({
            search: '',
            team_id: '',
            status: '',
        }),
    },
});

const filters = ref({ ...props.filters });

const search = debounce(() => {
    router.get(route('projects.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const filter = () => {
    router.get(route('projects.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

