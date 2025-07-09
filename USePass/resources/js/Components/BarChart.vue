<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Statistics</h3>
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const chartData = {
    labels: ['BSIT', 'BEED', 'BSED', 'BTVTED', 'BSABE'],
    datasets: [
        {
            label: 'Students',
            data: [700, 350, 540, 780, 1150],
            backgroundColor: '#000',
        },
    ],
}

const chartOptions = {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
        },
    },
}
</script>


