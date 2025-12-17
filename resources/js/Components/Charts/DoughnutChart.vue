<template>
    <div class="chart-container">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import {
    Chart,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';

Chart.register(
    ArcElement,
    Tooltip,
    Legend
);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'right',
        },
        tooltip: {
            enabled: true,
        },
    },
};

const createChart = () => {
    if (!chartCanvas.value) return;

    const ctx = chartCanvas.value.getContext('2d');
    
    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: props.data,
        options: {
            ...defaultOptions,
            ...props.options,
        },
    });
};

onMounted(() => {
    createChart();
});

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});

watch(() => props.data, () => {
    if (chartInstance) {
        chartInstance.data = props.data;
        chartInstance.update();
    }
}, { deep: true });
</script>

<style scoped>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>

