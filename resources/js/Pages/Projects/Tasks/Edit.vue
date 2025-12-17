<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link
                        :href="route('projects.tasks.show', [project.id, task.id])"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver a la tarea
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Editar Tarea</h2>
                </div>

                <!-- Alerta de bloqueo -->
                <div
                    v-if="task.has_blocking_dependencies"
                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                Esta tarea tiene dependencias bloqueantes. No podrás cambiar su estado hasta que se completen las tareas dependientes.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Título -->
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                    Título *
                                </label>
                                <div class="relative">
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        required
                                        @input="notifyTyping"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <!-- Indicador de typing -->
                                    <div v-if="typingUsers.length > 0" class="absolute right-2 top-2 text-xs text-gray-500">
                                        <span v-for="(user, index) in typingUsers" :key="user.id">
                                            {{ user.name }}{{ index < typingUsers.length - 1 ? ', ' : '' }}
                                        </span>
                                        {{ typingUsers.length === 1 ? 'está escribiendo...' : 'están escribiendo...' }}
                                    </div>
                                </div>
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Descripción
                                </label>
                                <div class="relative">
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        @input="notifyTyping"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <!-- Indicador de typing -->
                                    <div v-if="typingUsers.length > 0" class="absolute right-2 bottom-2 text-xs text-gray-500 bg-white px-2 py-1 rounded">
                                        <span v-for="(user, index) in typingUsers" :key="user.id">
                                            {{ user.name }}{{ index < typingUsers.length - 1 ? ', ' : '' }}
                                        </span>
                                        {{ typingUsers.length === 1 ? 'está escribiendo...' : 'están escribiendo...' }}
                                    </div>
                                </div>
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Estado y Prioridad -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="status_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Estado *
                                    </label>
                                    <select
                                        id="status_id"
                                        v-model="form.status_id"
                                        required
                                        :disabled="task.has_blocking_dependencies"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        :class="{ 'border-red-500': form.errors.status_id }"
                                    >
                                        <option value="">Selecciona un estado</option>
                                        <option v-for="status in statuses" :key="status.id" :value="status.id">
                                            {{ status.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.status_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.status_id }}
                                    </p>
                                    <p v-if="task.has_blocking_dependencies" class="mt-1 text-sm text-yellow-600">
                                        No se puede cambiar el estado: hay dependencias bloqueantes
                                    </p>
                                </div>
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prioridad *
                                    </label>
                                    <select
                                        id="priority"
                                        v-model="form.priority"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.priority }"
                                    >
                                        <option value="low">Baja</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                    <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.priority }}
                                    </p>
                                </div>
                            </div>

                            <!-- Asignado y Fecha -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                                        Asignado a
                                    </label>
                                    <select
                                        id="assigned_to"
                                        v-model="form.assigned_to"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="">Sin asignar</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                                        Fecha límite
                                    </label>
                                    <input
                                        id="due_date"
                                        v-model="form.due_date"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Estado completado -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_completed"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Tarea completada</span>
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end gap-3">
                                <Link
                                    :href="route('projects.tasks.show', [project.id, task.id])"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { useTypingIndicator } from '@/composables/useTypingIndicator';

const props = defineProps({
    project: Object,
    task: Object,
    statuses: Array,
    users: Array,
    tasks: Array,
});

const form = useForm({
    title: props.task.title,
    description: props.task.description || '',
    status_id: props.task.status_id,
    assigned_to: props.task.assigned_to || '',
    priority: props.task.priority,
    due_date: props.task.due_date || '',
    is_completed: props.task.is_completed,
    version: new Date(props.task.updated_at).getTime(), // Timestamp para conflict resolution
});

// Typing indicators
const { notifyTyping, setupTypingListener, typingUsers } = useTypingIndicator(
    props.project.id,
    'task',
    props.task.id
);

onMounted(() => {
    setupTypingListener();
});

const submit = () => {
    form.put(route('projects.tasks.update', [props.project.id, props.task.id]));
};
</script>

