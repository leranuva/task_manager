import { ref, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

export function useTypingIndicator(projectId, context, contextId) {
    const page = usePage();
    const currentUserId = page.props.auth?.user?.id;
    const typingUsers = ref([]);
    let typingTimeout = null;

    // Notificar que el usuario está escribiendo
    const notifyTyping = () => {
        if (!window.Echo) return;

        // Cancelar timeout anterior
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }

        // Notificar al servidor
        axios.post(route('projects.realtime.typing', projectId), {
            context: context,
            context_id: contextId,
            is_typing: true,
        }).catch(() => {
            // Silenciar errores
        });

        // Después de 3 segundos sin escribir, notificar que dejó de escribir
        typingTimeout = setTimeout(() => {
            axios.post(route('projects.realtime.typing', projectId), {
                context: context,
                context_id: contextId,
                is_typing: false,
            }).catch(() => {});
        }, 3000);
    };

    // Escuchar eventos de typing
    const setupTypingListener = () => {
        if (!window.Echo) return;

        window.Echo.channel(`project.${projectId}`)
            .listen('.user.typing', (event) => {
                if (event.user.id === currentUserId) return;
                if (event.context !== context || event.context_id !== contextId) return;

                if (event.is_typing) {
                    if (!typingUsers.value.find(u => u.id === event.user.id)) {
                        typingUsers.value.push(event.user);
                    }
                } else {
                    typingUsers.value = typingUsers.value.filter(
                        u => u.id !== event.user.id
                    );
                }
            });
    };

    // Limpiar al desmontar
    onUnmounted(() => {
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }
        if (window.Echo) {
            window.Echo.leave(`project.${projectId}`);
        }
    });

    return {
        notifyTyping,
        setupTypingListener,
        typingUsers,
    };
}
