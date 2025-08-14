<script setup lang="ts">
import { ref, computed, onMounted  } from 'vue';
import  Frontend from "@/Layouts/FrontendLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from 'axios';
import Swal from 'sweetalert2';


const showModal = ref(false);
const selectedImage = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const importFileInput = ref<HTMLInputElement | null>(null);
const selectedLocation = ref('Tagum');
const showEditModal = ref(false);
const guardToEdit = ref<{
    id: number;
    first_name: string;
    last_name: string;
    contact_number: string;
    email?: string;
} | null>(null);


function handleImageUpload(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        selectedImage.value = file;
        imagePreview.value = URL.createObjectURL(file);
        form.value.profile_image = file;
    }
}



    function handleImportFile(event: Event) {
        const file = (event.target as HTMLInputElement).files?.[0];
        if (file) {
            alert(`Imported file: ${file.name}`);
            // You can add logic here to upload it or read its content
        }
}

const guards = ref<any[]>([]);


const form = ref({
    last_name: '',
    first_name: '',
    middle_initial: '',
    role: 'guard',
    contact_number: '',
    email: '',
    password: '',
    password_confirmation: '',
    profile_image: null,

});

const fetchguard = () => {
    axios.get('/guard/list')
        .then((response) => {
            guards.value = response.data.map((guard: any) => ({
                id: guard.id,
                first_name: guard.first_name,
                middle_initial: guard.middle_initial,
                last_name: guard.last_name,
                contact_number: guard.contact_number,
                profile_image: guard.profile_image,
            }));
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
        });
};

onMounted(() => {
    fetchguard();
     setInterval(fetchguard, 5000);
});
async function submitForm() {
    const formData = new FormData();
    for (const key in form.value) {
        if (form.value[key] !== null) {
            formData.append(key, form.value[key]);
        }
    }

    try {
        await axios.post('/users', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        // ✅ Sweet success alert
        await Swal.fire({
            icon: 'success',
            title: 'Guard Added!',
            text: 'Guard has been successfully added.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'center',
        });

        showModal.value = false;
        fetchguard();

    } catch (error) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            let errorList = '';
            for (const field in errors) {
                errorList += `${errors[field].join(', ')}\n`;
            }

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: errorList,
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: 'Please try again later.',
            });
            console.error("Other error:", error);
        }
    }
}

const searchQuery = ref('');
const filteredGuards = computed(() => {
    let result = guards.value;

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(guard =>
            guard.first_name.toLowerCase().includes(query) ||
            guard.last_name.toLowerCase().includes(query)
        );
    }

    return result;
});

const currentPage = ref(1);
const guardsPerPage = 4;

const paginatedGuards = computed(() => {
    const start = (currentPage.value - 1) * guardsPerPage;
    const end = start + guardsPerPage;
    return filteredGuards.value.slice(start, end);
});

const totalPages = computed(() =>
    Math.ceil(guards.value.length / guardsPerPage)
);

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}
function openEditModal(guard: any) {
    guardToEdit.value = { ...guard };
    showEditModal.value = true;
}

async function updateGuard() {
    if (guardToEdit.value) {
        try {
            await axios.put(`/guard/${guardToEdit.value.id}`, guardToEdit.value);

            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Guard information updated successfully.',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'center',
            });

            fetchguard(); // Refresh the list from backend
            showEditModal.value = false;
        } catch (error) {
            console.error("Update failed:", error);

            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Something went wrong while updating the guard.',
            });
        }
    }
}

</script>

<template>
    <Head title="USePass" />
    <Frontend>
        <Head title="Guard Page" />
        <div class="flex flex-col p-3 px-12 pt-10">
            <!-- Top Control Buttons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <button @click="showModal = true" class="px-4 py-2 bg-white text-black border border-black rounded">+ Guard</button>

                <div class="flex flex-wrap items-center gap-2">

                    <div class="inline-flex py-1 px-1 justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
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
            <div class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
            <select class="border border-gray-300 p-2 rounded w-full md:w-24 text-sm">
                <option>Active</option>
                <option>Inactive</option>
            </select>
                <div class="relative w-full md:w-64">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search a Guard..."
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
                    <img :src="`/${guard.profile_image} || '/guard_profiles/guard_image.png'`"
                         @error="(e) => ((e.target as HTMLImageElement).src = '/guard_profiles/guard_image.png')"
                         alt="Guard Image" class="h-14 w-14 rounded-full border" />
                    <div>
                        <h2 class="text-[18px] font-bold">  {{ guard.first_name }} {{guard.middle_initial}} {{ guard.last_name }}</h2>
                        <p class="text-sm text-gray-600">Security Guard</p>
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
                                <input v-model="form.last_name" type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input v-model="form.first_name" type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/6">
                                <label class="block text-sm font-medium mb-1">M.I</label>
                                <input v-model="form.middle_initial" type="text" class="w-full border border-gray-300 p-2 rounded" maxlength="1" required />
                            </div>
                        </div>

                        <div class="mb-4 flex space-x-4">
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Contact</label>
                                <input v-model="form.contact_number" type="text" class="w-full border border-gray-300 p-2 rounded" maxlength="11" required />
                            </div>
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input v-model="form.email" type="email" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                        </div>
                        <div class="mb-4 flex space-x-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input v-model="form.password" type="password" class="w-full border border-gray-300 p-2 rounded" required />
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-medium mb-1">Confirm Password</label>
                            <input v-model="form.password_confirmation"  type="password" class="w-full border border-gray-300 p-2 rounded" required />
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
                            <label class="block text-sm font-medium mb-1">First Name</label>
                            <input
                                type="text"
                                v-model="guardToEdit.first_name"
                                class="w-full border border-gray-300 p-2 rounded"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Last Name</label>
                            <input
                                type="text"
                                v-model="guardToEdit.last_name"
                                class="w-full border border-gray-300 p-2 rounded"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Contact Number</label>
                            <input
                                type="text"
                                v-model="guardToEdit.contact_number"
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


