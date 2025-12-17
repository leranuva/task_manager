<template>
    <div class="file-uploader">
        <!-- Zona de drop -->
        <div
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @click="triggerFileInput"
            :class="[
                'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors',
                isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400',
                uploading ? 'opacity-50 cursor-not-allowed' : ''
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                :multiple="multiple"
                :accept="accept"
                @change="handleFileSelect"
                class="hidden"
                :disabled="uploading"
            />
            
            <div v-if="!uploading">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-medium text-blue-600">Haz clic para subir</span>
                    o arrastra y suelta
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    {{ acceptText }} (máx. {{ maxSize }}MB)
                </p>
            </div>
            
            <div v-else class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-600">Subiendo archivo...</span>
            </div>
        </div>

        <!-- Lista de archivos seleccionados (antes de subir) -->
        <div v-if="selectedFiles.length > 0 && !uploading" class="mt-4 space-y-2">
            <div
                v-for="(file, index) in selectedFiles"
                :key="index"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                    </div>
                </div>
                <button
                    @click.stop="removeFile(index)"
                    class="ml-2 text-red-600 hover:text-red-800"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button
                @click="uploadFiles"
                class="w-full mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Subir {{ selectedFiles.length }} {{ selectedFiles.length === 1 ? 'archivo' : 'archivos' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    attachableType: {
        type: String,
        required: true,
    },
    attachableId: {
        type: [Number, String],
        required: true,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: '.jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar',
    },
    maxSize: {
        type: Number,
        default: 10, // MB
    },
});

const emit = defineEmits(['uploaded', 'error']);

const fileInput = ref(null);
const selectedFiles = ref([]);
const uploading = ref(false);
const isDragging = ref(false);

const acceptText = 'Imágenes, PDF, documentos Office, texto, comprimidos';

const triggerFileInput = () => {
    if (!uploading.value) {
        fileInput.value?.click();
    }
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    addFiles(files);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    addFiles(files);
};

const addFiles = (files) => {
    const validFiles = files.filter(file => {
        if (file.size > props.maxSize * 1024 * 1024) {
            emit('error', `El archivo ${file.name} excede el tamaño máximo de ${props.maxSize}MB`);
            return false;
        }
        return true;
    });

    if (props.multiple) {
        selectedFiles.value.push(...validFiles);
    } else {
        selectedFiles.value = validFiles.slice(0, 1);
    }
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const uploadFiles = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;

    try {
        for (const file of selectedFiles.value) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('attachable_type', props.attachableType);
            formData.append('attachable_id', props.attachableId);

            const response = await axios.post(route('projects.attachments.store', props.projectId), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            emit('uploaded', response.data.attachment);
        }

        selectedFiles.value = [];
    } catch (error) {
        emit('error', error.response?.data?.message || 'Error al subir el archivo');
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

