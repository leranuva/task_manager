<template>
    <AppLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Notificaciones</h2>
                        <p class="text-gray-600">Gestiona tus notificaciones</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="markAllAsRead"
                            :disabled="markingAll"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                        >
                            {{ markingAll ? 'Marcando...' : 'Marcar todas como leídas' }}
                        </button>
                        <Link
                            :href="route('notifications.preferences')"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
                        >
                            Preferencias
                        </Link>
                    </div>
                </div>

                <!-- Notificaciones agrupadas -->
                <div v-if="grouped.length > 0" class="space-y-4 mb-6">
                    <div
                        v-for="group in grouped"
                        :key="group.latest.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                            {{ group.count }} {{ group.count === 1 ? 'notificación' : 'notificaciones' }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ formatDate(group.latest.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 mb-2">
                                        {{ group.summary }}
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <span v-for="(user, index) in group.users.slice(0, 3)" :key="user">
                                            {{ user }}{{ index < Math.min(group.users.length, 3) - 1 ? ', ' : '' }}
                                        </span>
                                        <span v-if="group.users.length > 3">
                                            y {{ group.users.length - 3 }} más
                                        </span>
                                    </div>
                                </div>
                                <button
                                    @click="markGroupAsRead(group)"
                                    class="ml-4 text-blue-600 hover:text-blue-800 text-sm"
                                >
                                    Marcar como leída
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de notificaciones individuales -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="notification in notifications.data"
                            :key="notification.id"
                            class="p-4 hover:bg-gray-50 transition-colors"
                            :class="{ 'bg-blue-50': !notification.read_at }"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Icono -->
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center"
                                        :class="getNotificationIconClass(notification.type)"
                                    >
                                        <span class="text-white text-lg">
                                            {{ getNotificationIcon(notification.type) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-900">
                                            {{ notification.data.user_name || 'Usuario' }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ getNotificationText(notification) }}
                                        </span>
                                    </div>

                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ getNotificationDescription(notification) }}
                                    </p>

                                    <div class="flex items-center gap-4 text-xs text-gray-400">
                                        <span>{{ formatDate(notification.created_at) }}</span>
                                        <Link
                                            v-if="getNotificationUrl(notification)"
                                            :href="getNotificationUrl(notification)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            Ver →
                                        </Link>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="flex-shrink-0">
                                    <button
                                        @click="markAsRead(notification.id)"
                                        v-if="!notification.read_at"
                                        class="text-gray-400 hover:text-gray-600"
                                        title="Marcar como leída"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="notifications.data.length === 0" class="p-8 text-center text-gray-500">
                            No hay notificaciones
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="notifications.links && notifications.links.length > 3" class="px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando {{ notifications.from }} a {{ notifications.to }} de {{ notifications.total }} resultados
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in notifications.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 rounded-md text-sm',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
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
    notifications: Object,
    grouped: Array,
});

const markingAll = ref(false);

const markAsRead = async (notificationId) => {
    try {
        await axios.post(route('notifications.mark-read'), {
            notification_ids: [notificationId],
        });
        router.reload({ only: ['notifications', 'grouped'] });
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markGroupAsRead = async (group) => {
    const ids = group.notifications.map(n => n.id);
    try {
        await axios.post(route('notifications.mark-read'), {
            notification_ids: ids,
        });
        router.reload({ only: ['notifications', 'grouped'] });
    } catch (error) {
        console.error('Error marking group as read:', error);
    }
};

const markAllAsRead = async () => {
    markingAll.value = true;
    try {
        await axios.post(route('notifications.mark-read'));
        router.reload({ only: ['notifications', 'grouped'] });
    } catch (error) {
        console.error('Error marking all as read:', error);
    } finally {
        markingAll.value = false;
    }
};

const getNotificationIcon = (type) => {
    if (type.includes('TaskNotification')) return '📋';
    if (type.includes('CommentNotification')) return '💬';
    if (type.includes('ProjectNotification')) return '📁';
    return '🔔';
};

const getNotificationIconClass = (type) => {
    if (type.includes('TaskNotification')) return 'bg-blue-500';
    if (type.includes('CommentNotification')) return 'bg-green-500';
    if (type.includes('ProjectNotification')) return 'bg-purple-500';
    return 'bg-gray-500';
};

const getNotificationText = (notification) => {
    const data = notification.data || {};
    const action = data.action || 'unknown';

    if (notification.type.includes('TaskNotification')) {
        const actions = {
            created: 'creó una tarea',
            updated: 'actualizó una tarea',
            deleted: 'eliminó una tarea',
            assigned: 'te asignó una tarea',
            moved: 'movió una tarea',
        };
        return actions[action] || 'realizó una acción en una tarea';
    }

    if (notification.type.includes('CommentNotification')) {
        return data.is_mention ? 'te mencionó en un comentario' : 'comentó';
    }

    if (notification.type.includes('ProjectNotification')) {
        const actions = {
            updated: 'actualizó el proyecto',
            member_added: 'te agregó al proyecto',
            member_removed: 'te removió del proyecto',
        };
        return actions[action] || 'realizó una acción en el proyecto';
    }

    return 'realizó una acción';
};

const getNotificationDescription = (notification) => {
    const data = notification.data || {};

    if (notification.type.includes('TaskNotification')) {
        return `Tarea: ${data.task_title || 'Sin título'} - Proyecto: ${data.project_name || 'Sin proyecto'}`;
    }

    if (notification.type.includes('CommentNotification')) {
        return `Comentario: ${data.comment_body || 'Sin contenido'}...`;
    }

    if (notification.type.includes('ProjectNotification')) {
        return `Proyecto: ${data.project_name || 'Sin proyecto'}`;
    }

    return 'Notificación';
};

const getNotificationUrl = (notification) => {
    const data = notification.data || {};

    if (notification.type.includes('TaskNotification')) {
        return route('projects.tasks.show', [data.project_id, data.task_id]);
    }

    if (notification.type.includes('CommentNotification')) {
        if (data.subject_type && data.subject_id) {
            // Intentar determinar la URL basada en el tipo de sujeto
            return null; // Se puede mejorar
        }
    }

    if (notification.type.includes('ProjectNotification')) {
        return route('projects.show', data.project_id);
    }

    return null;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

