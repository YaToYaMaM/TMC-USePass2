<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

// Define the User interface with role property
interface User {
    id: number;
    name: string;
    email?: string;
    role: 'admin' | 'guard' | 'user';
}

// Extend the Inertia page props to include our User type
interface PageProps {
    auth: {
        user: User;
    };
}

const page = usePage();
const currentUser = computed(() => page.props.auth.user as User & { role: string });

const props = defineProps<{
    reports: any[];
    spot: any[];
    selectedDate?: string;
}>();

const reports = ref(props.reports);

function formatDateTime(dateString: string): string {
    if (!dateString) return '';

    const date = new Date(dateString);

    // Format date as YYYY-MM-DD
    const formattedDate = date.toISOString().split('T')[0];

    // Format time as 12-Hour format with AM/PM
    const formattedTime = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    return `${formattedDate} | ${formattedTime}`;
}

const showViewModal = ref(false);
const showAddModal = ref(false);
const selectedReport = ref<any>(null);

// Form data for new report
const newReport = ref({
    findings: '',
    time: '',
    date: '',
    location: '',
    actionTaken: '',
    teamLeader: '',
    departmentRepresentative: '',
    Rtype: 'Spot Report'
});

// Form validation errors
const formErrors = ref<Record<string, string>>({});

// Check if user can view a specific report
function canViewReport(report: any): boolean {
    if (!currentUser.value) {
        return false;
    }

    if (currentUser.value.role === "admin") {
        return true;
    }

    if (currentUser.value.role === "guard") {
        return report.user_id === currentUser.value.id;
    }

    return false;
}

function openEditModal(report: any) {
    if (!canViewReport(report)) {
        alert("You don't have permission to view this report.");
        return;
    }

    selectedReport.value = report;
    showViewModal.value = true;
}

// New function to open add modal
function openAddModal() {
    resetForm();
    showAddModal.value = true;
}

// Reset form data
function resetForm() {
    newReport.value = {
        findings: '',
        time: '',
        date: new Date().toISOString().split('T')[0], // Set current date as default
        location: '',
        actionTaken: '',
        teamLeader: '',
        departmentRepresentative: '',
        Rtype: 'Spot Report'
    };
    formErrors.value = {};
}

// Validate form
function validateForm(): boolean {
    formErrors.value = {};

    if (!newReport.value.findings.trim()) {
        formErrors.value.findings = 'Findings are required';
    }

    if (!newReport.value.time.trim()) {
        formErrors.value.time = 'Time is required';
    }

    if (!newReport.value.date.trim()) {
        formErrors.value.date = 'Date is required';
    }

    if (!newReport.value.location.trim()) {
        formErrors.value.location = 'Location is required';
    }

    if (!newReport.value.actionTaken.trim()) {
        formErrors.value.actionTaken = 'Action taken is required';
    }

    if (!newReport.value.teamLeader.trim()) {
        formErrors.value.teamLeader = 'Team leader is required';
    }

    if (!newReport.value.departmentRepresentative.trim()) {
        formErrors.value.departmentRepresentative = 'Department representative is required';
    }

    return Object.keys(formErrors.value).length === 0;
}

// Submit new report
function submitReport() {
    if (!validateForm()) {
        return;
    }

    if (!currentUser.value) {
        alert('User not authenticated');
        return;
    }

    // Create new report object
    const reportToAdd = {
        id: Math.max(...reports.value.map(r => r.id)) + 1,
        user_id: currentUser.value.id,
        guardName: currentUser.value.name,
        ...newReport.value
    };

    // Add to reports array
    reports.value.unshift(reportToAdd);

    // Close modal and reset form
    showAddModal.value = false;
    resetForm();

    // Show success message
    alert('Spot report created successfully!');
}

function printReport() {
    if (!selectedReport.value || !canViewReport(selectedReport.value)) {
        alert("You don't have permission to print this report.");
        return;
    }

    const reportParams = {
        id: selectedReport.value.id,
        guardName: selectedReport.value.guardName,
        findings: selectedReport.value.findings,
        time: selectedReport.value.time,
        date: selectedReport.value.date,
        location: selectedReport.value.location,
        actionTaken: selectedReport.value.actionTaken,
        teamLeader: selectedReport.value.teamLeader,
        departmentRepresentative: selectedReport.value.departmentRepresentative,
        Rtype: selectedReport.value.Rtype
    };

    const query = new URLSearchParams(reportParams).toString();
    window.open(`/spot-report/print?${query}`, '_blank');
}

const currentPage = ref(1);
const itemsPerPage = 7;

const filteredReport = computed(() => {
    let filtered = reports.value;

    if (props.selectedDate) {
        filtered = filtered.filter((s) => s.date === props.selectedDate);
    }

    filtered = filtered.filter((report) => canViewReport(report));

    return filtered;
});


const paginatedIncident = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredReport.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
    Math.ceil(filteredReport.value.length / itemsPerPage)
);

const getRoleDisplayName = computed(() => {
    if (!currentUser.value) {
        return "User";
    }

    switch (currentUser.value.role) {
        case "admin":
            return "Administrator";
        case "guard":
            return "Security Guard";
        default:
            return "User";
    }
});

watch(filteredReport, () => {
    if (currentPage.value > totalPages.value && totalPages.value > 0) {
        currentPage.value = 1;
    }
});
</script>

<template>
    <!-- User Info Header -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4" v-if="currentUser">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">Welcome, {{ currentUser.name }}</h3>
                <p class="text-sm text-blue-700">{{ getRoleDisplayName }}</p>
            </div>
            <div class="text-sm text-blue-600">
                <span v-if="currentUser.role === 'guard'">
                    You can create and view your own spot reports
                </span>
            </div>
        </div>
    </div>

    <!-- Add Spot Report Button -->
    <div class="mb-4">
        <button
            v-if="currentUser?.role === 'guard'"
            @click="openAddModal"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition duration-200"
        >
            + Spot Report
        </button>
    </div>

    <div class="overflow-x-auto mt-6 rounded-lg shadow border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white text-sm text-left">
            <thead class="bg-gray-50">
            <tr class="text-gray-600 uppercase text-xs tracking-wider">
                <th class="px-6 py-3 text-left">Guard on Duty</th>
                <th class="px-6 py-3 text-left">Findings</th>
                <th class="px-6 py-3 text-left">Type of Report</th>
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-center">Action</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
            <tr
                v-for="(item, index) in paginatedIncident"
                :key="item.id || index"
                :class="{
                    'bg-green-50': currentUser && item.user_id === currentUser.id && currentUser.role === 'guard',
                }"
            >
                <td class="px-6 py-4 font-medium whitespace-nowrap">
                    {{ item.guard_name }}
                    <span
                        v-if="currentUser && item.user_id === currentUser.id && currentUser.role === 'guard'"
                        class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded"
                    >Your Report</span>
                </td>
                <td class="px-6 py-4 font-semibold">{{ item.findings }}</td>
                <td class="px-6 py-4 font-semibold">{{ item.action_taken }}</td>
                <td class="px-6 py-4 font-semibold">{{formatDateTime(item.created_at)}}</td>
                <td class="px-6 py-4 text-center">
                    <button
                        @click="openEditModal(item)"
                        :disabled="!canViewReport(item)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs transition"
                        :class="canViewReport(item)
                            ? 'bg-blue-500 text-white hover:bg-blue-600'
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                        </svg>
                        {{ canViewReport(item) ? "View" : "Restricted" }}
                    </button>
                </td>
            </tr>

            <tr v-if="paginatedIncident.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg
                            class="w-12 h-12 mb-2 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <p class="text-lg font-medium">No spot reports available</p>
                        <p class="text-sm">
                            {{ currentUser && currentUser.role === "guard"
                            ? "You haven't created any spot reports yet."
                            : "No spot reports match your current filter." }}
                        </p>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex justify-center mt-4 gap-2 p-4" v-if="totalPages > 1">
            <button
                :disabled="currentPage === 1"
                @click="currentPage--"
                class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Prev
            </button>

            <span class="px-4 py-1 text-sm text-gray-700 flex items-center">
                Page {{ currentPage }} of {{ totalPages }}
            </span>

            <button
                :disabled="currentPage === totalPages"
                @click="currentPage++"
                class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Next
            </button>
        </div>
    </div>

    <!-- Add Report Modal -->
    <div
        v-if="showAddModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
        @click.self="showAddModal = false"
    >
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <button
                @click="showAddModal = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold focus:outline-none z-10"
                title="Close"
            >
                &times;
            </button>

            <!-- Header -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2 pr-8">
                Create New Spot Report
            </h2>

            <!-- Form -->
            <form @submit.prevent="submitReport" class="space-y-4">
                <!-- Findings -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Findings <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="newReport.findings"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': formErrors.findings }"
                        placeholder="Describe what you found or observed..."
                    ></textarea>
                    <p v-if="formErrors.findings" class="text-red-500 text-xs mt-1">{{ formErrors.findings }}</p>
                </div>

                <!-- Time and Date Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Time <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="time"
                            v-model="newReport.time"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.time }"
                        />
                        <p v-if="formErrors.time" class="text-red-500 text-xs mt-1">{{ formErrors.time }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="newReport.date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.date }"
                        />
                        <p v-if="formErrors.date" class="text-red-500 text-xs mt-1">{{ formErrors.date }}</p>
                    </div>
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Location <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="newReport.location"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': formErrors.location }"
                        placeholder="e.g., Main Building, Parking Area, etc."
                    />
                    <p v-if="formErrors.location" class="text-red-500 text-xs mt-1">{{ formErrors.location }}</p>
                </div>

                <!-- Action Taken -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Action Taken <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="newReport.actionTaken"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': formErrors.actionTaken }"
                        placeholder="Describe what action you took..."
                    ></textarea>
                    <p v-if="formErrors.actionTaken" class="text-red-500 text-xs mt-1">{{ formErrors.actionTaken }}</p>
                </div>

                <!-- Team Leader and Department Rep Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Team Leader <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="newReport.teamLeader"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.teamLeader }"
                        >
                            <option value="" disabled selected>Select a team leader</option>
                            <option value="Alice">Alice</option>
                            <option value="Bob">Bob</option>
                            <option value="Charlie">Charlie</option>
                            <!-- Add more team leaders as needed -->
                        </select>
                        <p v-if="formErrors.teamLeader" class="text-red-500 text-xs mt-1">{{ formErrors.teamLeader }}</p>
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Department Representative <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="newReport.departmentRepresentative"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.departmentRepresentative }"
                            placeholder="Department representative name"
                        />
                        <p v-if="formErrors.departmentRepresentative" class="text-red-500 text-xs mt-1">{{ formErrors.departmentRepresentative }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="showAddModal = false"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200"
                    >
                        Create Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div
        v-if="showViewModal && selectedReport"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4"
        @click.self="showViewModal = false"
    >
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <button
                @click="showViewModal = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold focus:outline-none z-10"
                title="Close"
            >
                &times;
            </button>

            <!-- Header -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 border-b pb-2 pr-8">
                Spot Report Details
                <span
                    v-if="currentUser && selectedReport.user_id === currentUser.id && currentUser.role === 'guard'"
                    class="text-sm bg-green-100 text-green-800 px-2 py-1 rounded ml-2"
                >Your Report</span>
            </h2>

            <!-- Report Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm text-gray-700">
                <div>
                    <label class="text-lg font-bold block mb-2">Findings:</label>
                    <p class="text-gray-900">{{ selectedReport.findings }}</p>
                </div>
                <div>
                    <label class="text-lg font-bold block mb-2">Time:</label>
                    <p class="text-gray-900">{{ selectedReport.time }}</p>
                </div>
                <div>
                    <label class="text-lg font-bold block mb-2">Date:</label>
                    <p class="text-gray-900">{{ selectedReport.date }}</p>
                </div>
                <div>
                    <label class="text-lg font-bold block mb-2">Location:</label>
                    <p class="text-gray-900">{{ selectedReport.location }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Guard Name:</label>
                    <p class="text-gray-900">{{ selectedReport.guardName }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Team Leader:</label>
                    <p class="text-gray-900">{{ selectedReport.teamLeader }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Type:</label>
                    <p class="text-gray-900">{{ selectedReport.Rtype }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Action Taken:</label>
                    <p class="text-gray-900">{{ selectedReport.actionTaken }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="font-bold text-lg block mb-2">Department Representative:</label>
                    <p class="text-gray-900">{{ selectedReport.departmentRepresentative }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-2">
                <button
                    @click="showViewModal = false"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition text-sm"
                >
                    Close
                </button>
                <button
                    v-if="currentUser && currentUser.role === 'guard' && selectedReport.user_id === currentUser.id"
                    @click="printReport"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition text-sm"
                >
                    Print
                </button>
                <button
                    v-if="currentUser && currentUser.role === 'admin'"
                    @click="printReport"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition text-sm"
                >
                    Print Report
                </button>
            </div>
        </div>
    </div>
</template>
