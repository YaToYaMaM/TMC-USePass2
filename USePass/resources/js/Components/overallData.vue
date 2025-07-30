<template>
    <div class="bg-white rounded-lg shadow p-6">
        <table class="w-full text-left border-collapse">
            <tbody>
            <tr v-for="(overall, index) in overalls" :key="index" class="border-b">
                <td class="py-2 px-4" style="color: #828282;">{{ overall.dataSubject }}</td>
                <td class="py-2 px-4 text-right" style="color: #828282;">{{ overall.count }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const overalls = ref([])

const fetchCounts = async () => {
    try {
        const response = await axios.get('/getCounts')
        const data = response.data

        overalls.value = [
            { dataSubject: 'STUDENTS', count: data.students },
            { dataSubject: 'TEACHERS', count: data.teachers ?? 0 },
            { dataSubject: 'VISITORS', count: data.visitors ?? 0 },
            { dataSubject: 'ALUMNI', count: data.alumni ?? 0 },
        ]
    } catch (error) {
        console.error('Error fetching dashboard data:', error)
    }
}

onMounted(fetchCounts)
</script>
