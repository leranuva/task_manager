<template>
    <div
        class="metric-card bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-200 dark:border-gray-700"
        :class="animationClass"
    >
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    {{ title }}
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ formattedValue }}
                </p>
                <p v-if="subtitle" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ subtitle }}
                </p>
                <div v-if="change !== null" class="mt-2 flex items-center">
                    <span
                        :class="[
                            'text-sm font-medium',
                            change >= 0
                                ? 'text-green-600 dark:text-green-400'
                                : 'text-red-600 dark:text-red-400'
                        ]"
                    >
                        <svg
                            v-if="change >= 0"
                            class="inline h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"
                            />
                        </svg>
                        <svg
                            v-else
                            class="inline h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3"
                            />
                        </svg>
                        {{ Math.abs(change) }}%
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                        vs período anterior
                    </span>
                </div>
            </div>
            <div
                v-if="icon"
                class="flex-shrink-0 ml-4"
                :class="iconColorClass"
            >
                <component :is="icon" class="h-8 w-8" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    change: {
        type: Number,
        default: null,
    },
    icon: {
        type: [Object, String],
        default: null,
    },
    iconColor: {
        type: String,
        default: 'blue',
    },
    format: {
        type: String,
        default: 'number', // 'number', 'percentage', 'currency'
    },
    subtitle: {
        type: String,
        default: null,
    },
    animation: {
        type: String,
        default: 'fade-in',
    },
});

const formattedValue = computed(() => {
    if (props.format === 'percentage') {
        return `${props.value}%`;
    }
    if (props.format === 'currency') {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR',
        }).format(props.value);
    }
    return new Intl.NumberFormat('es-ES').format(props.value);
});

const iconColorClass = computed(() => {
    const colors = {
        blue: 'text-blue-600 dark:text-blue-400',
        green: 'text-green-600 dark:text-green-400',
        red: 'text-red-600 dark:text-red-400',
        yellow: 'text-yellow-600 dark:text-yellow-400',
        purple: 'text-purple-600 dark:text-purple-400',
        gray: 'text-gray-600 dark:text-gray-400',
    };
    return colors[props.iconColor] || colors.blue;
});

const animationClass = computed(() => {
    const animations = {
        'fade-in': 'animate-fade-in',
        'slide-up': 'animate-slide-up',
        'scale-in': 'animate-scale-in',
    };
    return animations[props.animation] || '';
});
</script>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.5s ease-out;
}

.animate-scale-in {
    animation: scale-in 0.5s ease-out;
}
</style>

