<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-800">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div>
                        <h1 class="text-5xl font-bold text-white mb-3 tracking-tight">
                            Crear Equipo
                        </h1>
                        <p class="text-xl text-indigo-100 dark:text-indigo-200">
                            Crea un nuevo equipo para colaborar
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700">
                    <form @submit.prevent="submit">
                        <!-- Nombre -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Nombre del Equipo <span class="text-red-500">*</span>
                            </label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Ej: Equipo de Desarrollo"
                                :error="form.errors.name"
                                class="w-full"
                                required
                                autofocus
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Descripción
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Describe el propósito del equipo..."
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('teams.index')"
                                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Creando...</span>
                                <span v-else>Crear Equipo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    description: '',
    avatar: null,
});

const submit = () => {
    form.post(route('teams.store'), {
        preserveScroll: true,
    });
};
</script>

<style scoped>
.bg-grid-pattern {
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>

