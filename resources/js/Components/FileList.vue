<template>
    <div class="file-list">
        <!-- Modal de preview -->
        <div
            v-if="previewFileData"
            class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4"
            @click="closePreview"
        >
            <div class="bg-white rounded-lg max-w-4xl max-h-[90vh] w-full overflow-auto" @click.stop>
                <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">{{ previewFileData.original_name }}</h3>
                    <button
                        @click="closePreview"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <!-- Preview de imagen -->
                    <img
                        v-if="previewFileData.is_image"
                        :src="previewFileData.url"
                        :alt="previewFileData.original_name"
                        class="max-w-full h-auto mx-auto"
                    />
                    <!-- Preview de PDF -->
                    <iframe
                        v-else-if="previewFileData.is_pdf"
                        :src="previewFileData.url"
                        class="w-full h-[80vh] border-0"
                    />
                    <!-- Otros archivos -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 text-gray-600">Vista previa no disponible para este tipo de archivo</p>
                        <a
                            :href="route('projects.attachments.download', [projectId, previewFileData.id])"
                            class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Descargar archivo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="files.length === 0" class="text-sm text-gray-500 text-center py-4">
            No hay archivos adjuntos
        </div>
        
        <div v-else class="space-y-2">
            <div
                v-for="file in files"
                :key="file.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <!-- Icono o preview -->
                    <div class="flex-shrink-0 cursor-pointer" @click="previewFile(file)">
                        <img
                            v-if="file.is_image"
                            :src="file.url"
                            :alt="file.original_name"
                            class="h-12 w-12 object-cover rounded hover:opacity-80 transition-opacity"
                            @error="handleImageError"
                        />
                        <div
                            v-else-if="file.is_pdf"
                            class="h-12 w-12 bg-red-100 rounded flex items-center justify-center"
                        >
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div
                            v-else
                            class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center"
                        >
                            <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Información del archivo -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ file.original_name }}
                        </p>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                            <span>{{ file.formatted_size }}</span>
                            <span>•</span>
                            <span>Subido por {{ file.uploaded_by?.name || 'Usuario' }}</span>
                            <span>•</span>
                            <span>{{ formatDate(file.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-2 ml-4">
                    <button
                        @click="previewFile(file)"
                        class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded"
                        title="Vista previa"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    <a
                        :href="route('projects.attachments.download', [projectId, file.id])"
                        class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded"
                        title="Descargar"
                        @click.stop
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                    <button
                        v-if="canDelete(file)"
                        @click="deleteFile(file)"
                        class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded"
                        title="Eliminar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    projectId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['deleted']);

const page = usePage();
const currentUser = page.props.auth?.user;
const previewFileData = ref(null);

const canDelete = (file) => {
    return file.uploaded_by?.id === currentUser?.id;
};

const deleteFile = async (file) => {
    if (!confirm(`¿Estás seguro de eliminar "${file.original_name}"?`)) {
        return;
    }

    try {
        await axios.delete(route('projects.attachments.destroy', [props.projectId, file.id]));
        emit('deleted', file.id);
        router.reload({ only: ['task', 'project'] });
    } catch (error) {
        alert('Error al eliminar el archivo');
    }
};

const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Hace unos segundos';
    if (minutes < 60) return `Hace ${minutes} ${minutes === 1 ? 'minuto' : 'minutos'}`;
    if (hours < 24) return `Hace ${hours} ${hours === 1 ? 'hora' : 'horas'}`;
    if (days < 7) return `Hace ${days} ${days === 1 ? 'día' : 'días'}`;

    return d.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const handleImageError = (event) => {
    event.target.style.display = 'none';
};

const previewFile = (file) => {
    previewFileData.value = file;
};

const closePreview = () => {
    previewFileData.value = null;
};
</script>

