<template>
    <AppLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('projects.show', project.id)"
                        class="text-blue-600 hover:text-blue-800 mb-4 inline-block"
                    >
                        ← Volver al proyecto
                    </Link>
                    <h2 class="text-2xl font-bold text-gray-900">Galería de Imágenes</h2>
                    <p class="text-gray-600">{{ project.name }}</p>
                </div>

                <!-- Filtros -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <div class="flex items-center gap-4">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por nombre..."
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            @input="searchFiles"
                        />
                        <select
                            v-model="fileTypeFilter"
                            @change="searchFiles"
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Todos los tipos</option>
                            <option value="image">Solo imágenes</option>
                            <option value="pdf">Solo PDFs</option>
                            <option value="document">Documentos</option>
                        </select>
                    </div>
                </div>

                <!-- Grid de imágenes -->
                <div v-if="loading" class="text-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div v-else-if="files.length === 0" class="text-center py-12 text-gray-500">
                    No se encontraron archivos
                </div>

                <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div
                        v-for="file in files"
                        :key="file.id"
                        class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                        @click="previewFile(file)"
                    >
                        <!-- Thumbnail o icono -->
                        <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                            <img
                                v-if="file.is_image"
                                :src="file.url"
                                :alt="file.original_name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else-if="file.is_pdf" class="w-full h-full bg-red-100 flex items-center justify-center">
                                <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <!-- Información -->
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 truncate" :title="file.original_name">
                                {{ file.original_name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ file.formatted_size }} • {{ formatDate(file.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex justify-center">
                    <div class="flex gap-2">
                        <button
                            v-for="page in pagination.last_page"
                            :key="page"
                            @click="loadPage(page)"
                            :class="[
                                'px-3 py-2 rounded-md text-sm',
                                pagination.current_page === page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>

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
                            :href="route('projects.attachments.download', [project.id, previewFileData.id])"
                            class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Descargar archivo
                        </a>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    project: Object,
});

const files = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const fileTypeFilter = ref('');
const pagination = ref(null);
const previewFileData = ref(null);

const searchFiles = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(route('projects.attachments.search', props.project.id), {
            params: {
                search: searchQuery.value || undefined,
                file_type: fileTypeFilter.value || undefined,
                page,
            },
        });
        files.value = response.data.data;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
        };
    } catch (error) {
        console.error('Error searching files:', error);
    } finally {
        loading.value = false;
    }
};

const loadPage = (page) => {
    searchFiles(page);
};

const previewFile = (file) => {
    previewFileData.value = file;
};

const closePreview = () => {
    previewFileData.value = null;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

onMounted(() => {
    searchFiles();
});
</script>

