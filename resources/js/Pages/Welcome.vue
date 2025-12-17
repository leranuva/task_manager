<template>
    <div class="min-h-screen bg-gray-100">
        <div class="flex items-center justify-center min-h-screen">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Task Manager</h1>
                <p class="text-gray-600 mb-8">Sistema de gestión de proyectos colaborativo</p>
                <Link
                    :href="route('login')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    Iniciar Sesión
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

