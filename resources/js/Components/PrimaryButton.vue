<template>
    <Button
        :variant="'primary'"
        :disabled="disabled"
        :loading="processing"
        :type="type"
        @click="$emit('click', $event)"
    >
        <slot />
    </Button>
</template>

<script setup>
import Button from './Button.vue';

defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'button',
    },
});

defineEmits(['click']);
</script>

