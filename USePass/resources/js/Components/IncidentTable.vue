<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

// Import Inertia's form helper for adding new reports
import { useForm } from '@inertiajs/vue3';

const selectedImages = ref<File[]>([]);
const imagePreviews = ref<string[]>([]);

function handleImageUpload(event: Event) {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
        selectedImages.value = Array.from(files);
        imagePreviews.value = selectedImages.value.map(file => URL.createObjectURL(file));

        // Optional: Assign to your report field (if needed to send to backend)
        newReport.incidentPicture = selectedImages.value; // ✅ no `.value` needed for useForm

    }
}


// Define the User interface with role property
interface User {
    id: number;
    first_name: string;
    last_name: string;
    email?: string;
    role: 'admin' | 'guard' | 'user';
    name?: string; // Add name property for compatibility
}

const page = usePage();
// Fix the type assertion - remove the redundant intersection type
const currentUser = computed(() => page.props.auth.user as User);

const props = defineProps<{
    reports: any[];
    selectedDate?: string;
}>();

const reports = ref(props.reports);
// const filteredReports = computed(() => {
//     return props.reports?.filter(report => {
//         // your filtering condition
//         return true; // modify as needed
//     }) ?? [];
// });

// Utility function to format date as "Date | Time"
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
// const newReport = ref({
//     description: '',
//     // type: 'Incident Report',
//     date: '',
//     what: '',
//     who: '',
//     where: '',
//     when: '',
//     how: '',
//     why: '',
//     recommendation: '',
//     incidentPicture: null,
// });

const newReport = useForm({
    description: '',
    type: 'Incident Report',
    date: new Date().toISOString().split('T')[0],
    what: '',
    who: '',
    where: '',
    when: '',
    how: '',
    why: '',
    recommendation: '',
    incidentPicture: null, // This can be an array if you're uploading multiple
});

// Form validation errors
const formErrors = ref<Record<string, string>>({});

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
// function resetForm() {
//     newReport.value = {
//         description: '',
//         type: 'Incident Report',
//         date: new Date().toISOString().split('T')[0], // Set current date as default
//         what: '',
//         who: '',
//         where: '',
//         when: '',
//         how: '',
//         why: '',
//         recommendation: '',
//         incidentPicture: null,
//     };
//     formErrors.value = {};
// }
function resetForm() {
    newReport.reset(); // ✅ This is the correct way
    selectedImages.value = [];
    imagePreviews.value = [];
    formErrors.value = {};
}


// Validate form
function validateForm(): boolean {
    formErrors.value = {};

    if (!newReport.description.trim()) {
        formErrors.value.description = 'Description is required';
    }

    if (!newReport.date.trim()) {
        formErrors.value.date = 'Date is required';
    }

    if (!newReport.what.trim()) {
        formErrors.value.what = 'What field is required';
    }

    if (!newReport.who.trim()) {
        formErrors.value.who = 'Who field is required';
    }

    if (!newReport.where.trim()) {
        formErrors.value.where = 'Where field is required';
    }

    if (!newReport.when.trim()) {
        formErrors.value.when = 'When field is required';
    }

    if (!newReport.how.trim()) {
        formErrors.value.how = 'How field is required';
    }

    if (!newReport.why.trim()) {
        formErrors.value.why = 'Why field is required';
    }

    if (!newReport.recommendation.trim()) {
        formErrors.value.recommendation = 'Recommendation is required';
    }

    return Object.keys(formErrors.value).length === 0;
}

// Submit new report
// Submit new report
function submitReport() {
    if (!validateForm()) {
        return;
    }

    if (!currentUser.value) {
        alert('User not authenticated');
        return;
    }

    // ✅ Store the form data before submission (since form gets reset)
    const formData = {
        description: newReport.description,
        what: newReport.what,
        who: newReport.who,
        where: newReport.where,
        when: newReport.when,
        how: newReport.how,
        why: newReport.why,
        recommendation: newReport.recommendation,
        type: newReport.type,
        date: newReport.date
    };

    newReport.post('/incident-report', {
        forceFormData: true,
        onSuccess: (page) => {
            showAddModal.value = false;

            // ✅ If server returns the new report, add it to the local array
            if (page.props && page.props.newReport) {
                reports.value.unshift(page.props.newReport);
            } else {
                // ✅ Fallback: manually create the report object using stored form data
                const newReportData = {
                    id: Date.now(), // temporary ID
                    guard_name: currentUser.value.name || `${currentUser.value.first_name} ${currentUser.value.last_name}`,
                    user_id: currentUser.value.id,
                    created_at: new Date().toISOString(),
                    // ✅ Use the stored form data instead of the reset form
                    description: formData.description,
                    what: formData.what,
                    who: formData.who,
                    where: formData.where,
                    when: formData.when,
                    how: formData.how,
                    why: formData.why,
                    recommendation: formData.recommendation,
                    type: formData.type,
                    date: formData.date
                };
                reports.value.unshift(newReportData);
            }

            // ✅ Reset form after updating the reports array
            resetForm();
            alert('Incident report created successfully!');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            formErrors.value = errors;
        }
    });
}

function printReport() {
    if (!selectedReport.value || !canViewReport(selectedReport.value)) {
        alert("You don't have permission to print this report.");
        return;
    }

    const reportParams = {
        id: selectedReport.value.id,
        name: selectedReport.value.name,
        type: selectedReport.value.type,
        date: selectedReport.value.date,
        what: selectedReport.value.what,
        who: selectedReport.value.who,
        where: selectedReport.value.where,
        when: selectedReport.value.when,
        how: selectedReport.value.how,
        why: selectedReport.value.why,
        incidentPicture: selectedReport.value.incidentPicture,
        recommendation: selectedReport.value.recommendation
    };

    const query = new URLSearchParams(reportParams).toString();
    window.open(`/incident-report/print?${query}`, '_blank');
}

const currentPage = ref(1);
const itemsPerPage = 4;

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
    console.log('incidentPicture:', report.incidentPicture);
    console.log('Type of incidentPicture:', typeof report.incidentPicture);
    console.log('Is array?', Array.isArray(report.incidentPicture));

    if (typeof report.incidentPicture === 'string') {
        console.log('Processed URL:', getImageUrl(report.incidentPicture));
    } else if (Array.isArray(report.incidentPicture)) {
        report.incidentPicture.forEach((img: string, index: number) => {
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

console.log('Reports received:', props.reports);

</script>

<template>
    <!-- User Info Header -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4" v-if="currentUser">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">Welcome, {{ currentUser.first_name }} {{ currentUser.last_name }}</h3>
                <p class="text-sm text-blue-700">{{ getRoleDisplayName }}</p>
            </div>
            <div class="text-sm text-blue-600">
                <span v-if="currentUser.role === 'guard'">
                    You can create and view your own reports
                </span>
            </div>
        </div>
    </div>

    <!-- Add Incident Report Button -->
    <div class="mb-4">
        <button
            v-if="currentUser?.role === 'guard'"
            @click="openAddModal"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition duration-200"
        >
            + Incident Report
        </button>
    </div>

    <div class="overflow-x-auto mt-6 rounded-lg shadow border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white text-sm text-left">
            <thead class="bg-gray-50">
            <tr class="text-gray-600 uppercase text-xs tracking-wider">
                <th class="px-6 py-3 text-left">Guard on Duty</th>
                <th class="px-6 py-3 text-left">What</th>
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-left">Where</th>
                <th class="px-6 py-3 text-left">Date | Time</th>
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
                <td class="px-6 py-4 font-semibold">{{ item.what }}</td>
                <td class="px-6 py-4 font-semibold">{{ item.description }}</td>
                <td class="px-6 py-4 font-semibold">{{ item.where }}</td>
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
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
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
                        <p class="text-lg font-medium">No reports available</p>
                        <p class="text-sm">
                            {{ currentUser && currentUser.role === "guard"
                            ? "You haven't created any reports yet."
                            : "No reports match your current filter." }}
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
        <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl p-6 relative max-h-[90vh] overflow-y-auto">
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
                Create New Incident Report
            </h2>

            <!-- Form -->
            <form @submit.prevent="submitReport" class="space-y-6">
                <!-- Description and Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="newReport.description"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.description }"
                            placeholder="Brief description of the incident"
                        />
                        <p v-if="formErrors.description" class="text-red-500 text-xs mt-1">{{ formErrors.description }}</p>
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

                <!-- 5W1H Section -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Incident Details</h3>

                    <!-- What -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            What? <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="newReport.what"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.what }"
                            placeholder="Describe exactly what happened..."
                        ></textarea>
                        <p v-if="formErrors.what" class="text-red-500 text-xs mt-1">{{ formErrors.what }}</p>
                    </div>

                    <!-- Who and Where Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Who was involved? <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="newReport.who"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': formErrors.who }"
                                placeholder="People involved in the incident"
                            />
                            <p v-if="formErrors.who" class="text-red-500 text-xs mt-1">{{ formErrors.who }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Where did it happen? <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="newReport.where"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': formErrors.where }"
                                placeholder="Location of the incident"
                            />
                            <p v-if="formErrors.where" class="text-red-500 text-xs mt-1">{{ formErrors.where }}</p>
                        </div>
                    </div>

                    <!-- When -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            When did it happen? <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="newReport.when"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.when }"
                            placeholder="Time and date details (e.g., 2:30 PM, July 8, 2025)"
                        />
                        <p v-if="formErrors.when" class="text-red-500 text-xs mt-1">{{ formErrors.when }}</p>
                    </div>

                    <!-- How -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            How did it happen? <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="newReport.how"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.how }"
                            placeholder="Describe the sequence of events or method..."
                        ></textarea>
                        <p v-if="formErrors.how" class="text-red-500 text-xs mt-1">{{ formErrors.how }}</p>
                    </div>

                    <!-- Why -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Why did it happen? <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="newReport.why"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.why }"
                            placeholder="Analyze the root cause or contributing factors..."
                        ></textarea>
                        <p v-if="formErrors.why" class="text-red-500 text-xs mt-1">{{ formErrors.why }}</p>
                    </div>

                    <!-- Recommendation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Recommendations <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="newReport.recommendation"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': formErrors.recommendation }"
                            placeholder="Suggest preventive measures or corrective actions..."
                        ></textarea>
                        <p v-if="formErrors.recommendation" class="text-red-500 text-xs mt-1">{{ formErrors.recommendation }}</p>
                    </div>

                    <!-- Add picture -->
                    <div class="mt-5 flex justify-center">
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
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button
                        type="button"
                        @click="showAddModal = false"
                        class="px-6 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200"
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
                {{ selectedReport.type === 'Incident Report' ? 'Incident Report Details' : 'Incident Report Details' }}
                <span
                    v-if="currentUser && selectedReport.user_id === currentUser.id && currentUser.role === 'guard'"
                    class="text-sm bg-green-100 text-green-800 px-2 py-1 rounded ml-2"
                >Your Report</span>
            </h2>

            <!-- Report Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm text-gray-700">
                <div>
                    <label class="text-lg font-bold block mb-2">What:</label>
                    <p class="text-gray-900">{{ selectedReport.what }}</p>
                </div>
                <div>
                    <label class="text-lg font-bold block mb-2">Who:</label>
                    <p class="text-gray-900">{{ selectedReport.who }}</p>
                </div>
                <div>
                    <label class="text-lg font-bold block mb-2">Where:</label>
                    <p class="text-gray-900">{{ selectedReport.where }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">When:</label>
                    <p class="text-gray-900">{{ selectedReport.when }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">How:</label>
                    <p class="text-gray-900">{{ selectedReport.how }}</p>
                </div>
                <div>
                    <label class="font-bold text-lg block mb-2">Why:</label>
                    <p class="text-gray-900">{{ selectedReport.why }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="font-bold text-lg block mb-2">Recommendation:</label>
                    <p class="text-gray-900">{{ selectedReport.recommendation }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="font-bold text-lg block mb-2">Evidence:</label>

                    <!-- Handle multiple images or single image -->
                    <div v-if="selectedReport.incidentPicture && selectedReport.incidentPicture.length > 0" class="space-y-4">
                        <!-- If incidentPicture is an array of images -->
                        <div v-if="Array.isArray(selectedReport.incidentPicture)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(image, index) in selectedReport.incidentPicture" :key="index" class="relative">
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
                                :src="getImageUrl(selectedReport.incidentPicture)"
                                :alt="`Evidence for ${selectedReport.description}`"
                                class="max-w-full h-auto rounded shadow border cursor-pointer hover:opacity-90 transition-opacity"
                                @error="handleImageError"
                                @load="handleImageLoad"
                                @click="openImageModal(getImageUrl(selectedReport.incidentPicture))"
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
