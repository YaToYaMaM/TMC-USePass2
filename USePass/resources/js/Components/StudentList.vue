<template>
    <div v-for="student in paginatedStudents" :key="student.students_id" class="bg-white rounded-lg shadow p-6 w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Students</h3>
            <a href="/students" class="text-blue-500 text-sm">See All</a>
        </div>
        <ul>
            <li v-for="(student, index) in students" :key="index" class="flex items-center space-x-3 mb-3">
                <img :src="`/${student.students_profile_image}`" class="w-10 h-10 rounded-full object-cover" alt="Student Image"/>
                <div>
                    <p class="text-sm font-semibold">{{ student.students_first_name }} {{ student.students_middle_initial }} {{ student.students_last_name }} </p>
                    <p class="text-xs text-gray-500">{{ student.students_email }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">

import {ref, onMounted, computed} from "vue";
import axios from "axios";

const students = ref<any[]>([]);

const fetchStudents = () => {
    axios.get('/students/list')
        .then((response) => {
            students.value = response.data.map((student: any) => ({
                students_id: student.students_id,
                students_first_name: student.students_first_name,
                students_middle_initial: student.students_middle_initial,
                students_last_name: student.students_last_name,
                students_program: student.students_program,
                students_major: student.students_major,
                students_profile_image: student.students_profile_image,
                students_email: student.students_email,
            }));
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
        });
};

onMounted(() => {
    fetchStudents();
    setInterval(fetchStudents, 5000);
});

const currentPage = ref(1);
const studentsPerPage = 4;

const selectedProgram = ref('');
const searchQuery = ref('');

const majorsByProgram = {
    'Information Technology': ['Information Security'],
    'Education': [
        'Elementary Education',
        'Early Childhood Education',
        'Special Needs Education',

    ],
    'Secondary Education':[
        'English',
        'Mathematics',
        'Filipino',
    ],
    'Engineering': [
        'Land and Water Resources',
        'Machinery and Power',
        'Process Engineering',
        'Structures and Environment',
    ],
    'TVL Teacher Education':[
        'Agricultural Crops Technology',
        'Animal Production',
    ],
};

const filteredStudents = computed(() => {
    let result = students.value;

    if (selectedProgram.value) {
        const majors = majorsByProgram[selectedProgram.value] || [];
        result = result.filter(student =>
            majors.includes(student.students_major)
        );
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(student =>
            student.students_first_name.toLowerCase().includes(query) ||
            student.students_last_name.toLowerCase().includes(query)
        );
    }

    return result;
});

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * studentsPerPage;
    const end = start + studentsPerPage;
    return filteredStudents.value.slice(start, end);
});
</script>
