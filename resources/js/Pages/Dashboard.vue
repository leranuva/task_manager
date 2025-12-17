<template>
    <AppLayout>
        <div class="py-6 transition-colors duration-200">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Dashboard
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Resumen de tus proyectos y tareas
                    </p>
                </div>

                <!-- Métricas principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <MetricCard
                        title="Tareas Pendientes"
                        :value="metrics.pending_tasks"
                        :change="null"
                        icon-color="blue"
                        animation="fade-in"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </template>
                    </MetricCard>

                    <MetricCard
                        title="Tareas Completadas"
                        :value="metrics.completed_tasks"
                        :change="null"
                        icon-color="green"
                        animation="fade-in"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </MetricCard>

                    <MetricCard
                        title="Tareas Vencidas"
                        :value="metrics.overdue_tasks"
                        :change="null"
                        icon-color="red"
                        animation="fade-in"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </MetricCard>

                    <MetricCard
                        title="Proyectos Activos"
                        :value="metrics.active_projects"
                        :change="null"
                        icon-color="purple"
                        animation="fade-in"
                    >
                        <template #icon>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </template>
                    </MetricCard>
                </div>

                <!-- Métricas secundarias -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <MetricCard
                        title="Cumplimiento de Fechas"
                        :value="metrics.on_time_completion_rate"
                        format="percentage"
                        icon-color="green"
                        animation="slide-up"
                    />
                    <MetricCard
                        title="Tareas para Hoy"
                        :value="metrics.tasks_due_today"
                        icon-color="yellow"
                        animation="slide-up"
                    />
                    <MetricCard
                        title="Tareas Esta Semana"
                        :value="metrics.tasks_due_this_week"
                        icon-color="blue"
                        animation="slide-up"
                    />
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Gráfico de tareas completadas -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Tareas Completadas (Últimos 30 días)
                        </h3>
                        <LineChart :data="completedTasksChartData" />
                    </div>

                    <!-- Gráfico de tareas por prioridad -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Tareas por Prioridad
                        </h3>
                        <DoughnutChart :data="priorityChartData" />
                    </div>
                </div>

                <!-- Gráfico de tareas por estado -->
                <div class="mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Tareas por Estado
                        </h3>
                        <BarChart :data="statusChartData" />
                    </div>
                </div>

                <!-- Mis Tareas y Proyectos Recientes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Mis Tareas -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Mis Tareas
                            </h3>
                            <Link
                                :href="route('projects.index')"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                Ver todas
                            </Link>
                        </div>
                        <div v-if="myTasks.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No tienes tareas asignadas
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="task in myTasks"
                                :key="task.id"
                                class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                            >
                                <Link
                                    :href="route('projects.tasks.show', [task.project_id, task.id])"
                                    class="block"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ task.title }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ task.project?.name }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <span
                                                v-if="task.due_date"
                                                class="text-xs px-2 py-1 rounded"
                                                :class="[
                                                    isOverdue(task.due_date)
                                                        ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ formatDate(task.due_date) }}
                                            </span>
                                            <span
                                                class="text-xs px-2 py-1 rounded"
                                                :class="getPriorityClass(task.priority)"
                                            >
                                                {{ getPriorityLabel(task.priority) }}
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Proyectos Recientes -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Proyectos Recientes
                            </h3>
                            <Link
                                :href="route('projects.index')"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                Ver todos
                            </Link>
                        </div>
                        <div v-if="recentProjects.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            No tienes proyectos
                        </div>
                        <div v-else class="space-y-3">
                            <Link
                                v-for="project in recentProjects"
                                :key="project.id"
                                :href="route('projects.show', project.id)"
                                class="block p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                                            :style="{ backgroundColor: project.color || '#6366f1' }"
                                        >
                                            {{ project.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ project.name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatRelativeTime(project.updated_at) }}
                                            </p>
                                        </div>
                                    </div>
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';

const props = defineProps({
    metrics: Object,
    tasksByPriority: Object,
    tasksByStatus: Object,
    completedTasksByDay: Object,
    projectsByStatus: Object,
    myTasks: Array,
    recentProjects: Array,
});

// Datos para gráfico de tareas completadas
const completedTasksChartData = computed(() => {
    const labels = [];
    const data = [];
    
    // Generar últimos 30 días
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        labels.push(date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }));
        data.push(props.completedTasksByDay[dateStr] || 0);
    }

    return {
        labels,
        datasets: [
            {
                label: 'Tareas Completadas',
                data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
            },
        ],
    };
});

// Datos para gráfico de prioridades
const priorityChartData = computed(() => {
    const priorityLabels = {
        low: 'Baja',
        normal: 'Normal',
        high: 'Alta',
        urgent: 'Urgente',
    };

    const labels = [];
    const data = [];
    const colors = [
        'rgba(34, 197, 94, 0.8)',
        'rgba(59, 130, 246, 0.8)',
        'rgba(251, 191, 36, 0.8)',
        'rgba(239, 68, 68, 0.8)',
    ];

    Object.entries(props.tasksByPriority).forEach(([priority, count], index) => {
        labels.push(priorityLabels[priority] || priority);
        data.push(count);
    });

    return {
        labels,
        datasets: [
            {
                data,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: '#fff',
            },
        ],
    };
});

// Datos para gráfico de estados
const statusChartData = computed(() => {
    const labels = Object.keys(props.tasksByStatus);
    const data = Object.values(props.tasksByStatus);

    return {
        labels,
        datasets: [
            {
                label: 'Tareas',
                data,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
            },
        ],
    };
});

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baja',
        normal: 'Normal',
        high: 'Alta',
        urgent: 'Urgente',
    };
    return labels[priority] || priority;
};

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        normal: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        high: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        urgent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[priority] || classes.normal;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        month: 'short',
        day: 'numeric',
    });
};

const formatRelativeTime = (dateTime) => {
    const date = new Date(dateTime);
    const now = new Date();
    const diff = now - date;
    const days = Math.floor(diff / 86400000);

    if (days === 0) return 'Hoy';
    if (days === 1) return 'Ayer';
    if (days < 7) return `Hace ${days} días`;
    if (days < 30) return `Hace ${Math.floor(days / 7)} semanas`;
    return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
};

const isOverdue = (dueDate) => {
    return new Date(dueDate) < new Date() && !new Date(dueDate).toDateString() === new Date().toDateString();
};
</script>
