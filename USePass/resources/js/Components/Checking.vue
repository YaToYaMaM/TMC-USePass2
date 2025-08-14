<template>
    <div class="p-6 sm:p-8 md:p-10 max-w-5xl mx-auto w-full min-h-screen bg-gradient-to-br from-black via-gray-900 to-white">
        <!-- Input and Button -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-8">
            <input
                v-model="studentId"
                placeholder="Enter Student ID"
                class="w-full sm:w-[1000px] border border-gray-300 rounded-lg px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <button
                @click="fetchStudent"
                class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700 transition"
            >
                Search
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="text-center text-gray-600">Loading...</div>

        <!-- Error -->
        <div v-else-if="error" class="text-center text-red-600 font-medium">{{ error }}</div>

        <!-- Student Profile -->
        <div
            v-else-if="studentProfile"
            class="text-center space-y-6 border border-gray-200 rounded-xl p-8 bg-white shadow-xl"
        >
            <img
                :src="studentProfile.profilePic"
                alt="Profile Picture"
                class="mx-auto w-70 h-64 rounded-md border-4 border-blue-300 object-cover shadow-lg"
            />
            <div class="space-y-2">
                <h2 class="text-3xl font-bold text-gray-900">{{ studentProfile.fullName }}</h2>
                <p class="text-lg text-gray-700"><strong>ID:</strong> {{ studentProfile.studentId }}</p>
                <p class="text-lg text-gray-700"><strong>Program:</strong> {{ studentProfile.program }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const studentId = ref('')
const studentProfile = ref(null)
const error = ref('')
const loading = ref(false)

const mockDatabase = {
    '2022-00001': {
        studentId: '2022-00001',
        fullName: 'Juan Dela Cruz',
        program: 'BS Computer Science',
        profilePic: 'https://randomuser.me/api/portraits/men/1.jpg',
    },
    '2022-00002': {
        studentId: '2022-00002',
        fullName: 'Maria Santos',
        program: 'BS Information Technology',
        profilePic: 'https://randomuser.me/api/portraits/women/2.jpg',
    },
    '2022-00003': {
        studentId: '2022-00003',
        fullName: 'Jose Rizal',
        program: 'BS Software Engineering',
        profilePic: 'https://randomuser.me/api/portraits/men/3.jpg',
    },
}

const fetchStudent = async () => {
    if (!studentId.value.trim()) {
        error.value = 'Please enter a student ID.'
        studentProfile.value = null
        return
    }

    loading.value = true
    error.value = ''
    studentProfile.value = null

    await new Promise(resolve => setTimeout(resolve, 2000)) // Optional 2s delay

    const student = mockDatabase[studentId.value.trim()]
    if (student) {
        studentProfile.value = student
    } else {
        error.value = 'Student ID not found.'
    }

    loading.value = false
}
</script>

<style scoped>
body {
    margin: 0;
    font-family: system-ui, sans-serif;
}
</style>
