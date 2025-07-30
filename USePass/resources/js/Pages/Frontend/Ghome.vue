<template>
    <Head title="USePass" />
    <Frontend>
        <div class="min-h-screen bg-gray-50">
            <!-- Header Section with Controls -->
            <div class="bg-white shadow-sm border-b sticky top-0 z-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="font-bold text-lg sm:text-xl text-gray-800">Home</h1>
                        <div class="inline-flex text-sm bg-gray-100 p-1 rounded-lg shadow-sm">
                            <button @click="selectUnit('Tagum')" :class="buttonClass('Tagum')">Tagum</button>
                            <button @click="selectUnit('Mabini')" :class="buttonClass('Mabini')">Mabini</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white shadow-sm border-b">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">

                        <!-- Filters -->
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <!-- Search Input -->
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="searchText"
                                    placeholder="Search students..."
                                    class="pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm w-full sm:w-60 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M15.5 10.5a5 5 0 11-10 0 5 5 0 0110 0z" />
                                </svg>
                            </div>

                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Date:</label>
                                <input
                                    type="date"
                                    v-model="selectedDate"
                                    class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Program:</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedProgram"
                                        class="appearance-none border border-gray-300 rounded-md px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white min-w-[200px]"
                                    >
                                        <option value="">All Programs</option>
                                        <option v-for="program in availablePrograms" :key="program" :value="program">
                                            {{ program }}
                                        </option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                        <svg class="w-4 h-4" fill="none">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Loading State -->
                <div v-if="loading" class="bg-white shadow rounded-lg p-6 text-center">
                    <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading students...
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-white shadow rounded-lg p-6">
                    <div class="text-center">
                        <div class="text-red-600 text-sm font-medium">{{ error }}</div>
                        <button @click="fetchStudentRecords" class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                            Try Again
                        </button>
                    </div>
                </div>

                <!-- Students Table -->
                <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Students Inside Campus</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Showing {{ filteredStudents.length }} students for {{ selectedUnit }} unit
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Major</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="(student, index) in paginatedStudents"
                                    :key="student.id"
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                >
                                    <!-- Student Column -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                                                <div class="text-sm text-gray-500">{{ student.program }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Major Column -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ student.major || 'N/A' }}</div>
                                    </td>

                                    <!-- Unit Column -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ student.unit }}
                                        </span>
                                    </td>

                                    <!-- Time In Column -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ formatTime(student.time_in) }}</div>
                                        <div class="text-xs text-gray-400">{{ formatDate(student.date) }}</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <!-- Empty State -->
                            <div v-if="filteredStudents.length === 0" class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                                <p class="mt-1 text-sm text-gray-500">No students are currently inside the campus matching your filters.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination Footer -->
                    <div v-if="filteredStudents.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button
                                @click="prevPage"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
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
                                    <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredStudents.length) }}</span>
                                    of
                                    <span class="font-medium">{{ filteredStudents.length }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button
                                        @click="prevPage"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        Page {{ currentPage }} of {{ totalPages }}
                                    </span>
                                    <button
                                        @click="nextPage"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Frontend>
</template>

<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue'
import {Head} from "@inertiajs/vue3";
import axios from 'axios';
import { ref, computed, watch, onMounted } from 'vue';

// Reactive data
const selectedUnit = ref("Tagum");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchText = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]); // Today's date
const selectedProgram = ref('');
const allStudentRecords = ref([]); // Store all records
const loading = ref(false);
const error = ref('');

// Computed properties
const availablePrograms = computed(() => {
    // Filter programs based on selected unit first
    const unitFilteredRecords = allStudentRecords.value.filter(student =>
        student.unit === selectedUnit.value
    );
    const programs = [...new Set(unitFilteredRecords.map(student => student.program))];
    return programs.sort();
});

const filteredStudents = computed(() => {
    const search = searchText.value.toLowerCase();
    const program = selectedProgram.value;
    const unit = selectedUnit.value;

    return allStudentRecords.value.filter(student => {
        // First filter by unit - this is the main fix
        const matchesUnit = student.unit === unit;

        const matchesSearch =
            student.name.toLowerCase().includes(search) ||
            student.program.toLowerCase().includes(search) ||
            (student.major && student.major.toLowerCase().includes(search));

        const matchesProgram = !program || student.program === program;

        return matchesUnit && matchesSearch && matchesProgram;
    });
});

const totalPages = computed(() => {
    return Math.ceil(filteredStudents.value.length / itemsPerPage.value);
});

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredStudents.value.slice(start, start + itemsPerPage.value);
});

const formatDate = (dateString) => {
    if (!dateString || dateString === 'null' || dateString === 'undefined') {
        return 'N/A';
    }

    try {
        let date;

        if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}/)) {
            date = new Date(dateString + 'T00:00:00');
        } else {
            date = new Date(dateString);
        }

        if (isNaN(date.getTime())) {
            console.warn('Invalid date string:', dateString);
            return 'Invalid Date';
        }

        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', dateString, error);
        return 'Invalid Date';
    }
};

const formatTime = (timeString) => {
    if (!timeString || timeString === 'null' || timeString === 'undefined') {
        return 'N/A';
    }

    try {
        let time;

        if (typeof timeString === 'string') {
            if (timeString.match(/^\d{1,2}:\d{2}(:\d{2})?$/)) {
                time = new Date(`1970-01-01T${timeString}${timeString.length === 5 ? ':00' : ''}`);
            } else {
                time = new Date(timeString);
            }
        } else {
            time = new Date(timeString);
        }

        if (isNaN(time.getTime())) {
            console.warn('Invalid time string:', timeString);
            return 'Invalid Time';
        }

        return time.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        console.error('Error formatting time:', timeString, error);
        return 'Invalid Time';
    }
};

const fetchStudentRecords = () => {
    loading.value = true;
    error.value = '';

    // Fetch all records without unit filtering on the backend
    // We'll do the unit filtering on the frontend
    axios.get('/student-records', {
        params: {
            date: selectedDate.value,
            program: selectedProgram.value || undefined,
            // Remove unit parameter - we'll filter on frontend
        }
    })
        .then((response) => {
            allStudentRecords.value = response.data
                .filter((record) => {
                    // Filter for students currently inside campus
                    const hasTimeIn = record.record_in &&
                        record.record_in !== null &&
                        record.record_in !== '' &&
                        record.record_in !== 'null';

                    const hasNoTimeOut = !record.record_out ||
                        record.record_out === null ||
                        record.record_out === '' ||
                        record.record_out === 'null';

                    return hasTimeIn && hasNoTimeOut;
                })
                .map((record) => ({
                    id: record.student_id,
                    name: record.name,
                    program: record.students_program,
                    major: record.students_major,
                    unit: record.students_unit, // Make sure this field is correctly mapped
                    date: record.date,
                    time_in: record.record_in,
                    time_out: record.record_out,
                    raw_date: record.date,
                    raw_time_in: record.record_in
                }));

            // Debug: Log the records to verify unit field
            console.log('Total records received:', response.data.length);
            console.log('Active students (inside campus):', allStudentRecords.value.length);
            console.log('Sample record with unit:', allStudentRecords.value[0]);

            // Log unique units to verify data
            const uniqueUnits = [...new Set(allStudentRecords.value.map(r => r.unit))];
            console.log('Available units in data:', uniqueUnits);
        })
        .catch((err) => {
            console.error('Error fetching student records:', err);
            error.value = 'Failed to load student records. Please try again.';
        })
        .finally(() => {
            loading.value = false;
        });
};

const selectUnit = (unit) => {
    selectedUnit.value = unit;
    // Reset program filter when unit changes since available programs will change
    selectedProgram.value = '';
    currentPage.value = 1;
};

const buttonClass = (unit) => {
    return [
        'px-3 py-1.5 rounded-lg font-medium transition-all duration-150',
        selectedUnit.value === unit
            ? 'bg-white text-black shadow-sm'
            : 'bg-transparent text-gray-600 hover:bg-gray-200',
    ];
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

// Watchers
watch(searchText, () => {
    currentPage.value = 1;
});

watch(selectedDate, () => {
    currentPage.value = 1;
    fetchStudentRecords();
});

watch(selectedProgram, () => {
    currentPage.value = 1;
    // Don't refetch - we already have all the data
});

// Note: removed the selectedUnit watcher that was calling fetchStudentRecords
// since we now do frontend filtering

// Lifecycle
onMounted(() => {
    fetchStudentRecords();
});
</script>
