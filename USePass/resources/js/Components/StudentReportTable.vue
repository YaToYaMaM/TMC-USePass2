<script setup lang="ts">
import axios from "axios";
import { onMounted, onBeforeUnmount, ref, computed, watch } from "vue";

interface StudentRecord {
    id: string;
    name: string;
    program: string;
    major: string;
    unit: string;
    date: string;
    time_in: string;
    time_out: string;
}

const props = defineProps<{
    selectedDate: string;
    selectedProgram?: string;
    selectedUnit?: string;
}>();

const studentRecords = ref<StudentRecord[]>([]);
const attendanceFilter = ref("");
const currentPage = ref(1);
const itemsPerPage = 8;
const loading = ref(false);
const error = ref("");

function formatTime(timeString: string): string {
    if (!timeString || timeString === 'null' || timeString === 'undefined' || timeString.trim() === '') {
        return 'N/A';
    }

    try {
        // Handle different time formats
        let timeToFormat = timeString;

        // If it's just time (HH:MM or HH:MM:SS), add a date
        if (timeString.match(/^\d{1,2}:\d{2}(:\d{2})?$/)) {
            timeToFormat = `1970-01-01T${timeString}`;
        }

        const date = new Date(timeToFormat);

        // Check if the date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid time format:', timeString);
            return 'N/A';
        }

        let hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12;
        const minutesStr = minutes < 10 ? '0' + minutes : minutes.toString();

        return `${hours}:${minutesStr} ${ampm}`;
    } catch (error) {
        console.warn('Error formatting time:', timeString, error);
        return 'N/A';
    }
}

function formatDate(dateString: string): string {
    if (!dateString || dateString === 'null' || dateString === 'undefined') {
        return 'N/A';
    }

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';

        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
}

const fetchStudentRecords = () => {
    // Only show loading on initial load, not on auto-refresh
    if (studentRecords.value.length === 0) {
        loading.value = true;
    }
    error.value = '';

    axios.get('/student-records', {
        params: {
            date: props.selectedDate,
            program: props.selectedProgram || undefined
        }
    })
        .then((response) => {
            studentRecords.value = response.data.map((record: any) => ({
                id: record.student_id,
                name: record.name,
                program: record.students_program,
                major: record.students_major,
                unit: record.students_unit,
                date: record.date,
                time_in: record.record_in,
                time_out: record.record_out,
            }));
            console.log("Fetched records:", studentRecords.value);
        })
        .catch((err) => {
            console.error('Error fetching student records:', err);
            error.value = 'Failed to load student records. Please try again.';
        })
};

watch(() => props.selectedDate, fetchStudentRecords, { immediate: true });
watch(() => props.selectedProgram, fetchStudentRecords);
watch(() => props.selectedUnit, () => {
    currentPage.value = 1; // Reset pagination when unit changes
});

let intervalId: ReturnType<typeof setInterval>;

onMounted(() => {
    fetchStudentRecords();
    intervalId = setInterval(fetchStudentRecords, 2000);
});

onBeforeUnmount(() => {
    clearInterval(intervalId);
});

const filteredRecords = computed(() => {
    let records = studentRecords.value;

    // Filter by unit if specified
    if (props.selectedUnit) {
        records = records.filter((record) => record.unit === props.selectedUnit);
    }

    // Filter by attendance status
    if (attendanceFilter.value === "time_in") {
        records = records.filter((record) => record.time_in && record.time_in !== 'null');
    } else if (attendanceFilter.value === "time_out") {
        records = records.filter((record) => record.time_out && record.time_out !== 'null');
    } else if (attendanceFilter.value === "present") {
        records = records.filter((record) =>
            record.time_in && record.time_in !== 'null' &&
            (!record.time_out || record.time_out === 'null')
        );
    }

    return records;
});

const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredRecords.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
    Math.ceil(filteredRecords.value.length / itemsPerPage)
);

const getStatusBadge = (record: StudentRecord) => {
    const hasTimeIn = record.time_in && record.time_in !== 'null';
    const hasTimeOut = record.time_out && record.time_out !== 'null';

    if (hasTimeIn && hasTimeOut) {
        return { text: 'Already Out', class: 'bg-blue-100 text-blue-800' };
    } else if (hasTimeIn && !hasTimeOut) {
        return { text: 'Present', class: 'bg-green-100 text-green-800' };
    } else {
        return { text: 'No Record', class: 'bg-gray-100 text-gray-800' };
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Filter Controls -->
        <div class="no-print bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Student Attendance Records</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Showing {{ filteredRecords.length }} records
                        <span v-if="selectedUnit"> for {{ selectedUnit }} unit</span>
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <label class="text-sm font-medium text-gray-700">Filter by status:</label>
                    <select
                        v-model="attendanceFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                    >
                        <option value="">All Records</option>
                        <option value="present">Currently Present</option>
                        <option value="time_in">Time In Only</option>
                        <option value="time_out">Time Out Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading State - Only show on initial load -->
        <div v-if="loading && studentRecords.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-center justify-center">
                <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading records...
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center">
                <div class="text-red-600 text-sm font-medium">{{ error }}</div>
                <button @click="fetchStudentRecords" class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    Try Again
                </button>
            </div>
        </div>

        <!-- Modern Table -->
        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Student
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Program & Major
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Unit
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Time In
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Time Out
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Date
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="record in paginatedRecords"
                        :key="record.id"
                        class="hover:bg-gray-50 transition-colors duration-150"
                    >
                        <!-- Student Column - ID removed -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ record.name }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Program & Major Column -->
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ record.program }}</div>
                            <div class="text-xs text-gray-500">{{ record.major || 'N/A' }}</div>
                        </td>

                        <!-- Unit Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ record.unit }}
                                </span>
                        </td>

                        <!-- Time In Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ formatTime(record.time_in) }}
                            </div>
                        </td>

                        <!-- Time Out Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ formatTime(record.time_out) }}
                            </div>
                        </td>

                        <!-- Status Column -->
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ getStatusBadge(record).text }}
                                </span>
                        </td>

                        <!-- Date Column -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(record.date) }}
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="filteredRecords.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No records found</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        No student records match your current filters.
                    </p>
                </div>
            </div>

            <!-- Modern Pagination -->
            <div v-if="filteredRecords.length > 0" class="no-print bg-white px-6 py-4 flex items-center justify-between border-t border-gray-200">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button
                        @click="prevPage"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Previous
                    </button>
                    <button
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        Next
                    </button>
                </div>

                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                            to
                            <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredRecords.length) }}</span>
                            of
                            <span class="font-medium">{{ filteredRecords.length }}</span>
                            results
                        </p>
                    </div>

                    <div>
                        <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px">
                            <button
                                @click="prevPage"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                Page {{ currentPage }} of {{ totalPages }}
                            </span>

                            <button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }

    .bg-white {
        background-color: white !important;
    }

    .shadow-sm,
    .shadow-md {
        box-shadow: none !important;
    }
}
</style>
