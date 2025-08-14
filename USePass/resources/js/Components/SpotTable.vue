<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";

// For image upload
const selectedImages = ref<File[]>([]);
const imagePreviews = ref<string[]>([]);

function handleImageUpload(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
        selectedImages.value = Array.from(files);
        imagePreviews.value = selectedImages.value.map(file => URL.createObjectURL(file));

        // Assign files to form data
        newReport.spotPicture = selectedImages.value;
    }
}

// Define the User interface with role property
interface User {
    id: number;
    first_name: string;
    last_name: string;
    email?: string;
    role: 'admin' | 'guard' | 'user';
    name?: string;
}

// Extend the Inertia page props to include our User type
interface PageProps {
    auth: {
        user: User;
    };
}

const page = usePage();
const currentUser = computed(() => page.props.auth.user as User);

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

// Form data for new report - FIXED: Added guard_name field
const newReport = useForm({
    findings: '',
    time: '',
    date: '',
    location: '',
    action_taken: '', // FIXED: Changed from actionTaken to action_taken
    team_leader: '', // FIXED: Changed from teamLeader to team_leader
    department_representative: '', // FIXED: Changed from departmentRepresentative to department_representative
    guard_name: '', // FIXED: Added guard_name field
    spotPicture: null as File[] | null,
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

// Reset form data - FIXED: Updated field names and added guard_name
function resetForm() {
    newReport.reset();

    // Set default values
    const now = new Date();
    newReport.date = now.toISOString().split('T')[0]; // Set current date as default
    newReport.time = now.toTimeString().slice(0, 5); // Set current time as default

    // Set guard name from current user
    if (currentUser.value) {
        newReport.guard_name = `${currentUser.value.first_name} ${currentUser.value.last_name}`;
    }

    selectedImages.value = [];
    imagePreviews.value = [];
    formErrors.value = {};
}

// Validate form - FIXED: Updated field names
function validateForm(): boolean {
    formErrors.value = {};

    if (!newReport.findings.trim()) {
        formErrors.value.findings = 'Findings are required';
    }

    if (!newReport.time.trim()) {
        formErrors.value.time = 'Time is required';
    }

    if (!newReport.date.trim()) {
        formErrors.value.date = 'Date is required';
    }

    if (!newReport.location.trim()) {
        formErrors.value.location = 'Location is required';
    }

    if (!newReport.action_taken.trim()) {
        formErrors.value.action_taken = 'Action taken is required';
    }

    if (!newReport.team_leader.trim()) {
        formErrors.value.team_leader = 'Team leader is required';
    }

    if (!newReport.department_representative.trim()) {
        formErrors.value.department_representative = 'Department representative is required';
    }

    if (!newReport.guard_name.trim()) {
        formErrors.value.guard_name = 'Guard name is required';
    }

    return Object.keys(formErrors.value).length === 0;
}

// Submit new report - FIXED: Updated to use proper form submission
function submitReport() {
    if (!validateForm()) {
        return;
    }

    if (!currentUser.value) {
        alert('User not authenticated');
        return;
    }

    const formData = {
        findings: newReport.findings,
        team_leader: newReport.team_leader,
        action_taken: newReport.action_taken,
        department_representative: newReport.department_representative,
        location: newReport.location,
        date: newReport.date
    };

    // Submit the form using Inertia's post method
    newReport.post('/spot-report', {
        forceFormData: true, // Required for sending images
        onSuccess: (page) => {
            showAddModal.value = false;
            resetForm();
            alert('Spot report created successfully!');

            // Update the reports list with the new data from the server
            if (page.props && page.props.reports) {
                reports.value.unshift(page.props.newReport);
            }else {
                // ✅ Fallback: manually create the report object using stored form data
                const newReportData = {
                    id: Date.now(), // temporary ID
                    guard_name: currentUser.value.name || `${currentUser.value.first_name} ${currentUser.value.last_name}`,
                    user_id: currentUser.value.id,
                    created_at: new Date().toISOString(),
                    // ✅ Use the stored form data instead of the reset form
                    findings: formData.findings,
                    team_leader: formData.team_leader,
                    action_taken: formData.action_taken,
                    department_representative: formData.department_representative,
                    location: formData.location,
                    date: formData.date
                };
                reports.value.unshift(newReportData);
            }
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);

            // Map server validation errors to form errors
            Object.keys(errors).forEach(key => {
                formErrors.value[key] = errors[key];
            });
        },
    });
}

function printReport() {
    if (!selectedReport.value || !canViewReport(selectedReport.value)) {
        alert("You don't have permission to print this report.");
        return;
    }

    const reportParams = {
        id: selectedReport.value.id,
        guard_name: selectedReport.value.guard_name || selectedReport.value.guardName,
        findings: selectedReport.value.findings,
        time: selectedReport.value.time,
        date: selectedReport.value.date,
        location: selectedReport.value.location,
        action_taken: selectedReport.value.action_taken || selectedReport.value.actionTaken,
        team_leader: selectedReport.value.team_leader || selectedReport.value.teamLeader,
        department_representative: selectedReport.value.department_representative || selectedReport.value.departmentRepresentative,
    };

    const query = new URLSearchParams(reportParams).toString();
    window.open(`/spot-report/print?${query}`, '_blank');
}

const currentPage = ref(1);
const itemsPerPage = 7;

const filteredReport = computed(() => {
    let filtered = reports.value || [];

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

// Add these new reactive variables to your existing refs
const showImageModal = ref(false);
const selectedImageUrl = ref('');

// Update your existing getImageUrl function to handle more cases
function getImageUrl(imagePath: string | string[]): string {
    if (!imagePath) return '';

    // Handle array case (get first image)
    if (Array.isArray(imagePath)) {
        if (imagePath.length === 0) return '';
        imagePath = imagePath[0];
    }

    // If it's already a full URL, return as is
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }

    // If it starts with storage/, return with leading slash
    if (imagePath.startsWith('storage/')) {
        return `/${imagePath}`;
    }

    // If it's a relative path starting with uploads/ or images/
    if (imagePath.startsWith('uploads/') || imagePath.startsWith('images/')) {
        return `/${imagePath}`;
    }

    // Otherwise, assume it's in storage directory
    return `/storage/${imagePath}`;
}

// New function to open image in modal
function openImageModal(imageUrl: string) {
    selectedImageUrl.value = imageUrl;
    showImageModal.value = true;
}

// Update your handleImageError function to be more robust
function handleImageError(event: Event) {
    const img = event.target as HTMLImageElement;
    console.error('Failed to load image:', img.src);

    // Try alternative paths
    const originalSrc = img.src;

    // If the current src includes /storage/, try without it
    if (originalSrc.includes('/storage/')) {
        const newSrc = originalSrc.replace('/storage/', '/');
        if (newSrc !== originalSrc) {
            img.src = newSrc;
            return;
        }
    }

    // If it doesn't include /storage/, try with it
    if (!originalSrc.includes('/storage/')) {
        const fileName = originalSrc.split('/').pop();
        if (fileName) {
            img.src = `/storage/uploads/${fileName}`;
            return;
        }
    }

    // Finally, set a placeholder or hide the image
    img.style.display = 'none';

    // Optionally, show an error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'flex items-center justify-center h-32 bg-gray-100 rounded border text-gray-500 text-sm';
    errorDiv.innerHTML = `
        <div class="text-center">
            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>Image could not be loaded</p>
        </div>
    `;
    img.parentNode?.insertBefore(errorDiv, img);
}

// Debug function to check image data (you can call this in console)
function debugImageData(report: any) {
    console.log('Report data:', report);
    console.log('spotPicture:', report.spotPicture);
    console.log('Type of spotPicture:', typeof report.spotPicture);
    console.log('Is array?', Array.isArray(report.spotPicture));

    if (typeof report.spotPicture === 'string') {
        console.log('Processed URL:', getImageUrl(report.spotPicture));
    } else if (Array.isArray(report.spotPicture)) {
        report.spotPicture.forEach((img: string, index: number) => {
            console.log(`Image ${index}:`, img, '-> Processed:', getImageUrl(img));
        });
    }
}

function handleImageLoad(event: Event) {
    const img = event.target as HTMLImageElement;
    console.log('Image loaded successfully:', img.src);
}


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
                <h3 class="text-lg font-semibold text-blue-900">Welcome, {{ currentUser.first_name }} {{ currentUser.last_name }}</h3>
                <p class="text-sm text-blue-700">{{ getRoleDisplayName }}</p>
                <div class="text-sm text-blue-600">
                    <span v-if="currentUser.role === 'guard'">
                        You can create and view your own spot reports
                    </span>
                </div>
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
                <th class="px-6 py-3 text-left">Action Taken</th>
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
                <td class="px-6 py-4 font-semibold">{{ formatDateTime(item.created_at) }}</td>
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
                <!-- Guard Name (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Guard Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        v-model="newReport.guard_name"
                        readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none"
                    />
                </div>

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
                        v-model="newReport.action_taken"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': formErrors.action_taken }"
                        placeholder="Describe what action you took..."
                    ></textarea>
                    <p v-if="formErrors.action_taken" class="text-red-500 text-xs mt-1">{{ formErrors.action_taken }}</p>
                </div>

                <!-- Team Leader and Department Rep Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Team Leader <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="newReport.team_leader"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.team_leader }"
                        >
                            <option value="" disabled>Select a team leader</option>
                            <option value="Alice">Alice</option>
                            <option value="Bob">Bob</option>
                            <option value="Charlie">Charlie</option>
                        </select>
                        <p v-if="formErrors.team_leader" class="text-red-500 text-xs mt-1">{{ formErrors.team_leader }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Department Representative <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="newReport.department_representative"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.department_representative }"
                            placeholder="Department representative name"
                        />
                        <p v-if="formErrors.department_representative" class="text-red-500 text-xs mt-1">{{ formErrors.department_representative }}</p>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="mt-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Pictures (Optional)
                    </label>
                    <div class="flex justify-center">
                        <div class="w-full max-w-xs border-2 border-dashed border-gray-400 rounded-md p-6 flex flex-col items-center text-center">
                            <!-- Upload Icon -->
                            <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0-9l-3 3m3-3l3 3m-6-6a4 4 0 118 0 4 4 0 01-8 0z" />
                            </svg>

                            <!-- Upload Text -->
                            <p class="text-sm font-medium text-gray-700">Upload Pictures</p>
                            <p class="text-xs text-gray-500 mb-3">JPEG, JPG, PNG formats, up to 5MB each</p>

                            <!-- Browse Files -->
                            <label class="cursor-pointer inline-block px-4 py-1 bg-gray-200 rounded text-sm font-medium hover:bg-gray-300">
                                Browse Files
                                <input type="file" accept="image/*" multiple @change="handleImageUpload" class="hidden" />
                            </label>

                            <!-- Image Previews -->
                            <div v-if="imagePreviews.length" class="mt-4 grid grid-cols-2 gap-2">
                                <div v-for="(src, index) in imagePreviews" :key="index">
                                    <img :src="src" alt="Preview" class="h-24 rounded border" />
                                </div>
                            </div>
                        </div>
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
                        :disabled="newReport.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 disabled:opacity-50"
                    >
                        {{ newReport.processing ? 'Creating...' : 'Create Report' }}
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
                    <label class="text-lg font-bold block mb-2">Guard Name:</label>
                    <p class="text-gray-900">{{ selectedReport.guard_name || selectedReport.guardName }}</p>
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
                <div class="md:col-span-2">
                    <label class="text-lg font-bold block mb-2">Findings:</label>
                    <p class="text-gray-900">{{ selectedReport.findings }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="text-lg font-bold block mb-2">Action Taken:</label>
                    <p class="text-gray-900">{{ selectedReport.action_taken || selectedReport.actionTaken }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Team Leader:</label>
                    <p class="text-gray-900">{{ selectedReport.team_leader || selectedReport.teamLeader }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Department Representative:</label>
                    <p class="text-gray-900">{{ selectedReport.department_representative || selectedReport.departmentRepresentative }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="font-bold text-lg block mb-2">Evidence:</label>

                    <!-- Handle multiple images or single image -->
                    <div v-if="selectedReport.spotPicture && selectedReport.spotPicture.length > 0" class="space-y-4">
                        <!-- If incidentPicture is an array of images -->
                        <div v-if="Array.isArray(selectedReport.spotPicture)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(image, index) in selectedReport.spotPicture" :key="index" class="relative">
                                <img
                                    :src="getImageUrl(image)"
                                    :alt="`Evidence ${index + 1} for ${selectedReport.description}`"
                                    class="max-w-full h-auto rounded shadow border cursor-pointer hover:opacity-90 transition-opacity"
                                    @error="handleImageError"
                                    @load="handleImageLoad"
                                    @click="openImageModal(getImageUrl(image))"
                                />
                                <p class="text-xs text-gray-500 mt-1">Evidence {{ index + 1 }}</p>
                            </div>
                        </div>

                        <!-- If incidentPicture is a single image (string) -->
                        <div v-else class="relative inline-block">
                            <img
                                :src="getImageUrl(selectedReport.spotPicture)"
                                :alt="`Evidence for ${selectedReport.description}`"
                                class="max-w-full h-auto rounded shadow border cursor-pointer hover:opacity-90 transition-opacity"
                                @error="handleImageError"
                                @load="handleImageLoad"
                                @click="openImageModal(getImageUrl(selectedReport.spotPicture))"
                            />
                        </div>
                    </div>

                    <!-- No evidence message -->
                    <div v-else class="flex items-center justify-center h-32 bg-gray-50 rounded border-2 border-dashed border-gray-300">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No evidence images available</p>
                        </div>
                    </div>
                    <!-- Add this Image Modal for full-screen viewing (add after your main modal) -->
                    <div
                        v-if="showImageModal"
                        class="fixed inset-0 z-60 flex items-center justify-center bg-black bg-opacity-80 px-4"
                        @click.self="showImageModal = false"
                    >
                        <div class="relative max-w-4xl max-h-[90vh] overflow-hidden">
                            <button
                                @click="showImageModal = false"
                                class="absolute top-4 right-4 text-white hover:text-red-400 text-3xl font-bold focus:outline-none z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center"
                                title="Close"
                            >
                                &times;
                            </button>
                            <img
                                :src="selectedImageUrl"
                                alt="Full size evidence"
                                class="max-w-full max-h-full rounded shadow-2xl"
                                @error="handleImageError"
                            />
                        </div>
                    </div>
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
