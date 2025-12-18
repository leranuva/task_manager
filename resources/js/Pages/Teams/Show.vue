<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
            <!-- Hero Header -->
            <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-800 dark:via-purple-800 dark:to-pink-800">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-2xl shadow-lg">
                                {{ team.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h1 class="text-5xl font-bold text-white mb-2 tracking-tight">
                                    {{ team.name }}
                                </h1>
                                <p v-if="team.description" class="text-lg text-indigo-100 dark:text-indigo-200">
                                    {{ team.description }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('teams.edit', team.id)"
                                class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-lg hover:bg-white/30 transition-colors duration-200"
                            >
                                Editar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Proyectos del Equipo -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Proyectos</h2>
                                <Link
                                    :href="route('projects.create', { team_id: team.id })"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                                >
                                    + Nuevo Proyecto
                                </Link>
                            </div>
                            <div v-if="team.projects && team.projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <Link
                                    v-for="project in team.projects"
                                    :key="project.id"
                                    :href="route('projects.show', project.id)"
                                    class="p-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md transition-all duration-300"
                                >
                                    <div class="flex items-center gap-3 mb-2">
                                        <div
                                            class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                                            :style="{ backgroundColor: project.color || '#6366f1' }"
                                        >
                                            {{ project.icon || project.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ project.name }}</h3>
                                    </div>
                                    <p v-if="project.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ project.description }}
                                    </p>
                                </Link>
                            </div>
                            <p v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                No hay proyectos en este equipo
                            </p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Miembros -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Miembros</h2>
                                <button
                                    v-if="canManageMembers"
                                    @click="showInviteModal = true"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                                >
                                    + Invitar
                                </button>
                            </div>
                            <div class="space-y-3">
                                <!-- Owner -->
                                <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold">
                                        {{ team.owner.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ team.owner.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Propietario</p>
                                    </div>
                                </div>
                                <!-- Miembros -->
                                <div
                                    v-for="user in team.users"
                                    :key="user.id"
                                    class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors duration-200"
                                >
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 flex items-center justify-center text-white font-bold">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ getRoleLabel(user.pivot.role) }}</p>
                                    </div>
                                    <div v-if="canManageMembers" class="flex gap-2">
                                        <select
                                            :value="user.pivot.role"
                                            @change="updateMemberRole(user.id, $event.target.value)"
                                            class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        >
                                            <option value="admin">Admin</option>
                                            <option value="member">Member</option>
                                            <option value="viewer">Viewer</option>
                                        </select>
                                        <button
                                            @click="removeMember(user.id)"
                                            class="px-2 py-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded text-xs transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invitaciones Pendientes -->
                        <div v-if="canManageMembers && team.invitations && team.invitations.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Invitaciones Pendientes</h2>
                            <div class="space-y-3">
                                <div
                                    v-for="invitation in team.invitations"
                                    :key="invitation.id"
                                    class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg"
                                >
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ invitation.email }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ getRoleLabel(invitation.role) }}</p>
                                    </div>
                                    <button
                                        @click="cancelInvitation(invitation.id)"
                                        class="px-2 py-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded text-xs transition-colors duration-200"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Invitación -->
            <div
                v-if="showInviteModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                @click.self="showInviteModal = false"
            >
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Invitar Miembro</h3>
                    <form @submit.prevent="inviteMember">
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <TextInput
                                v-model="inviteForm.email"
                                type="email"
                                placeholder="usuario@ejemplo.com"
                                :error="inviteForm.errors.email"
                                class="w-full"
                                required
                            />
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="inviteForm.role"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent"
                                required
                            >
                                <option value="admin">Admin</option>
                                <option value="member">Member</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showInviteModal = false"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 disabled:opacity-50"
                            >
                                <span v-if="inviteForm.processing">Enviando...</span>
                                <span v-else>Enviar Invitación</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    team: Object,
    canManageMembers: Boolean,
});

const showInviteModal = ref(false);

const inviteForm = useForm({
    email: '',
    role: 'member',
});

const getRoleLabel = (role) => {
    const labels = {
        owner: 'Propietario',
        admin: 'Administrador',
        member: 'Miembro',
        viewer: 'Visualizador',
    };
    return labels[role] || role;
};

const inviteMember = () => {
    inviteForm.post(route('invitations.teams.invite', props.team.id), {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const updateMemberRole = (userId, role) => {
    router.put(route('teams.members.update', [props.team.id, userId]), {
        role,
    }, {
        preserveScroll: true,
    });
};

const removeMember = (userId) => {
    if (confirm('¿Estás seguro de que deseas eliminar a este miembro del equipo?')) {
        router.delete(route('teams.members.remove', [props.team.id, userId]), {
            preserveScroll: true,
        });
    }
};

const cancelInvitation = (invitationId) => {
    router.delete(route('invitations.cancel', invitationId), {
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

