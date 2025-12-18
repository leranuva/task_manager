<template>
    <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :class="[
            'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm',
            'focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400',
            'bg-white dark:bg-gray-800 text-gray-900 dark:text-white',
            'placeholder:text-gray-400 dark:placeholder:text-gray-500',
            'disabled:bg-gray-100 dark:disabled:bg-gray-900 disabled:cursor-not-allowed',
            hasError ? 'border-red-500 dark:border-red-400 focus:border-red-500 dark:focus:border-red-400 focus:ring-red-500 dark:focus:ring-red-400' : '',
            inputClass
        ]"
        :required="required"
        :autofocus="autofocus"
        :autocomplete="autocomplete"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    id: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    class: {
        type: String,
        default: '',
    },
    error: {
        type: [String, Boolean],
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);

const inputClass = props.class || '';

// Verificar si hay errores
const hasError = computed(() => {
    return !!props.error;
});
</script>
