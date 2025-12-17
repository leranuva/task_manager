<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link
                        :href="route('projects.statuses.index', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver a estados
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Editar Estado</h2>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Nombre -->
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre del Estado *
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

                            <!-- Color -->
                            <div class="mb-4">
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                    Color *
                                </label>
                                <div class="flex gap-4">
                                    <input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        required
                                        class="h-10 w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <input
                                        v-model="form.color"
                                        type="text"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="#3b82f6"
                                    />
                                </div>
                                <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.color }}
                                </p>
                            </div>

                            <!-- Opciones -->
                            <div class="mb-6 space-y-2">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_default"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Estado por defecto</span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_final"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Estado final (completado)</span>
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end gap-3">
                                <Link
                                    :href="route('projects.statuses.index', project.id)"
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
    status: Object,
});

const form = useForm({
    name: props.status.name,
    color: props.status.color,
    is_default: props.status.is_default,
    is_final: props.status.is_final,
});

const submit = () => {
    form.put(route('projects.statuses.update', [props.project.id, props.status.id]));
};
</script>

