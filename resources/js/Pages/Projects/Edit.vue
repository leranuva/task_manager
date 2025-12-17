<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar Proyecto</h2>

                        <form @submit.prevent="submit">
                            <!-- Nombre -->
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Proyecto *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Descripción
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.description }"
                                />
                                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Color e Icono -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                        Color
                                    </label>
                                    <input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">
                                        Icono
                                    </label>
                                    <input
                                        id="icon"
                                        v-model="form.icon"
                                        type="text"
                                        placeholder="📋"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                        Fecha de inicio
                                    </label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
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

                            <!-- Estado -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Proyecto activo</span>
                                </label>
                                <label class="flex items-center mt-2">
                                    <input
                                        v-model="form.is_archived"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Archivado</span>
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end gap-3">
                                <Link
                                    :href="route('projects.show', project.id)"
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
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';

const props = defineProps({
    project: Object,
    teams: Array,
});

const form = useForm({
    name: props.project.name,
    description: props.project.description || '',
    color: props.project.color || '#3b82f6',
    icon: props.project.icon || '',
    start_date: props.project.start_date || '',
    due_date: props.project.due_date || '',
    is_active: props.project.is_active,
    is_archived: props.project.is_archived,
});

const submit = () => {
    form.put(route('projects.update', props.project.id));
};
</script>

