<template>
    <AppLayout>
        <div class="py-6">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('projects.show', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver al proyecto
                    </Link>
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900">Tablero Kanban</h2>
                            <p class="text-gray-600">{{ project.name }}</p>
                            <!-- Usuarios conectados -->
                            <div v-if="connectedUsers.length > 0" class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-gray-500">Usuarios conectados:</span>
                                <div class="flex -space-x-2">
                                    <div
                                        v-for="user in connectedUsers"
                                        :key="user.id"
                                        class="w-6 h-6 bg-blue-100 rounded-full border-2 border-white flex items-center justify-center"
                                        :title="user.name"
                                    >
                                        <span class="text-blue-600 text-xs font-semibold">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">({{ connectedUsers.length }})</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="toggleViewMode"
                                class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                :title="viewMode === 'compact' ? 'Vista expandida' : 'Vista compacta'"
                            >
                                {{ viewMode === 'compact' ? '📖' : '📄' }}
                            </button>
                            <Link
                                :href="route('projects.tasks.create', project.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Nueva Tarea
                            </Link>
                        </div>
                    </div>

                    <!-- Filtros y Búsqueda -->
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Búsqueda -->
                            <div class="md:col-span-2">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Buscar tareas..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @input="applyFilters"
                                />
                            </div>
                            <!-- Filtro por usuario -->
                            <div>
                                <select
                                    v-model="filters.assigned_to"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="applyFilters"
                                >
                                    <option value="">Todos los usuarios</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                            <!-- Filtro por prioridad -->
                            <div>
                                <select
                                    v-model="filters.priority"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="applyFilters"
                                >
                                    <option value="">Todas las prioridades</option>
                                    <option value="low">Baja</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                            <!-- Filtro por fecha -->
                            <div>
                                <select
                                    v-model="filters.date_filter"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="applyFilters"
                                >
                                    <option value="">Todas las fechas</option>
                                    <option value="today">Hoy</option>
                                    <option value="this_week">Esta semana</option>
                                    <option value="overdue">Vencidas</option>
                                    <option value="no_date">Sin fecha</option>
                                </select>
                            </div>
                        </div>
                        <!-- Botón limpiar filtros -->
                        <div v-if="hasActiveFilters" class="mt-3">
                            <button
                                @click="clearFilters"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tablero Kanban -->
                <div class="overflow-x-auto relative">
                    <!-- Cursor indicators -->
                    <div class="fixed pointer-events-none z-50">
                        <div
                            v-for="(positions, key) in cursorPositions"
                            :key="key"
                        >
                            <div
                                v-for="(cursorData, userId) in positions"
                                :key="userId"
                                class="absolute"
                                :style="{
                                    left: cursorData.position.x + 'px',
                                    top: cursorData.position.y + 'px',
                                    transform: 'translate(-50%, -50%)',
                                }"
                            >
                                <div class="flex items-center gap-2 bg-white rounded-full px-2 py-1 shadow-lg border border-gray-200">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                                    <span class="text-xs font-medium text-gray-700">
                                        {{ cursorData.user.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4 min-w-max pb-4">
                        <div
                            v-for="status in statuses"
                            :key="status.id"
                            class="flex-shrink-0 w-80"
                        >
                            <div class="bg-gray-50 rounded-lg p-4 h-full">
                                <!-- Header de la columna -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-3 h-3 rounded-full"
                                            :style="{ backgroundColor: status.color }"
                                        />
                                        <h3 class="font-semibold text-gray-900">{{ status.name }}</h3>
                                        <span class="text-sm text-gray-500">
                                            ({{ getTasksForStatus(status.id).length }})
                                        </span>
                                    </div>
                                </div>

                                <!-- Lista de tareas -->
                                <draggable
                                    :list="getTasksForStatus(status.id)"
                                    :group="{ name: 'tasks', pull: true, put: true }"
                                    :item-key="(item) => item.id"
                                    :disabled="false"
                                    class="min-h-[200px] space-y-3"
                                    :data-status-id="status.id"
                                    @end="onTaskDragEnd($event, status.id)"
                                    :move="onTaskMove"
                                >
                                    <template #item="{ element: task }">
                                        <div
                                            :key="task.id"
                                            class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-move"
                                            :class="{
                                                'opacity-60': task.has_blocking_dependencies,
                                                'border-red-300': task.has_blocking_dependencies,
                                                'p-2': viewMode === 'compact',
                                                'p-4': viewMode === 'expanded'
                                            }"
                                            @click="openTask(task.id)"
                                        >
                                            <!-- Indicador de bloqueo -->
                                            <div
                                                v-if="task.has_blocking_dependencies"
                                                class="mb-2 flex items-center gap-1 text-xs text-red-600"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                                Bloqueada
                                            </div>

                                            <!-- Título -->
                                            <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                                {{ task.title }}
                                            </h4>

                                            <!-- Descripción -->
                                            <p
                                                v-if="task.description && viewMode === 'expanded'"
                                                class="text-sm text-gray-600 mb-3 line-clamp-2"
                                            >
                                                {{ task.description }}
                                            </p>

                                            <!-- Footer -->
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <!-- Prioridad -->
                                                    <span
                                                        class="px-2 py-1 text-xs font-medium rounded"
                                                        :style="{
                                                            backgroundColor: getPriorityColor(task.priority) + '20',
                                                            color: getPriorityColor(task.priority)
                                                        }"
                                                    >
                                                        {{ getPriorityLabel(task.priority) }}
                                                    </span>
                                                </div>

                                                <!-- Asignado -->
                                                <div v-if="task.assigned_to" class="flex items-center">
                                                    <div
                                                        class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xs font-semibold"
                                                        :title="task.assigned_to.name"
                                                    >
                                                        {{ task.assigned_to.name.charAt(0).toUpperCase() }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Fecha límite -->
                                            <div v-if="task.due_date" class="mt-2">
                                                <span
                                                    class="text-xs"
                                                    :class="{
                                                        'text-red-600': isOverdue(task.due_date),
                                                        'text-gray-500': !isOverdue(task.due_date)
                                                    }"
                                                >
                                                    📅 {{ formatDate(task.due_date) }}
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                </draggable>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de error -->
        <div
            v-if="showErrorModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            @click="showErrorModal = false"
        >
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-4 text-center">
                        No se puede mover la tarea
                    </h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 text-center">
                            {{ errorMessage }}
                        </p>
                        <div v-if="blockingTasks.length > 0" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Tareas bloqueantes:</p>
                            <ul class="list-disc list-inside text-sm text-gray-600">
                                <li v-for="blockingTask in blockingTasks" :key="blockingTask.id">
                                    {{ blockingTask.title }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button
                            @click="showErrorModal = false"
                            class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notificaciones Toast -->
        <div class="fixed bottom-4 right-4 z-50 space-y-2">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                class="bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] animate-slide-in"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="flex-1">{{ notification.message }}</span>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { draggable } from 'vue-draggable-next';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import { useCursorTracking } from '@/composables/useCursorTracking';
import axios from 'axios';

const props = defineProps({
    project: Object,
    statuses: Array,
    tasksByStatus: Object,
    users: Array,
});

// Estado local para las tareas (para actualización optimista)
const localTasksByStatus = ref({ ...props.tasksByStatus });

// Filtros
const filters = ref({
    search: '',
    assigned_to: '',
    priority: '',
    date_filter: '',
});

// Modo de vista
const viewMode = ref('expanded'); // 'compact' o 'expanded'

// Tareas originales sin filtrar
const originalTasksByStatus = ref({ ...props.tasksByStatus });

// Estado del modal de error
const showErrorModal = ref(false);
const errorMessage = ref('');
const blockingTasks = ref([]);

// Obtener tareas para un estado específico (con filtros aplicados)
const getTasksForStatus = (statusId) => {
    let tasks = localTasksByStatus.value[statusId] || [];
    
    // Aplicar filtros
    if (filters.value.search) {
        const searchLower = filters.value.search.toLowerCase();
        tasks = tasks.filter(task => 
            task.title.toLowerCase().includes(searchLower) ||
            (task.description && task.description.toLowerCase().includes(searchLower))
        );
    }
    
    if (filters.value.assigned_to) {
        tasks = tasks.filter(task => 
            task.assigned_to && task.assigned_to.id === parseInt(filters.value.assigned_to)
        );
    }
    
    if (filters.value.priority) {
        tasks = tasks.filter(task => task.priority === filters.value.priority);
    }
    
    if (filters.value.date_filter) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        switch (filters.value.date_filter) {
            case 'today':
                tasks = tasks.filter(task => {
                    if (!task.due_date) return false;
                    const dueDate = new Date(task.due_date);
                    dueDate.setHours(0, 0, 0, 0);
                    return dueDate.getTime() === today.getTime();
                });
                break;
            case 'this_week':
                const weekEnd = new Date(today);
                weekEnd.setDate(weekEnd.getDate() + 7);
                tasks = tasks.filter(task => {
                    if (!task.due_date) return false;
                    const dueDate = new Date(task.due_date);
                    return dueDate >= today && dueDate <= weekEnd;
                });
                break;
            case 'overdue':
                tasks = tasks.filter(task => {
                    if (!task.due_date) return false;
                    const dueDate = new Date(task.due_date);
                    dueDate.setHours(0, 0, 0, 0);
                    return dueDate < today && !task.is_completed;
                });
                break;
            case 'no_date':
                tasks = tasks.filter(task => !task.due_date);
                break;
        }
    }
    
    return tasks;
};

// Verificar si hay filtros activos
const hasActiveFilters = computed(() => {
    return filters.value.search || 
           filters.value.assigned_to || 
           filters.value.priority || 
           filters.value.date_filter;
});

// Aplicar filtros
const applyFilters = () => {
    // Los filtros se aplican automáticamente en getTasksForStatus
    // Solo necesitamos forzar la reactividad
};

// Limpiar filtros
const clearFilters = () => {
    filters.value = {
        search: '',
        assigned_to: '',
        priority: '',
        date_filter: '',
    };
};

// Toggle vista compacta/expandida
const toggleViewMode = () => {
    viewMode.value = viewMode.value === 'compact' ? 'expanded' : 'compact';
};

// Validar movimiento antes de permitirlo
const onTaskMove = async (evt) => {
    const task = evt.draggedContext.element;
    
    // Obtener el nuevo estado desde el elemento destino
    let newStatusId = null;
    if (evt.to) {
        newStatusId = parseInt(evt.to.dataset.statusId);
    } else if (evt.related) {
        newStatusId = parseInt(evt.related.dataset.statusId);
    }

    // Si no hay nuevo estado, permitir el movimiento (solo reordenamiento)
    if (!newStatusId || newStatusId === task.status_id) {
        return true;
    }

    // Si la tarea tiene dependencias bloqueantes, no permitir movimiento
    if (task.has_blocking_dependencies) {
        // Obtener información de bloqueo del servidor
        try {
            const blockingResponse = await axios.get(
                route('projects.tasks.blocking', [props.project.id, task.id])
            );
            blockingTasks.value = blockingResponse.data.blocking_tasks || [];
            errorMessage.value = 'Esta tarea tiene dependencias bloqueantes sin completar.';
            showErrorModal.value = true;
        } catch (error) {
            console.error('Error al validar bloqueo:', error);
        }
        return false;
    }

    return true;
};

// Manejar el final del drag
const onTaskDragEnd = async (evt, newStatusId) => {
    const task = evt.item.element || evt.item;
    const oldStatusId = parseInt(evt.from.dataset.statusId);
    const taskId = task.id;

    if (!taskId) {
        console.error('No se pudo obtener el ID de la tarea');
        router.reload({ only: ['tasksByStatus'] });
        return;
    }

    // Si no cambió de estado, solo actualizar posición
    if (oldStatusId === newStatusId) {
        await updateTaskPosition(taskId, newStatusId, evt.newIndex);
        return;
    }

    // Intentar mover la tarea al nuevo estado
    try {
        const response = await axios.post(
            route('projects.tasks.move', [props.project.id, taskId]),
            {
                status_id: newStatusId,
                position: evt.newIndex,
            }
        );

        if (response.data.success) {
            // Actualizar estado local
            const oldTasks = localTasksByStatus.value[oldStatusId] || [];
            const newTasks = localTasksByStatus.value[newStatusId] || [];
            
            // Remover de la lista antigua
            const taskIndex = oldTasks.findIndex(t => t.id === taskId);
            if (taskIndex > -1) {
                oldTasks.splice(taskIndex, 1);
            }

            // Agregar a la nueva lista
            const updatedTask = {
                ...task,
                status_id: newStatusId,
            };
            newTasks.splice(evt.newIndex, 0, updatedTask);

            localTasksByStatus.value[oldStatusId] = oldTasks;
            localTasksByStatus.value[newStatusId] = newTasks;
        }
    } catch (error) {
        // Revertir el movimiento si falla
        router.reload({ only: ['tasksByStatus'] });
        
        if (error.response?.data?.error) {
            errorMessage.value = error.response.data.message;
            blockingTasks.value = error.response.data.blocking_tasks || [];
            showErrorModal.value = true;
        }
    }
};

// Actualizar posición de la tarea
const updateTaskPosition = async (taskId, statusId, position) => {
    try {
        await axios.post(
            route('projects.tasks.reorder', props.project.id),
            {
                tasks: [{
                    id: taskId,
                    position: position,
                }],
            }
        );
    } catch (error) {
        console.error('Error al actualizar posición:', error);
        router.reload({ only: ['tasksByStatus'] });
    }
};

// Abrir tarea
const openTask = (taskId) => {
    router.visit(route('projects.tasks.show', [props.project.id, taskId]));
};

// Utilidades
const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baja',
        normal: 'Normal',
        high: 'Alta',
        urgent: 'Urgente',
    };
    return labels[priority] || priority;
};

const getPriorityColor = (priority) => {
    const colors = {
        low: '#6b7280',
        normal: '#3b82f6',
        high: '#f59e0b',
        urgent: '#ef4444',
    };
    return colors[priority] || '#6b7280';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        month: 'short',
        day: 'numeric',
    });
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    return new Date(dueDate) < new Date() && !new Date(dueDate).setHours(0, 0, 0, 0) === new Date().setHours(0, 0, 0, 0);
};

// Notificaciones en tiempo real
let echoChannel = null;
const connectedUsers = ref([]);

// Cursor tracking
const { trackMouse, setupCursorListener, cursorPositions } = useCursorTracking(
    props.project.id,
    'kanban',
    props.project.id
);

onMounted(() => {
    // Setup cursor tracking
    setupCursorListener();
    document.addEventListener('mousemove', trackMouse);
    // Suscribirse al canal del proyecto para recibir actualizaciones en tiempo real
    if (window.Echo) {
        try {
            echoChannel = window.Echo.join(`project.${props.project.id}`)
                .here((users) => {
                    connectedUsers.value = users;
                })
                .joining((user) => {
                    if (!connectedUsers.value.find(u => u.id === user.id)) {
                        connectedUsers.value.push(user);
                    }
                    showNotification(`${user.name} se unió al proyecto`);
                })
                .leaving((user) => {
                    connectedUsers.value = connectedUsers.value.filter(u => u.id !== user.id);
                    showNotification(`${user.name} dejó el proyecto`);
                })
                .listen('.task.moved', (event) => {
                    handleTaskMoved(event);
                })
                .listen('.task.updated', (event) => {
                    handleTaskUpdated(event);
                })
                .listen('.comment.created', (event) => {
                    handleCommentCreated(event);
                })
                .listen('.user.joined', (event) => {
                    handleUserJoined(event);
                })
                .listen('.task.created', (event) => {
                    handleTaskCreated(event);
                })
                .listen('.task.deleted', (event) => {
                    handleTaskDeleted(event);
                })
                .listen('.task.status.updated', (event) => {
                    handleTaskStatusUpdated(event);
                })
                .listen('.project.updated', (event) => {
                    handleProjectUpdated(event);
                })
                .listen('.activity.logged', (event) => {
                    handleActivityLogged(event);
                });
        } catch (error) {
            console.error('Error al suscribirse al canal:', error);
        }
    }
});

onUnmounted(() => {
    // Desuscribirse del canal al desmontar el componente
    if (window.Echo && echoChannel) {
        try {
            window.Echo.leave(`project.${props.project.id}`);
        } catch (error) {
            console.error('Error al desuscribirse del canal:', error);
        }
    }
    // Remover listener de mouse
    document.removeEventListener('mousemove', trackMouse);
});

// Manejar evento de tarea actualizada
const handleTaskUpdated = (event) => {
    const { task, user, changes } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    // Si la tarea fue actualizada por otro usuario, actualizar el estado local
    if (user.id !== currentUserId) {
        // Buscar y actualizar la tarea en el estado local
        for (const [statusId, tasks] of Object.entries(localTasksByStatus.value)) {
            const taskIndex = tasks.findIndex(t => t.id === task.id);
            if (taskIndex > -1) {
                // Actualizar la tarea con los nuevos datos
                Object.assign(tasks[taskIndex], {
                    ...task,
                    status_id: task.status_id,
                });
                
                // Si cambió de estado, moverla
                if (parseInt(statusId) !== task.status_id) {
                    // Remover de la lista actual
                    tasks.splice(taskIndex, 1);
                    
                    // Agregar a la nueva lista
                    if (!localTasksByStatus.value[task.status_id]) {
                        localTasksByStatus.value[task.status_id] = [];
                    }
                    localTasksByStatus.value[task.status_id].push({
                        ...task,
                        status_id: task.status_id,
                    });
                }
                
                showNotification(`${user.name} actualizó la tarea "${task.title}"`);
                break;
            }
        }
    }
};

// Manejar evento de comentario creado
const handleCommentCreated = (event) => {
    const { comment, user } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    if (user.id !== currentUserId) {
        showNotification(`${user.name} comentó en una tarea`);
    }
};

// Manejar evento de usuario unido
const handleUserJoined = (event) => {
    const { user } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    if (user.id !== currentUserId) {
        showNotification(`${user.name} se unió al proyecto`);
    }
};

// Manejar evento de tarea creada
const handleTaskCreated = (event) => {
    const { task, user } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    if (user.id !== currentUserId) {
        // Agregar la tarea al estado local
        if (!localTasksByStatus.value[task.status_id]) {
            localTasksByStatus.value[task.status_id] = [];
        }
        localTasksByStatus.value[task.status_id].push(task);
        showNotification(`${user.name} creó la tarea "${task.title}"`);
    }
};

// Manejar evento de tarea eliminada
const handleTaskDeleted = (event) => {
    const { task_id, status_id, user } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    if (user.id !== currentUserId) {
        // Remover la tarea del estado local
        if (localTasksByStatus.value[status_id]) {
            localTasksByStatus.value[status_id] = localTasksByStatus.value[status_id].filter(
                t => t.id !== task_id
            );
        }
        showNotification(`${user.name} eliminó una tarea`);
    }
};

// Manejar evento de estado actualizado
const handleTaskStatusUpdated = (event) => {
    const { status } = event;
    // Actualizar el estado en la lista de estados
    const statusIndex = statuses.value.findIndex(s => s.id === status.id);
    if (statusIndex > -1) {
        Object.assign(statuses.value[statusIndex], status);
    }
};

// Manejar evento de proyecto actualizado
const handleProjectUpdated = (event) => {
    const { project, user } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    if (user.id !== currentUserId) {
        // Actualizar información del proyecto
        Object.assign(props.project, project);
        showNotification(`${user.name} actualizó el proyecto`);
    }
};

// Manejar evento de actividad registrada
const handleActivityLogged = (event) => {
    const { activity } = event;
    // Agregar a historial de actividad si está visible
    showNotification(`Nueva actividad: ${activity.description}`);
};

// Manejar evento de tarea movida
const handleTaskMoved = (event) => {
    const { task, user, from_status_id, to_status_id } = event;
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    
    // Si la tarea fue movida por otro usuario, actualizar el estado local
    if (user && user.id !== currentUserId) {
        // Buscar la tarea en el estado local
        let foundTask = null;
        let foundStatusId = null;
        
        for (const [statusId, tasks] of Object.entries(localTasksByStatus.value)) {
            const taskIndex = tasks.findIndex(t => t.id === task.id);
            if (taskIndex > -1) {
                foundTask = tasks[taskIndex];
                foundStatusId = parseInt(statusId);
                break;
            }
        }
        
        if (foundTask) {
            // Remover de la lista anterior
            if (foundStatusId && localTasksByStatus.value[foundStatusId]) {
                const index = localTasksByStatus.value[foundStatusId].findIndex(t => t.id === task.id);
                if (index > -1) {
                    localTasksByStatus.value[foundStatusId].splice(index, 1);
                }
            }
            
            // Agregar a la nueva lista
            if (!localTasksByStatus.value[to_status_id]) {
                localTasksByStatus.value[to_status_id] = [];
            }
            
            // Actualizar la tarea
            foundTask.status_id = to_status_id;
            foundTask.position = task.position;
            
            // Insertar en la posición correcta
            const insertIndex = Math.min(task.position, localTasksByStatus.value[to_status_id].length);
            localTasksByStatus.value[to_status_id].splice(insertIndex, 0, foundTask);
            
            // Mostrar notificación
            showNotification(`${user.name} movió la tarea "${task.title}"`);
        } else {
            // Si no encontramos la tarea, recargar desde el servidor
            router.reload({ only: ['tasksByStatus'] });
        }
    }
};

// Notificaciones toast
const notifications = ref([]);

const showNotification = (message) => {
    const notification = {
        id: Date.now(),
        message: message,
    };
    
    notifications.value.push(notification);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        const index = notifications.value.findIndex(n => n.id === notification.id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    }, 5000);
};
</script>

<style scoped>
/* Estilos para el drag & drop */
.sortable-ghost {
    opacity: 0.4;
}

.sortable-drag {
    opacity: 0.8;
}

/* Animación para notificaciones */
@keyframes slide-in {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>

