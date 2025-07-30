<template>
    <div class="bg-white rounded-lg shadow p-6 w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Students</h3>
            <a href="/students" class="text-blue-500 text-sm">See All</a>
        </div>
        <ul>
            <li v-for="(student, index) in students" :key="index" class="flex items-center space-x-3 mb-3">
                <img :src="student.avatar" class="w-10 h-10 rounded-full object-cover" />
                <div>
                    <p class="text-sm font-semibold">{{ student.name }}</p>
                    <p class="text-xs text-gray-500">{{ student.email }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
const students = [
    { name: 'Student 1', email: 'email@figmasfakedomain.net', avatar: 'https://i.pravatar.cc/40?img=1' },
    { name: 'Student 1', email: 'email@figmasfakedomain.net', avatar: 'https://i.pravatar.cc/40?img=2' },
    { name: 'Student 1', email: 'email@figmasfakedomain.net', avatar: 'https://i.pravatar.cc/40?img=3' },
    { name: 'Student 1', email: 'email@figmasfakedomain.net', avatar: 'https://i.pravatar.cc/40?img=4' },
    { name: 'Student 1', email: 'email@figmasfakedomain.net', avatar: 'https://i.pravatar.cc/40?img=5' },
]
</script>
