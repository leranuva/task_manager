<template>
    <AppLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('notifications.index')"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver a notificaciones
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Preferencias de Notificaciones</h2>
                    <p class="text-gray-600">Configura cómo y cuándo recibir notificaciones</p>
                </div>

                <!-- Formulario de preferencias -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <form @submit.prevent="submit">
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="(label, type) in types"
                                :key="type"
                                class="p-6"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                                            {{ label }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mb-4">
                                            Selecciona cómo quieres recibir este tipo de notificación
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <!-- Toggle enabled -->
                                        <label class="flex items-center">
                                            <input
                                                v-model="form.preferences[type].enabled"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">Habilitado</span>
                                        </label>

                                        <!-- Canal -->
                                        <select
                                            v-model="form.preferences[type].channel"
                                            :disabled="!form.preferences[type].enabled"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        >
                                            <option value="in_app">Solo en la app</option>
                                            <option value="email">Solo por email</option>
                                            <option value="both">Ambos</option>
                                            <option value="none">Ninguno</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                            <Link
                                :href="route('notifications.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Guardando...' : 'Guardar Preferencias' }}
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

const props = defineProps({
    preferences: Object,
    types: Object,
});

// Inicializar formulario con preferencias existentes o valores por defecto
const defaultPreferences = {};
Object.keys(props.types).forEach(type => {
    const existing = props.preferences[type];
    defaultPreferences[type] = {
        type: type,
        channel: existing?.channel || 'both',
        enabled: existing?.enabled !== false,
    };
});

const form = useForm({
    preferences: defaultPreferences,
});

const submit = () => {
    form.put(route('notifications.preferences.update'));
};
</script>

