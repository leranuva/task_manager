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
                            <h2 class="text-2xl font-bold text-gray-900">Estados del Proyecto</h2>
                            <p class="text-gray-600">{{ project.name }}</p>
                        </div>
                        <Link
                            :href="route('projects.statuses.create', project.id)"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                        >
                            Nuevo Estado
                        </Link>
                    </div>
                </div>

                <!-- Estados con drag & drop -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            v-if="statuses.length > 0"
                            class="space-y-3"
                            ref="statusList"
                        >
                            <div
                                v-for="(status, index) in statuses"
                                :key="status.id"
                                :data-id="status.id"
                                :data-position="status.position"
                                class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-move transition-all"
                                :class="{ 'opacity-50': isDragging && draggedId === status.id }"
                                draggable="true"
                                @dragstart="handleDragStart($event, status)"
                                @dragover.prevent="handleDragOver($event, status)"
                                @drop="handleDrop($event, status)"
                                @dragend="handleDragEnd"
                            >
                                <div class="flex-shrink-0 cursor-move">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                                <div
                                    class="w-4 h-4 rounded-full"
                                    :style="{ backgroundColor: status.color }"
                                />
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ status.name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ status.tasks_count || 0 }} tareas
                                        <span v-if="status.is_default" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">
                                            Por defecto
                                        </span>
                                        <span v-if="status.is_final" class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">
                                            Final
                                        </span>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('projects.statuses.edit', [project.id, status.id])"
                                        class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="deleteStatus(status)"
                                        :disabled="status.tasks_count > 0"
                                        class="px-3 py-1 text-sm text-red-600 hover:text-red-800 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :title="status.tasks_count > 0 ? 'No se puede eliminar: tiene tareas asignadas' : 'Eliminar'"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 mb-4">No hay estados configurados</p>
                            <Link
                                :href="route('projects.statuses.create', project.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Crear primer estado
                            </Link>
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
import axios from 'axios';

const props = defineProps({
    project: Object,
    statuses: Array,
});

const statusList = ref(null);
const isDragging = ref(false);
const draggedId = ref(null);
const draggedOverId = ref(null);

const handleDragStart = (event, status) => {
    isDragging.value = true;
    draggedId.value = status.id;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', event.target);
};

const handleDragOver = (event, status) => {
    event.preventDefault();
    draggedOverId.value = status.id;
};

const handleDrop = async (event, targetStatus) => {
    event.preventDefault();
    
    if (!draggedId.value || draggedId.value === targetStatus.id) {
        return;
    }

    const draggedStatus = props.statuses.find(s => s.id === draggedId.value);
    if (!draggedStatus) return;

    // Reordenar localmente
    const newStatuses = [...props.statuses];
    const draggedIndex = newStatuses.findIndex(s => s.id === draggedId.value);
    const targetIndex = newStatuses.findIndex(s => s.id === targetStatus.id);

    const [removed] = newStatuses.splice(draggedIndex, 1);
    newStatuses.splice(targetIndex, 0, removed);

    // Actualizar posiciones
    const statusesToUpdate = newStatuses.map((status, index) => ({
        id: status.id,
        position: index,
    }));

    // Enviar al servidor
    try {
        await axios.post(route('projects.statuses.reorder', props.project.id), {
            statuses: statusesToUpdate,
        });
        
        router.reload({ only: ['statuses'] });
    } catch (error) {
        console.error('Error al reordenar:', error);
    }
};

const handleDragEnd = () => {
    isDragging.value = false;
    draggedId.value = null;
    draggedOverId.value = null;
};

const deleteStatus = (status) => {
    if (status.tasks_count > 0) {
        alert('No se puede eliminar un estado que tiene tareas asignadas.');
        return;
    }

    if (confirm('¿Estás seguro de que deseas eliminar este estado?')) {
        router.delete(route('projects.statuses.destroy', [props.project.id, status.id]));
    }
};
</script>

