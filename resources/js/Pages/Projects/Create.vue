<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Crear Nuevo Proyecto</h2>

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

                            <!-- Equipo -->
                            <div class="mb-4">
                                <label for="team_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Equipo *
                                </label>
                                <select
                                    id="team_id"
                                    v-model="form.team_id"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.team_id }"
                                >
                                    <option value="">Selecciona un equipo</option>
                                    <option v-for="team in teams" :key="team.id" :value="team.id">
                                        {{ team.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.team_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.team_id }}
                                </p>
                            </div>

                            <!-- Plantilla -->
                            <div class="mb-4">
                                <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Plantilla (opcional)
                                </label>
                                <select
                                    id="template_id"
                                    v-model="form.template_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Sin plantilla</option>
                                    <option v-for="template in templates" :key="template.id" :value="template.id">
                                        {{ template.name }}
                                    </option>
                                </select>
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
                            <div class="grid grid-cols-2 gap-4 mb-6">
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

                            <!-- Botones -->
                            <div class="flex justify-end gap-3">
                                <Link
                                    :href="route('projects.index')"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creando...' : 'Crear Proyecto' }}
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
    teams: Array,
    templates: Array,
});

const form = useForm({
    name: '',
    description: '',
    team_id: '',
    template_id: '',
    color: '#3b82f6',
    icon: '',
    start_date: '',
    due_date: '',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

