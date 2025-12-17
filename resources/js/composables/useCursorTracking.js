import { ref, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { throttle } from 'lodash';

const cursorPositions = ref({});

export function useCursorTracking(projectId, context, contextId) {
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;

    // Función throttled para enviar posición del cursor
    const sendCursorPosition = throttle((position) => {
        if (!window.Echo) return;

        axios.post(route('projects.realtime.cursor', projectId), {
            context: context,
            context_id: contextId,
            position: position,
        }).catch(() => {
            // Silenciar errores
        });
    }, 100); // Actualizar cada 100ms

    // Trackear movimiento del mouse
    const trackMouse = (event) => {
        const position = {
            x: event.clientX,
            y: event.clientY,
        };
        sendCursorPosition(position);
    };

    // Trackear posición en Kanban (columna/fila)
    const trackKanbanPosition = (columnId, taskId) => {
        const position = {
            column: columnId,
            row: taskId,
        };
        sendCursorPosition(position);
    };

    // Escuchar eventos de cursor
    const setupCursorListener = () => {
        if (!window.Echo) return;

        window.Echo.channel(`project.${projectId}`)
            .listen('.cursor.moved', (event) => {
                if (event.user.id === currentUserId) return;

                const key = `${event.context}_${event.context_id}`;
                cursorPositions.value[key] = cursorPositions.value[key] || {};
                cursorPositions.value[key][event.user.id] = {
                    user: event.user,
                    position: event.position,
                    timestamp: Date.now(),
                };

                // Remover posiciones antiguas (más de 2 segundos)
                setTimeout(() => {
                    if (cursorPositions.value[key] && cursorPositions.value[key][event.user.id]) {
                        delete cursorPositions.value[key][event.user.id];
                    }
                }, 2000);
            });
    };

    // Limpiar al desmontar
    onUnmounted(() => {
        if (window.Echo) {
            window.Echo.leave(`project.${projectId}`);
        }
    });

    return {
        trackMouse,
        trackKanbanPosition,
        setupCursorListener,
        cursorPositions,
    };
}

