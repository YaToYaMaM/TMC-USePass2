<script setup lang="ts">
import { ref } from 'vue';
import html2pdf from 'html2pdf.js';
import  Frontend from "@/Layouts/FrontendLayout.vue";
import { Head } from "@inertiajs/vue3";
import StudentReportTable from "@/Components/StudentReportTable.vue";


const selectedLocation = ref('Tagum');
const selectedDate = ref<string>('');
const selectedProgram = ref('');
const pdfContent = ref(null);
const downloadPDF = () => {
    const element = document.getElementById('pdf-content');
    if (!element) return;

    html2pdf()
        .set({
            margin: 0.5,
            filename: 'attendance-report.pdf',
            html2canvas: {
                scale: 2,
                ignoreElements: (el) => el.classList.contains('no-print'),
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        })
        .from(element)
        .save();
};

</script>

<template>
    <Frontend>
        <Head title="Attendance Page" />
        <div class="flex flex-col p-3 px-12">

            <!-- Top Control Buttons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <span></span>
                <div class="flex flex-wrap items-center gap-2">
                    <button  @click="downloadPDF" class="px-3 py-1 text-sm bg-green-500 text-white rounded">Download as PDF</button>

                    <!-- Button Group -->
                    <div class="inline-flex px-1 py-1 justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
                        <button
                            @click="selectedLocation = 'Tagum'"
                            :class="[
        'px-3 py-1 text-sm border-r rounded-[5px] border-gray-300',
        selectedLocation === 'Tagum'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Tagum
                        </button>
                        <button
                            @click="selectedLocation = 'Mabini'"
                            :class="[
        'px-3 py-1 text-sm rounded-[5px]',
        selectedLocation === 'Mabini'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Mabini
                        </button>
                    </div>
                </div>

            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold">Reports</h1>

                </div>
                <div class="no-print flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                    <input
                        type="date"
                        class="border border-gray-300 p-2 rounded w-full sm:w-36 text-sm"
                        v-model="selectedDate"
                    />
                    <select v-model="selectedProgram" class="border border-gray-300 p-2 w-fit min-w-[350px] text-sm rounded-lg">
                        <option value="">All Programs</option>
                        <option value="Information Technology" class="whitespace-nowrap" >Bachelor of Science in Information Technology</option>
                        <option value="Early Childhood Education" class="whitespace-nowrap">Bachelor of Science in Early Childhood Education</option>
                        <option value="Elementary Education" class="whitespace-nowrap">Bachelor of Elementary Eduction</option>
                        <option value="Secondary Education" class="whitespace-nowrap">Bachelor of Secondary Education</option>
                        <option value="TVL Teacher Educaton" class="whitespace-nowrap">Bachelor of Technical Vocational Teacher Education</option>
                        <option value="Engineering" class="whitespace-nowrap">Bachelor of Science in Agricultural Biosystem Engineering</option>
                    </select>
                </div>

            </div>
            <div id="pdf-content" ref="pdfContent" class="bg-white p-6 mt-2 rounded-lg shadow-md">
                <div class="mb-4 text-center">
                    <h1 class="text-2xl font-bold">TMC Attendance Report</h1>
                    <p class="text-sm">{{ selectedLocation }} | {{ selectedDate || 'All' }} |  {{ selectedProgram || 'All' }}</p>
                </div>
                <StudentReportTable
                    :selectedDate="selectedDate"
                    :selectedProgram="selectedProgram"
                    :selectedUnit="selectedLocation"
                />
            </div>
        </div>
    </Frontend>
</template>
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .for-print {
        display: table-row !important;
    }
}
</style>
