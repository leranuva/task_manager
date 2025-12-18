<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-xl font-bold text-gray-900 dark:text-white transition-colors duration-200">
                                Task Manager
                            </Link>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <Link
                                :href="route('teams.index')"
                                :class="[
                                    $page.url.startsWith('/teams') 
                                        ? 'border-indigo-500 text-gray-900 dark:text-white' 
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200'
                                ]"
                            >
                                Equipos
                            </Link>
                            <Link
                                :href="route('projects.index')"
                                :class="[
                                    $page.url.startsWith('/projects') 
                                        ? 'border-blue-500 text-gray-900 dark:text-white' 
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200'
                                ]"
                            >
                                Proyectos
                            </Link>
                            <Link
                                v-if="$page.props.auth.user?.is_super_admin"
                                :href="route('users.index')"
                                :class="[
                                    $page.url.startsWith('/users') 
                                        ? 'border-purple-500 text-gray-900 dark:text-white' 
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200'
                                ]"
                            >
                                Usuarios
                            </Link>
                            <Link
                                v-if="$page.props.auth.user?.is_super_admin"
                                :href="route('admin.dashboard')"
                                :class="[
                                    $page.url.startsWith('/admin') 
                                        ? 'border-red-500 text-gray-900 dark:text-white' 
                                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300',
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200'
                                ]"
                            >
                                Admin
                            </Link>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <!-- Dark Mode Toggle -->
                        <DarkModeToggle class="mr-3" />
                        
                        <!-- Notification Bell -->
                        <div class="relative ml-3">
                            <button @click="toggleNotifications" class="relative p-1 rounded-full text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-200">
                                <span class="sr-only">View notifications</span>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                                    {{ unreadCount }}
                                </span>
                            </button>

                            <div v-if="showNotifications" @click.away="showNotifications = false" class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-700 ring-opacity-5 focus:outline-none z-50 transition-colors duration-200">
                                <div class="block px-4 py-2 text-xs text-gray-400 dark:text-gray-500">
                                    Notificaciones
                                </div>
                                <template v-if="groupedNotifications.length > 0">
                                    <Link
                                        v-for="group in groupedNotifications"
                                        :key="group.latest.id"
                                        :href="getNotificationUrl(group.latest)"
                                        @click="markAsReadAndRedirect(group.latest.id)"
                                        class="flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                    >
                                        <div class="flex-shrink-0 mr-3">
                                            <svg class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 110-6 3 3 0 010 6z"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ group.summary }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatRelativeTime(group.latest.created_at) }}</p>
                                        </div>
                                    </Link>
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    <Link :href="route('notifications.index')" class="block px-4 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors duration-200">
                                        Ver todas las notificaciones
                                    </Link>
                                </template>
                                <p v-else class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">No hay notificaciones nuevas.</p>
                            </div>
                        </div>

                        <div class="flex-shrink-0 ml-4">
                            <span class="text-sm text-gray-700 dark:text-gray-300 mr-4">{{ $page.props.auth.user?.name }}</span>
                            <Link
                                :href="route('profile.edit')"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200"
                            >
                                Perfil
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

const page = usePage();
const unreadCount = ref(page.props.auth.user?.unread_notifications_count || 0);
const showNotifications = ref(false);
const groupedNotifications = ref([]);

const fetchUnreadNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.unread'));
        unreadCount.value = response.data.unread_count;
        groupedNotifications.value = response.data.grouped;
    } catch (error) {
        console.error('Error fetching unread notifications:', error);
    }
};

const toggleNotifications = async () => {
    showNotifications.value = !showNotifications.value;
    if (showNotifications.value) {
        await fetchUnreadNotifications();
    }
};

const markAsReadAndRedirect = async (notificationId) => {
    try {
        await axios.post(route('notifications.mark-read'), { notification_ids: [notificationId] });
        unreadCount.value--;
        groupedNotifications.value = groupedNotifications.value.filter(group => group.latest.id !== notificationId);
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
    showNotifications.value = false;
};

const getNotificationUrl = (notification) => {
    const data = notification.data;
    if (data.type === 'task') {
        return route('projects.tasks.show', [data.project_id, data.task_id]);
    }
    if (data.type === 'comment') {
        return route('projects.tasks.show', [data.project_id, data.subject_id]) + `#comment-${data.comment_id}`;
    }
    if (data.type === 'project') {
        return route('projects.show', data.project_id);
    }
    return '#'; // Fallback
};

const formatRelativeTime = (dateTime) => {
    const date = new Date(dateTime);
    const now = new Date();
    const diffSeconds = Math.floor((now - date) / 1000);

    if (diffSeconds < 60) return `Hace ${diffSeconds}s`;
    const diffMinutes = Math.floor(diffSeconds / 60);
    if (diffMinutes < 60) return `Hace ${diffMinutes}m`;
    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `Hace ${diffHours}h`;
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `Hace ${diffDays}d`;

    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(() => {
    // Listen for new notifications via Echo
    if (window.Echo && page.props.auth.user) {
        window.Echo.private(`users.${page.props.auth.user.id}`)
            .notification((notification) => {
                console.log('New notification received:', notification);
                unreadCount.value++;
                // Re-fetch grouped notifications to update the dropdown
                fetchUnreadNotifications();
            });
    }
});

onUnmounted(() => {
    if (window.Echo && page.props.auth.user) {
        window.Echo.leave(`users.${page.props.auth.user.id}`);
    }
});
</script>
