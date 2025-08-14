<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue'
import {Head} from "@inertiajs/vue3";
import axios from 'axios';
import html2pdf from 'html2pdf.js';
import { ref, computed, watch, onMounted } from 'vue';

// Define props using defineProps (Composition API way)
const props = defineProps({
    selectedFaculty: {
        type: Object,
        default: null
    }
});
const downloadPDF = () => {
    const element = document.getElementById('pdf-content');
    if (!element) return;

    html2pdf()
        .set({
            margin: 0.1,
            filename: 'faculty-attendance-report.pdf',
            html2canvas: {
                scale: 2,
                ignoreElements: (el) => el.classList.contains('no-print'),
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        })
        .from(element)
        .save();
};

// Reactive data
const selectedUnit = ref("Tagum");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchText = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]); // Today's date
const selectedDepartment = ref('');
const allFacultyRecords = ref([]); // Store all records
const loading = ref(false);
const error = ref('');
const localSelectedFaculty = ref(null);

// Computed properties
const availableDepartments = computed(() => {
    // Filter departments based on selected unit first
    const unitFilteredRecords = allFacultyRecords.value.filter(faculty =>
        faculty.unit === selectedUnit.value
    );
    const departments = [...new Set(unitFilteredRecords.map(faculty => faculty.department))].filter(dept => dept);
    return departments.sort();
});

const filteredFaculty = computed(() => {
    const search = searchText.value.toLowerCase();
    const department = selectedDepartment.value;
    const unit = selectedUnit.value;

    return allFacultyRecords.value.filter(faculty => {
        // First filter by unit - this is the main addition
        const matchesUnit = faculty.unit === unit;

        const matchesSearch =
            faculty.name.toLowerCase().includes(search) ||
            (faculty.department && faculty.department.toLowerCase().includes(search)) ||
            (faculty.unit && faculty.unit.toLowerCase().includes(search)) ||
            (faculty.employee_id && faculty.employee_id.toString().toLowerCase().includes(search));

        const matchesDepartment = !department || faculty.department === department;

        return matchesUnit && matchesSearch && matchesDepartment;
    });
});

const totalPages = computed(() => {
    return Math.ceil(filteredFaculty.value.length / itemsPerPage.value);
});

const paginatedFaculty = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredFaculty.value.slice(start, start + itemsPerPage.value);
});

// Status helper functions
const getStatusText = (faculty) => {
    const hasTimeIn = faculty.time_in && faculty.time_in !== 'N/A' && faculty.time_in !== 'null' && faculty.time_in !== 'undefined';
    const hasTimeOut = faculty.time_out && faculty.time_out !== 'N/A' && faculty.time_out !== 'null' && faculty.time_out !== 'undefined';

    if (hasTimeIn && !hasTimeOut) {
        return 'Present';
    } else if (hasTimeIn && hasTimeOut) {
        return 'Already Out';
    } else {
        return 'Unknown';
    }
};

const getStatusClass = (faculty) => {
    const hasTimeIn = faculty.time_in && faculty.time_in !== 'N/A' && faculty.time_in !== 'null' && faculty.time_in !== 'undefined';
    const hasTimeOut = faculty.time_out && faculty.time_out !== 'N/A' && faculty.time_out !== 'null' && faculty.time_out !== 'undefined';

    if (hasTimeIn && !hasTimeOut) {
        return 'bg-green-100 text-green-800'; // Present - Green
    } else if (hasTimeIn && hasTimeOut) {
        return 'bg-red-100 text-red-800'; // Already Out - Red
    } else {
        return 'bg-gray-100 text-gray-800'; // Unknown - Gray
    }
};

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

const fetchFacultyRecords = () => {
    loading.value = true;
    error.value = '';

    // Fetch all records without unit filtering on the backend
    // We'll do the unit filtering on the frontend
    axios.get('/faculty-records', {
        params: {
            date: selectedDate.value,
            department: selectedDepartment.value || undefined,
            // Remove unit parameter - we'll filter on frontend
        }
    })
        .then((response) => {
            allFacultyRecords.value = response.data
                .map((record) => ({
                    id: record.faculty_id,
                    name: record.name || 'Unknown Name',
                    department: record.faculty_department,
                    position: record.faculty_unit, // Using faculty_unit as position
                    employee_id: record.faculty_id,
                    unit: record.faculty_unit, // Make sure this field is correctly mapped
                    email: record.faculty_email,
                    phone: record.faculty_phone_num,
                    gender: record.faculty_gender,
                    middle_initial: record.faculty_middle_intial,
                    date: record.date,
                    time_in: record.record_in,
                    time_out: record.record_out,
                    raw_date: record.date,
                    raw_time_in: record.record_in
                }));

            // Debug: Log the records to verify data structure
            console.log('Total faculty records received:', response.data.length);
            console.log('All faculty records:', allFacultyRecords.value.length);
            console.log('Sample record:', allFacultyRecords.value[0]);

            // Log unique units to verify data
            const uniqueUnits = [...new Set(allFacultyRecords.value.map(r => r.unit))];
            console.log('Available units in data:', uniqueUnits);

            // Log unique departments to verify data
            const uniqueDepartments = [...new Set(allFacultyRecords.value.map(r => r.department))];
            console.log('Available departments in data:', uniqueDepartments);
        })
        .catch((err) => {
            console.error('Error fetching faculty records:', err);
            error.value = 'Failed to load faculty records. Please try again.';
        })
        .finally(() => {
            loading.value = false;
        });
};

const selectUnit = (unit) => {
    selectedUnit.value = unit;
    // Reset department filter when unit changes since available departments will change
    selectedDepartment.value = '';
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

const clearFilters = () => {
    searchText.value = '';
    selectedDepartment.value = '';
    localSelectedFaculty.value = null;
    currentPage.value = 1;
};

const applyFilters = () => {
    currentPage.value = 1;
    // Filters are applied reactively, so this just resets pagination
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

const selectFaculty = (faculty) => {
    localSelectedFaculty.value = faculty;
    searchText.value = faculty.name;
    // You might want to emit this to parent component
    // emit('facultySelected', faculty);
};

// Watchers
watch(searchText, () => {
    currentPage.value = 1;
});

watch(selectedDate, () => {
    currentPage.value = 1;
    fetchFacultyRecords();
});

watch(selectedDepartment, () => {
    currentPage.value = 1;
    // Don't refetch - we already have all the data
});

// Watch for selectedFaculty prop changes and update search input
watch(() => props.selectedFaculty, (newFaculty) => {
    if (newFaculty && newFaculty.name) {
        searchText.value = newFaculty.name;
        localSelectedFaculty.value = newFaculty;
    }
}, { immediate: true });

// Watch for search text changes and clear selection when user types
watch(searchText, (newSearchText, oldSearchText) => {
    // Only clear selection if the user manually changed the search text
    // (not when it was programmatically set by selecting a faculty member)
    if (localSelectedFaculty.value &&
        newSearchText !== localSelectedFaculty.value.name) {
        localSelectedFaculty.value = null;
        // Emit an event to parent to clear selectedFaculty if needed
        // emit('clearSelectedFaculty');
    }
    // Reset pagination when searching
    currentPage.value = 1;
});

// Lifecycle
onMounted(() => {
    fetchFacultyRecords();
});
</script>

<template>
    <Frontend>
    <Head title="USePass" />
    <div class="min-h-screen bg-gray-50">
        <!-- Filter Section -->
        <div class="w-full px-4 lg:px-8 py-2 bg-gray-50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <span></span>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex w-full lg:w-auto text-sm p-1 bg-green-500 rounded-lg shadow-sm">
                    <button  @click="downloadPDF" class="px-3 py-1 text-sm text-white rounded">Download as PDF</button>
                        </div>
                    <!-- Button Group -->
                    <div class="inline-flex w-full lg:w-auto text-sm bg-gray-100 p-1 rounded-lg shadow-sm">
                        <button
                            @click="selectUnit('Tagum')"
                            :class="buttonClass('Tagum')"
                            class="flex-1 lg:flex-none px-4 py-2 rounded-md font-medium transition-all duration-200"
                        >
                            Tagum
                        </button>
                        <button
                            @click="selectUnit('Mabini')"
                            :class="buttonClass('Mabini')"
                            class="flex-1 lg:flex-none px-4 py-2 rounded-md font-medium transition-all duration-200"
                        >
                            Mabini
                        </button>
                    </div>
                </div>

            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold">Faculty Reports</h1>

                </div>

                <div class="no-print flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                    <div class="relative w-full lg:max-w-lg">
                        <input
                            type="text"
                            v-model="searchText"
                            placeholder="Search faculty/staff..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        />
                        <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M15.5 10.5a5 5 0 11-10 0 5 5 0 0110 0z" />
                        </svg>
                    </div>
                    <div class="flex flex-col xs:flex-row xs:items-center space-y-2 xs:space-y-0 xs:space-x-3">
                        <input
                            type="date"
                            v-model="selectedDate"
                            class="w-full xs:w-auto border border-gray-300 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        />
                    </div>
                    <div class="relative w-full xs:w-auto xs:min-w-[200px]">
                        <select
                            v-model="selectedDepartment"
                            class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-colors"
                        >
                            <option value="">All Departments</option>
                            <option v-for="department in availableDepartments" :key="department" :value="department">
                                {{ department }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
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
                    Loading faculty/staff...
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-white shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="text-red-600 text-sm font-medium">{{ error }}</div>
                    <button @click="fetchFacultyRecords" class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Try Again
                    </button>
                </div>
            </div>
            <!-- Faculty/Staff Table -->
            <div v-else class="bg-white shadow rounded-lg overflow-hidden">

                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="mb-4 text-center">
                        <h1 class="text-2xl font-bold">TMC Attendance Report</h1>
                        <p class="text-sm">{{ selectedDate || 'All' }} |  {{ selectedDepartment || 'All' }}</p>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Faculty/Staff Inside Campus</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Showing {{ filteredFaculty.length }} faculty/staff members for {{ selectedUnit }} unit
                        <span v-if="localSelectedFaculty">
                            (Selected: {{ localSelectedFaculty.name }})
                        </span>
                        <span v-else-if="props.selectedFaculty && searchText === props.selectedFaculty.name">
                            (Selected: {{ props.selectedFaculty.name }})
                        </span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty/Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(faculty, index) in paginatedFaculty"
                                :key="faculty.id"
                                @click="selectFaculty(faculty)"
                                :class="[
                                'hover:bg-gray-50 transition-colors duration-150 cursor-pointer',
                                (localSelectedFaculty?.id === faculty.id ||
                                 (props.selectedFaculty?.id === faculty.id && searchText === props.selectedFaculty.name)) &&
                                 getStatusText(faculty) === 'Present' ? 'bg-blue-50 border-l-4 border-[#760000]' : ''
                                ]"
                            >
                                <!-- Faculty/Staff Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ faculty.name }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ faculty.employee_id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Department Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ faculty.department || 'N/A' }}</div>
                                </td>

                                <!-- Unit Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ faculty.unit || 'N/A' }}
                                    </span>
                                </td>

                                <!-- Time In Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ formatTime(faculty.time_in) }}</div>
                                    <div class="text-xs text-gray-400">{{ formatDate(faculty.date) }}</div>
                                </td>

                                <!-- Time Out Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ formatTime(faculty.time_out) }}</div>
                                    <div class="text-xs text-gray-400">{{ formatDate(faculty.date) }}</div>
                                </td>

                                <!-- Status Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusClass(faculty)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ getStatusText(faculty) }}
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="filteredFaculty.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No faculty/staff found</h3>
                            <p class="mt-1 text-sm text-gray-500">No faculty/staff members are currently inside the campus matching your filters.</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="filteredFaculty.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
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
                                <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredFaculty.length) }}</span>
                                of
                                <span class="font-medium">{{ filteredFaculty.length }}</span>
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
