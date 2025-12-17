<template>
    <div
        :class="[
            'rounded-lg shadow-sm border transition-all duration-200',
            'bg-white dark:bg-gray-800',
            'border-gray-200 dark:border-gray-700',
            hoverable ? 'hover:shadow-md cursor-pointer' : '',
            padding
        ]"
    >
        <div v-if="title || $slots.header" class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h3 v-if="title" class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h3>
            <slot name="header" />
        </div>
        <slot />
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    hoverable: {
        type: Boolean,
        default: false,
    },
    padding: {
        type: String,
        default: 'p-6',
    },
});
</script>

