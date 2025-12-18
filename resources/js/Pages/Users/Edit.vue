<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 dark:from-purple-800 dark:via-indigo-800 dark:to-blue-800">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div>
                        <h1 class="text-5xl font-bold text-white mb-3 tracking-tight">
                            Editar Usuario
                        </h1>
                        <p class="text-xl text-purple-100 dark:text-purple-200">
                            Modifica la información del usuario
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
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Ingresa el nombre completo"
                                :error="form.errors.name"
                                class="w-full"
                                required
                                autofocus
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="usuario@ejemplo.com"
                                :error="form.errors.email"
                                class="w-full"
                                required
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Nueva Contraseña
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-2">(Dejar vacío para mantener la actual)</span>
                            </label>
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Mínimo 8 caracteres"
                                :error="form.errors.password"
                                class="w-full"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Confirmar Nueva Contraseña
                            </label>
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Repite la nueva contraseña"
                                :error="form.errors.password_confirmation"
                                class="w-full"
                            />
                            <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Super Admin -->
                        <div class="mb-6">
                            <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 cursor-pointer">
                                <input
                                    v-model="form.is_super_admin"
                                    type="checkbox"
                                    class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 dark:focus:ring-purple-400 dark:ring-offset-gray-800 focus:ring-2"
                                />
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        Super Administrador
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Acceso completo al sistema. Puede gestionar usuarios, equipos y proyectos.
                                    </div>
                                </div>
                            </label>
                            <p v-if="form.errors.is_super_admin" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.is_super_admin }}
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="route('users.index')"
                                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing">Actualizando...</span>
                                <span v-else>Actualizar Usuario</span>
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

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    is_super_admin: props.user.is_super_admin || false,
});

const submit = () => {
    form.put(route('users.update', props.user.id), {
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

