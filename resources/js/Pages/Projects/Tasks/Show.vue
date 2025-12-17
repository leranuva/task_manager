<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link
                        :href="route('projects.tasks.index', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver a tareas
                    </Link>
                </div>

                <!-- Alerta de bloqueo -->
                <div
                    v-if="task.has_blocking_dependencies && blockingTasks.length > 0"
                    class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Esta tarea está bloqueada
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Las siguientes tareas deben completarse antes de poder mover esta tarea:</p>
                                <ul class="list-disc list-inside mt-1">
                                    <li v-for="blockingTask in blockingTasks" :key="blockingTask.id">
                                        {{ blockingTask.title }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la tarea -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ task.title }}</h1>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="px-3 py-1 text-sm font-medium rounded"
                                        :style="{
                                            backgroundColor: task.status?.color + '20',
                                            color: task.status?.color
                                        }"
                                    >
                                        {{ task.status?.name }}
                                    </span>
                                    <span
                                        class="px-3 py-1 text-sm font-medium rounded"
                                        :style="{
                                            backgroundColor: task.get_priority_color + '20',
                                            color: task.get_priority_color
                                        }"
                                    >
                                        {{ getPriorityLabel(task.priority) }}
                                    </span>
                                </div>
                            </div>
                            <Link
                                :href="route('projects.tasks.edit', [project.id, task.id])"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Editar
                            </Link>
                        </div>

                        <div v-if="task.description" class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Descripción</h3>
                            <p class="text-gray-600 whitespace-pre-wrap">{{ task.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Asignado a</p>
                                <p class="text-sm text-gray-900">{{ task.assigned_to?.name || 'Sin asignar' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Creado por</p>
                                <p class="text-sm text-gray-900">{{ task.created_by?.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Fecha límite</p>
                                <p class="text-sm text-gray-900">{{ task.due_date ? formatDate(task.due_date) : 'Sin fecha' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Estado</p>
                                <p class="text-sm text-gray-900">{{ task.is_completed ? 'Completada' : 'Pendiente' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archivos Adjuntos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">Archivos Adjuntos</h2>
                        </div>
                        
                        <!-- Uploader -->
                        <FileUploader
                            :attachable-type="'App\\Models\\Task'"
                            :attachable-id="task.id"
                            :project-id="project.id"
                            :multiple="true"
                            @uploaded="handleFileUploaded"
                            @error="handleFileError"
                        />
                        
                        <!-- Lista de archivos -->
                        <div class="mt-4">
                            <FileList
                                :files="task.attachments || []"
                                :project-id="project.id"
                                @deleted="handleFileDeleted"
                            />
                        </div>
                    </div>
                </div>

                <!-- Dependencias -->
                <div v-if="task.dependencies && task.dependencies.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Dependencias</h2>
                        <div class="space-y-2">
                            <div
                                v-for="dependency in task.dependencies"
                                :key="dependency.id"
                                class="p-3 border border-gray-200 rounded-lg"
                            >
                                <Link
                                    :href="route('projects.tasks.show', [project.id, dependency.depends_on?.id])"
                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    {{ dependency.depends_on?.title }}
                                </Link>
                                <p class="text-sm text-gray-500 mt-1">
                                    Tipo: {{ getDependencyTypeLabel(dependency.type) }}
                                    <span v-if="!dependency.depends_on?.is_completed" class="ml-2 text-red-600">
                                        (Pendiente)
                                    </span>
                                    <span v-else class="ml-2 text-green-600">
                                        (Completada)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Historial de Movimientos</h2>
                <div v-if="loadingMovements" class="text-gray-500 text-sm">
                    Cargando historial...
                </div>
                <div v-else-if="movements.length === 0" class="text-gray-500 text-sm">
                    No hay movimientos registrados para esta tarea.
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="movement in movements"
                        :key="movement.id"
                        class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg"
                    >
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 text-xs font-semibold">
                                {{ movement.user?.name?.charAt(0).toUpperCase() || '?' }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">{{ movement.user?.name || 'Usuario' }}</span>
                                <span v-if="movement.from_status_id">
                                    movió la tarea de
                                    <span class="font-medium">{{ movement.from_status?.name || 'Estado anterior' }}</span>
                                    a
                                    <span class="font-medium">{{ movement.to_status?.name || 'Estado nuevo' }}</span>
                                </span>
                                <span v-else>
                                    creó la tarea en
                                    <span class="font-medium">{{ movement.to_status?.name || 'Estado inicial' }}</span>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ formatDateTime(movement.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import FileUploader from '@/Components/FileUploader.vue';
import FileList from '@/Components/FileList.vue';

const props = defineProps({
    project: Object,
    task: Object,
    blockingTasks: Array,
});

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baja',
        normal: 'Normal',
        high: 'Alta',
        urgent: 'Urgente',
    };
    return labels[priority] || priority;
};

const getDependencyTypeLabel = (type) => {
    const labels = {
        blocks: 'Bloquea',
        relates_to: 'Relacionado con',
        duplicates: 'Duplica',
    };
    return labels[type] || type;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateTime) => {
    const date = new Date(dateTime);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Hace unos segundos';
    if (minutes < 60) return `Hace ${minutes} ${minutes === 1 ? 'minuto' : 'minutos'}`;
    if (hours < 24) return `Hace ${hours} ${hours === 1 ? 'hora' : 'horas'}`;
    if (days < 7) return `Hace ${days} ${days === 1 ? 'día' : 'días'}`;

    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const handleFileUploaded = (attachment) => {
    router.reload({ only: ['task'] });
};

const handleFileError = (error) => {
    alert(error);
};

const handleFileDeleted = (fileId) => {
    // El componente FileList ya recarga la página
};
</script>

