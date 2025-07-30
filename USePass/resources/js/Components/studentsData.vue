<template>
    <div class="bg-white rounded-lg shadow p-6">
        <table class="w-full text-left border-collapse">
            <tbody>
            <tr v-for="(student, index) in students" :key="index" class="border-b">
                <td class="py-2 px-4"style="color: #828282;">{{ student.course }}</td>
                <td class="py-2 px-4 text-right"style="color: #828282;">{{ student.count }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const students = ref([]);

onMounted(async () => {
    const res = await axios.get('/students-by-category');
    students.value = res.data;
});

</script>
