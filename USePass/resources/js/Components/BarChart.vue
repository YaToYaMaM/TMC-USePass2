<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Students by Program</h3>
        <Bar v-if="chartData" :data="chartData" :options="chartOptions" />
        <div v-else>Loading chart...</div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const chartData = ref(null)

const chartOptions = {
    responsive: true,
    scales: {
        y: {
            beginAtZero: true,
        },
    },
}

const fetchChartData = async () => {
    try {
        const response = await axios.get('/getProgramCategoryCounts')
        console.log('API Response:', response.data)

        const labels = response.data.map(item => item.course)
        const data = response.data.map(item => item.count)

        chartData.value = {
            labels: labels,
            datasets: [
                {
                    label: 'Students',
                    data: data,
                    backgroundColor: '#760000',
                    barThickness: 50,
                },
            ],
        }
    } catch (error) {
        console.error('Error fetching chart data:', error)
    }
}

onMounted(() => {
    fetchChartData()
})
</script>
