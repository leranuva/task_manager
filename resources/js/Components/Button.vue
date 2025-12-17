<template>
    <component
        :is="tag"
        :href="href"
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center px-4 py-2 border rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
            buttonClasses,
            disabled || loading ? 'opacity-50 cursor-not-allowed' : '',
            loading ? 'relative' : ''
        ]"
        @click="handleClick"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <slot />
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary', // primary, secondary, danger, success, warning
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    href: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: 'button',
    },
});

const emit = defineEmits(['click']);

const tag = computed(() => props.href ? Link : 'button');

const buttonClasses = computed(() => {
    const variants = {
        primary: 'bg-blue-600 hover:bg-blue-700 text-white border-transparent focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600',
        secondary: 'bg-gray-600 hover:bg-gray-700 text-white border-transparent focus:ring-gray-500 dark:bg-gray-500 dark:hover:bg-gray-600',
        danger: 'bg-red-600 hover:bg-red-700 text-white border-transparent focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
        success: 'bg-green-600 hover:bg-green-700 text-white border-transparent focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600',
        warning: 'bg-yellow-600 hover:bg-yellow-700 text-white border-transparent focus:ring-yellow-500 dark:bg-yellow-500 dark:hover:bg-yellow-600',
        outline: 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 focus:ring-gray-500',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-xs',
        md: 'px-4 py-2 text-xs',
        lg: 'px-6 py-3 text-sm',
    };

    return `${variants[props.variant] || variants.primary} ${sizes[props.size] || sizes.md}`;
});

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

