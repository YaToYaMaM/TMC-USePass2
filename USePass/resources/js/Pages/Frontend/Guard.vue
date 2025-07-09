<script setup lang="ts">
import { ref, computed } from 'vue';
import  Frontend from "@/Layouts/FrontendLayout.vue";
import { Head } from "@inertiajs/vue3";


const showModal = ref(false);
const selectedImage = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const importFileInput = ref<HTMLInputElement | null>(null);
const selectedLocation = ref('Tagum');
const showEditModal = ref(false);
const guardToEdit = ref<{ id: number; name: string; title: string } | null>(null);

function submitForm() {
    alert('Guard saved!');
    showModal.value = false;
}
function handleImageUpload(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        selectedImage.value = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

    function triggerImport() {
        importFileInput.value?.click();
    }

    function handleImportFile(event: Event) {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (file) {
            alert(`Imported file: ${file.name}`);
            // You can add logic here to upload it or read its content
        }
}
// Mock guard list (you can fetch this from a backend later)
const guards = ref([
    { id: 1, name: "Froilan Canete", title: "Security Guard" },
    { id: 2, name: "Marvin Dela Cruz", title: "Security Guard" },
    { id: 3, name: "Carlos Reyes", title: "Security Guard" },
    { id: 4, name: "Ronald Sison", title: "Security Guard" },
    { id: 5, name: "Elmer Santos", title: "Security Guard" },
    { id: 6, name: "Benjie Ramos", title: "Security Guard" },
    { id: 7, name: "Jake Garcia", title: "Security Guard" },
]);

const currentPage = ref(1);
const guardsPerPage = 4;

const paginatedGuards = computed(() => {
    const start = (currentPage.value - 1) * guardsPerPage;
    return guards.value.slice(start, start + guardsPerPage);
});

const totalPages = computed(() =>
    Math.ceil(guards.value.length / guardsPerPage)
);

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}
function openEditModal(guard: { id: number; name: string; title: string }) {
    guardToEdit.value = { ...guard };
    showEditModal.value = true;
}

function updateGuard() {
    if (guardToEdit.value) {
        const index = guards.value.findIndex(g => g.id === guardToEdit.value?.id);
        if (index !== -1) {
            guards.value[index] = { ...guardToEdit.value };
            alert("Guard updated!");
        }
    }
    showEditModal.value = false;
}
</script>

<template>
    <Frontend>
        <Head title="Guard Page" />
        <div class="flex flex-col p-3 ">
            <!-- Top Control Buttons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <button @click="showModal = true" class="px-4 py-2 bg-white text-black border border-black rounded">+ Guard</button>

                <div class="flex flex-wrap items-center gap-2">
                    <button @click="triggerImport" class="px-3 py-1 text-sm bg-green-500 text-white rounded">Import</button>

                    <!-- Button Group -->
                    <div class="inline-flex justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
                        <button
                            @click="selectedLocation = 'Tagum'"
                            :class="[
        'px-3 py-1 text-sm border-r border-gray-300',
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
        'px-3 py-1 text-sm',
        selectedLocation === 'Mabini'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Mabini
                        </button>
                    </div>
                </div>

                <!-- Hidden file input -->
                <input
                    type="file"
                    ref="importFileInput"
                    @change="handleImportFile"
                    accept=".csv,.xlsx,.xls,.docx,.pdf"
                    class="hidden"
                />
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-xl font-bold mb-4">Security Guard</h1>
            <div class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto ml-96">
            <select class="border border-gray-300 p-2 rounded w-full md:w-24 text-sm">
                <option>Active</option>
                <option>Inactive</option>
            </select>
                <div class="relative w-full md:w-64">
                    <input
                        type="text"
                        placeholder="Search a student..."
                        class="w-full border border-gray-300 pl-10 pr-4 py-2 rounded-3xl focus:outline-none"
                    />
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <!-- Magnifying Glass SVG Icon -->
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            </div>
            <!-- Guard Card -->
            <div v-for="guard in paginatedGuards" :key="guard.id" class="bg-white rounded-lg shadow-md p-4 flex flex-col md:flex-row items-center justify-between mt-5 gap-4">
                <div class="flex items-center gap-4">
                    <img src="@/Icons/secguard.png" alt="Guard Image" class="h-14 w-14 rounded-full border" />
                    <div>
                        <h2 class="text-[18px] font-bold">{{ guard.name }}</h2>
                        <p class="text-sm text-gray-600">{{ guard.title }}</p>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <button @click="openEditModal(guard)" class="bg-green-500 text-sm text-white px-4 py-2 rounded">Edit</button>
                    <button class="bg-red-500 text-sm text-white px-4 py-2 rounded">Disable</button>
                </div>
            </div>


            <!-- Pagination Controls -->
            <div class="flex justify-center items-center mt-6 space-x-2">
                <button
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-1 rounded border bg-white hover:bg-gray-100 disabled:opacity-50"
                >
                    Prev
                </button>

                <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      'px-3 py-1 rounded border',
                      page === currentPage ? 'bg-blue-500 text-white' : 'bg-white hover:bg-gray-100'
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-3 py-1 rounded border bg-white hover:bg-gray-100 disabled:opacity-50"
                >
                    Next
                </button>
            </div>


            <!-- Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
                <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold mb-4">Add Guard</h2>

                    <!-- Form -->
                    <form @submit.prevent="submitForm">
                        <!-- Name Fields -->
                        <div class="mb-4 flex space-x-4">
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/6">
                                <label class="block text-sm font-medium mb-1">M.I</label>
                                <input type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                        </div>

                        <div class="mb-4 flex space-x-4">
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Gender</label>
                                <select class="w-full border border-gray-300 p-2 rounded" required>
                                    <option value="" disabled selected>Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">ID Number</label>
                                <input type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                        </div>
                        <div class="mb-4 flex space-x-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" class="w-full border border-gray-300 p-2 rounded" required />
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-medium mb-1">Confirm Password</label>
                            <input type="password" class="w-full border border-gray-300 p-2 rounded" required />
                        </div>
                        </div>

                        <div class="mb-4 flex justify-center">
                            <div
                                class="w-full max-w-xs border-2 border-dashed border-gray-400 rounded-md p-6 flex flex-col items-center text-center"
                            >
                                <!-- Upload Icon -->
                                <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0-9l-3 3m3-3l3 3m-6-6a4 4 0 118 0 4 4 0 01-8 0z" />
                                </svg>

                                <!-- Upload Text -->
                                <p class="text-sm font-medium text-gray-700">Upload a Profile Picture</p>
                                <p class="text-xs text-gray-500 mb-3">JPEG, JPG and PNG formats, up to 5MB</p>

                                <!-- Browse File -->
                                <label class="cursor-pointer inline-block px-4 py-1 bg-gray-200 rounded text-sm font-medium hover:bg-gray-300">
                                    Browse File
                                    <input type="file" accept="image/*" @change="handleImageUpload" class="hidden" required />
                                </label>

                                <!-- Image Preview (optional) -->
                                <div v-if="imagePreview" class="mt-4">
                                    <img :src="imagePreview" alt="Preview" class="h-24 rounded border" />
                                </div>
                            </div>
                        </div>


                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button type="submit" class="px-10 py-3 bg-green-500 text-white rounded">Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
                <div class="bg-white w-full max-w-2xl p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl font-bold mb-4">Edit Guard</h2>
                    <form @submit.prevent="updateGuard">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Name</label>
                            <input
                                type="text"
                                v-model="guardToEdit.name"
                                class="w-full border border-gray-300 p-2 rounded"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input
                                type="text"
                                v-model="guardToEdit.title"
                                class="w-full border border-gray-300 p-2 rounded"
                                required
                            />
                        </div>
                        <div class="mb-4">
                        <select class="w-full border border-gray-300 p-2 rounded">
                            <option value="" disabled selected>Select Role</option>
                            <option value="male">Add Guard</option>
                            <option value="female">Add Student</option>
                        </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                            <button type="submit" class="px-10 py-2 bg-blue-500 text-white rounded">Update</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </Frontend>
</template>


