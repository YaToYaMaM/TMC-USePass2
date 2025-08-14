<template>
    <Head title="Dashboard" />
    <Frontend>
        <div class="px-3 pt-6 min-h-screen overflow-auto space-y-6 px-12">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <div class="inline-flex px-1 py-1 justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit max-h-10">
                    <button
                        @click="selectUnit('Tagum')"
                        :class="[
                    'px-3 py-1 rounded-[5px] font-semibold transition text-center',
                    selectedUnit === 'Tagum'
                      ? 'bg-white text-black shadow'
                      : 'bg-transparent text-black'
                  ]"
                    >
                        Tagum
                    </button>
                    <button
                        @click="selectUnit('Mabini')"
                        :class="[
                    'px-3 py-1 rounded-[5px] font-semibold transition text-center',
                    selectedUnit === 'Mabini'
                      ? 'bg-white text-black shadow'
                      : 'bg-transparent text-black'
                  ]"
                    >
                        Mabini
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <StatCard
                    title="Students"
                    :value="studentCount"
                    subtitle="Students at the campus"
                />
                <StatCard
                    title="Faculty and Staff"
                    :value="facultyStaffCount"
                    subtitle="Faculty and Staff at the campus"
                />
                <StatCard
                    title="Visitors"
                    :value="0"
                    subtitle="Visitors at the campus"
                />
            </div>


            <div class="grid grid-cols-1">
                <div class="grid grid-cols-2 md:col-span-2 gap-5">
                    <BarChart />
                    <StudentsList />
                </div>
            </div>
        </div>
    </Frontend>
</template>


<script setup lang="ts">
import StatCard from '../../Components/StatCard.vue'
import StudentsList from '../../Components/StudentList.vue'
import BarChart from '../../Components/BarChart.vue'
import Frontend from '@/Layouts/FrontendLayout.vue'
import {computed, onMounted, ref} from "vue";
import {Head} from "@inertiajs/vue3";
import axios from "axios";

const selectedUnit = ref('Tagum')
function selectUnit(unit) {
    selectedUnit.value = unit
}

//For Student Count at the campus by AKSELOT
const students = ref<any[]>([]);

// Count only students still inside campus
const studentCount = computed(() => {
    return students.value.filter(students => !students.record_out).length;
});
const fetchStudents = () => {
    axios.get('/student-records')
        .then((response) => {
            students.value = response.data.map((students: any) => ({
                record_in: students.record_in,
                record_out: students.record_out, // include record_out for condition
            }));
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
        });
};

onMounted(() => {
    fetchStudents();
    fetchFacultyStaff();
    setInterval(() => {
        fetchStudents();
        fetchFacultyStaff();
    }, 5000);
});

//For Faculty and Staff Count at the campus by AKSELOT

const facultyStaff = ref<any[]>([]);

// Count only faculty/staff still inside campus
const facultyStaffCount = computed(() => {
    return facultyStaff.value.filter(facultyStaff => !facultyStaff.record_out).length;
});
const fetchFacultyStaff = () => {
    axios.get('/faculty-records')
        .then((response) => {
            // console.log("FACULTY API RESPONSE:", response.data); // debug
            facultyStaff.value = response.data.map((facultyStaff: any) => ({
                record_in: facultyStaff.record_in,
                record_out: facultyStaff.record_out, // include record_out for condition
            }));
        })
        .catch((error) => {
            console.error('Error fetching faculty and staff:', error);
        });
};

</script>
