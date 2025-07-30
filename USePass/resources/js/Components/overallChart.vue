<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Overall Data</h3>
        <Bar v-if="chartData" :data="chartData" :options="chartOptions" />
        <div v-else>Loading...</div>
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
            ticks: {
                stepSize: 1,
            },
        },
    },
}

const fetchCounts = async () => {
    try {
        const response = await axios.get('/getCounts')
        const data = response.data

        const labels = ['STUDENTS', 'TEACHERS', 'VISITORS', 'ALUMNI']
        const counts = [
            data.students ?? 0,
            data.teachers ?? 0,
            data.visitors ?? 0,
            data.alumni ?? 0,
        ]

        chartData.value = {
            labels: labels,
            datasets: [
                {
                    label: 'Total Count',
                    data: counts,
                    backgroundColor: ['#760000', '#8e0000', '#a00000', '#b30000'],
                    barThickness: 50,
                },
            ],
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error)
    }
}

onMounted(fetchCounts)
</script>


