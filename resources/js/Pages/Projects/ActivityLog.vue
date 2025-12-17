<template>
    <AppLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('projects.show', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver al proyecto
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Historial de Actividad</h2>
                    <p class="text-gray-600">{{ project.name }}</p>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Acción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                            <select
                                v-model="filters.action"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Todas</option>
                                <option value="created">Creado</option>
                                <option value="updated">Actualizado</option>
                                <option value="deleted">Eliminado</option>
                                <option value="moved">Movido</option>
                                <option value="assigned">Asignado</option>
                            </select>
                        </div>

                        <!-- Usuario -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                            <select
                                v-model="filters.user_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Todos</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Tipo de sujeto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select
                                v-model="filters.subject_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Todos</option>
                                <option value="App\Models\Task">Tarea</option>
                                <option value="App\Models\Project">Proyecto</option>
                                <option value="App\Models\Comment">Comentario</option>
                            </select>
                        </div>

                        <!-- Fecha desde -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Fecha hasta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </form>

                    <div class="mt-4 flex gap-2">
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Aplicar Filtros
                        </button>
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>

                <!-- Lista de actividades -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="activity in activities.data"
                            :key="activity.id"
                            class="p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
                                    >
                                        <span class="text-blue-600 font-semibold">
                                            {{ activity.user?.name?.charAt(0).toUpperCase() || '?' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-900">
                                            {{ activity.user?.name || 'Usuario desconocido' }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ getActionLabel(activity.action) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ getSubjectTypeLabel(activity.subject_type) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ activity.description }}
                                    </p>

                                    <!-- Cambios -->
                                    <div v-if="activity.changes && Object.keys(activity.changes).length > 0" class="mt-2">
                                        <details class="text-sm">
                                            <summary class="cursor-pointer text-blue-600 hover:text-blue-800">
                                                Ver cambios
                                            </summary>
                                            <div class="mt-2 pl-4 border-l-2 border-gray-200">
                                                <div
                                                    v-for="(change, field) in activity.changes"
                                                    :key="field"
                                                    class="mb-1"
                                                >
                                                    <span class="font-medium">{{ field }}:</span>
                                                    <span class="text-red-600 line-through">
                                                        {{ change.old }}
                                                    </span>
                                                    →
                                                    <span class="text-green-600">
                                                        {{ change.new }}
                                                    </span>
                                                </div>
                                            </div>
                                        </details>
                                    </div>

                                    <div class="text-xs text-gray-400 mt-2">
                                        {{ formatDate(activity.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="activities.data.length === 0" class="p-8 text-center text-gray-500">
                            No hay actividades registradas
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="activities.links && activities.links.length > 3" class="px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando {{ activities.from }} a {{ activities.to }} de {{ activities.total }} resultados
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in activities.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 rounded-md text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                        !link.url && 'opacity-50 cursor-not-allowed'
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
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
    activities: Object,
    filters: Object,
    users: Array,
});

const filters = ref({
    action: props.filters?.action || '',
    user_id: props.filters?.user_id || '',
    subject_type: props.filters?.subject_type || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get(route('projects.activity.index', props.project.id), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.value = {
        action: '',
        user_id: '',
        subject_type: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

const getActionLabel = (action) => {
    const labels = {
        created: 'creó',
        updated: 'actualizó',
        deleted: 'eliminó',
        moved: 'movió',
        assigned: 'asignó',
    };
    return labels[action] || action;
};

const getSubjectTypeLabel = (type) => {
    const labels = {
        'App\\Models\\Task': 'una tarea',
        'App\\Models\\Project': 'el proyecto',
        'App\\Models\\Comment': 'un comentario',
    };
    return labels[type] || type;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

