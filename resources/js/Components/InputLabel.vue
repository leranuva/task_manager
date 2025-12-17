<template>
    <label :for="for" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        <slot />
    </label>
</template>

<script setup>
defineProps({
    for: {
        type: String,
        default: null,
    },
    value: {
        type: String,
        default: null,
    },
});
</script>

