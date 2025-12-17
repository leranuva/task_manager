<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('projects.show', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver al proyecto
                    </Link>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Tareas del Proyecto</h2>
                            <p class="text-gray-600">{{ project.name }}</p>
                        </div>
                        <Link
                            :href="route('projects.tasks.create', project.id)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                        >
                            Nueva Tarea
                        </Link>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Buscar tareas..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @input="search"
                                />
                            </div>
                            <select
                                v-model="filters.status_id"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="filter"
                            >
                                <option value="">Todos los estados</option>
                                <option v-for="status in statuses" :key="status.id" :value="status.id">
                                    {{ status.name }}
                                </option>
                            </select>
                            <select
                                v-model="filters.assigned_to"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="filter"
                            >
                                <option value="">Todos los usuarios</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <select
                                v-model="filters.priority"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                @change="filter"
                            >
                                <option value="">Todas las prioridades</option>
                                <option value="low">Baja</option>
                                <option value="normal">Normal</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Lista de tareas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="tasks.data.length > 0" class="space-y-3">
                            <div
                                v-for="task in tasks.data"
                                :key="task.id"
                                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                @click="$inertia.visit(route('projects.tasks.show', [project.id, task.id]))"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900">{{ task.title }}</h3>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded"
                                                :style="{
                                                    backgroundColor: task.get_priority_color + '20',
                                                    color: task.get_priority_color
                                                }"
                                            >
                                                {{ getPriorityLabel(task.priority) }}
                                            </span>
                                            <span
                                                v-if="task.has_blocking_dependencies"
                                                class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded"
                                                title="Esta tarea tiene dependencias bloqueantes"
                                            >
                                                ⚠ Bloqueada
                                            </span>
                                        </div>
                                        <p v-if="task.description" class="text-sm text-gray-600 mb-2 line-clamp-2">
                                            {{ task.description }}
                                        </p>
                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span
                                                class="px-2 py-1 rounded text-xs"
                                                :style="{
                                                    backgroundColor: task.status?.color + '20',
                                                    color: task.status?.color
                                                }"
                                            >
                                                {{ task.status?.name }}
                                            </span>
                                            <span v-if="task.assigned_to">
                                                Asignado a: {{ task.assigned_to?.name }}
                                            </span>
                                            <span v-if="task.due_date">
                                                Vence: {{ formatDate(task.due_date) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 mb-4">No se encontraron tareas</p>
                            <Link
                                :href="route('projects.tasks.create', project.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Crear primera tarea
                            </Link>
                        </div>

                        <!-- Paginación -->
                        <div v-if="tasks.links && tasks.links.length > 3" class="mt-6">
                            <div class="flex justify-center">
                                <Link
                                    v-for="link in tasks.links"
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

const props = defineProps({
    project: Object,
    tasks: Object,
    statuses: Array,
    users: Array,
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status_id: '',
            assigned_to: '',
            priority: '',
        }),
    },
});

const filters = ref({ ...props.filters });

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

const search = debounce(() => {
    router.get(route('projects.tasks.index', props.project.id), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const filter = () => {
    router.get(route('projects.tasks.index', props.project.id), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baja',
        normal: 'Normal',
        high: 'Alta',
        urgent: 'Urgente',
    };
    return labels[priority] || priority;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

