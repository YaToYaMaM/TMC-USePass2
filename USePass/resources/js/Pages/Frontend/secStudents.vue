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
const showParentModal = ref(false);

function handleImageUpload(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        selectedImage.value = file;
        imagePreview.value = URL.createObjectURL(file);
        form.value.students_profile_image = file;
    }
}

function triggerImport() {
    importFileInput.value?.click();
}
function handleImportFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (file) {
        const formData = new FormData();
        formData.append('file', file);

        axios.post('/students/import', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })
            .then(response => {
                alert('Import successful: ' + response.data.message);

            })
            .catch(error => {
                console.error('Import error:', error);
                if (error.response?.status === 422) {
                    alert('Validation error: Invalid file format or content.');
                } else {
                    alert('Something went wrong during import.');
                }
            });
    }
}

const students = ref<any[]>([]);

const fetchStudents = () => {
    axios.get('/students/list')
        .then((response) => {
            students.value = response.data.map((student: any) => ({
                students_id: student.students_id,
                students_first_name: student.students_first_name,
                students_middle_initial: student.students_middle_initial,
                students_last_name: student.students_last_name,
                students_program: student.students_program,
                students_major: student.students_major,
                students_profile_image: student.students_profile_image,
            }));
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
        });
};

onMounted(() => {
    fetchStudents();
    setInterval(fetchStudents, 5000);
});


const form = ref({
    students_last_name: '',
    students_first_name: '',
    students_middle_initial: '',
    students_gender: '',
    students_id: '',
    students_program: '',
    students_major: '',
    students_unit: '',
    students_email: '',
    students_phone_num: '',
    students_profile_image: null,

    parent_last_name: '',
    parent_first_name: '',
    parent_middle_initial: '',
    parent_phone_num: '',
    parent_email: '',
    parent_relation: '',
});

const majorsByProgram = {
    'Information Technology': ['Information Security'],
    'Education': [
        'Elementary Education',
        'Early Childhood Education',
        'Special Needs Education',

    ],
    'Secondary Education':[
        'English',
        'Mathematics',
        'Filipino',
    ],
    'Engineering': [
        'Land and Water Resources',
        'Machinery and Power',
        'Process Engineering',
        'Structures and Environment',
    ],
    'TVL Teacher Education':[
        'Agricultural Crops Technology',
        'Animal Production',
    ],
};

const filteredMajors = computed(() => {
    return majorsByProgram[form.value.students_program] || [];
});

async function submitForm() {
    const formData = new FormData();
    for (const key in form.value) {
        if (form.value[key] !== null) {
            formData.append(key, form.value[key]);
        }
    }

    try {
        await axios.post('/students', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }).then(response => {
            console.log('Success', response);
        })
            .catch(error => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    console.error("Validation Errors:");
                    for (const field in errors) {
                        console.error(`${field}: ${errors[field].join(', ')}`);
                    }
                } else {
                    console.error("Other error:", error);
                }
            });
        await Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: 'Student and parent have been saved successfully.',
            confirmButtonColor: '#760000'
        });

        showModal.value = false;
        showParentModal.value = false;

        fetchStudents();
    }
    catch (error) {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            const errorMessages = Object.keys(errors)
                .map(field => `${field}: ${errors[field].join(', ')}`)
                .join('\n');

            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                html: `<pre class="text-left whitespace-pre-wrap">${errorMessages}</pre>`,
                confirmButtonColor: '#760000'
            });
        }
        else {
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong!',
                text: 'Please try again later.',
                confirmButtonColor: '#760000'
            });
        }

    }


}

const currentPage = ref(1);
const studentsPerPage = 4;


const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * studentsPerPage;
    const end = start + studentsPerPage;
    return filteredStudents.value.slice(start, end);
});

const totalPages = computed(() =>
    Math.ceil(students.value.length / studentsPerPage)
);

function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

function goToParentInfo() {
    showModal.value = false;
    showParentModal.value = true;
}

function backToStudentForm() {
    showParentModal.value = false;
    showModal.value = true;
}

const selectedProgram = ref('');
const searchQuery = ref('');


const filteredStudents = computed(() => {
    let result = students.value;

    if (selectedProgram.value) {
        const majors = majorsByProgram[selectedProgram.value] || [];
        result = result.filter(student =>
            majors.includes(student.students_major)
        );
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(student =>
            student.students_first_name.toLowerCase().includes(query) ||
            student.students_last_name.toLowerCase().includes(query)
        );
    }

    return result;
});

</script>

<template>
    <Frontend>
        <Head title="Student Page" />
        <div class="flex flex-col p-3 ">

            <!-- Top Control Buttons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <button @click="showModal = true" class="px-4 py-2 bg-white text-black border border-black rounded">+ Student</button>

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
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold">Students</h1>
                    <select class="border border-gray-300 p-2 w-full md:w-24 rounded text-sm">
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Disabled</option>
                    </select>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">

                    <div class="relative w-full md:w-64">
                        <input
                            v-model="searchQuery"
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
                    <select v-model="selectedProgram" class="border border-gray-300 p-2 w-fit min-w-[350px] text-sm rounded-lg">
                        <option value="">All Programs</option>
                        <option value="Information Technology" class="whitespace-nowrap" >Bachelor of Science in Information Technology</option>
                        <option value="Education" class="whitespace-nowrap">Bachelor of Education</option>
                        <option value="Secondary Education" class="whitespace-nowrap">Bachelor of Secondary Education</option>
                        <option value="TVL Teacher Education" class="whitespace-nowrap">Bachelor of Technical Vocational Teacher Education</option>
                        <option value="Engineering" class="whitespace-nowrap">Bachelor of Science in Agricultural Biosystem Engineering</option>
                    </select>
                </div>
            </div>
            <div v-if="filteredStudents.length === 0" class="text-gray-500 p-4 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                No records found.
            </div>
            <!-- student Card -->
            <div v-for="student in paginatedStudents" :key="student.students_id" class="bg-white rounded-lg shadow-md p-4 flex flex-col md:flex-row items-center justify-between mt-5 gap-4">
                <div class="flex items-center gap-4">
                    <img :src="`/${student.students_profile_image}`" class="h-14 w-14 rounded-full border" alt="Student Image" />
                    <div>
                        <h2 class="text-[18px] font-bold">
                            {{ student.students_first_name }} {{student.students_middle_initial}} {{ student.students_last_name }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            Bachelor of {{ student.students_program }} Major in
                            {{student.students_major}}
                        </p>
                    </div>
                </div>

                <div class="flex space-x-2">
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
            <form @submit.prevent="submitForm">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
                    <div class="bg-white w-full max-w-4xl p-6 rounded-lg shadow-lg  relative">
                        <button
                            @click="showModal = false"
                            class="absolute top-4 right-4 text-gray-500 hover:text-black text-2xl font-bold focus:outline-none"
                        >
                            &times;
                        </button>
                        <h2 class="text-xl font-bold mb-4">Add Student</h2>

                        <!-- Form -->

                        <!-- Name Fields -->
                        <div class="mb-4 flex space-x-4">
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input v-model="form.students_last_name" type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input v-model="form.students_first_name" type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/12">
                                <label class="block text-sm font-medium mb-1">M.I</label>
                                <input v-model="form.students_middle_initial" type="text" class="w-full border border-gray-300 p-2 rounded"  maxlength="1" required />
                            </div>
                            <div class="w-1/6">
                                <label class="block text-sm font-medium mb-1">Gender</label>
                                <select v-model="form.students_gender" class="w-full border border-gray-300 p-2 rounded" required>
                                    <option value="" disabled selected>Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 flex space-x-4">

                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">ID Number</label>
                                <input v-model="form.students_id" type="text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">Program</label>
                                <select v-model="form.students_program" class="w-full border border-gray-300 p-2 w-fit min-w-[155px] rounded" required>
                                    <option value="" disabled selected>Select Program</option>
                                    <option value="Information Technology">Information Technology</option>
                                    <option value="Education">Education</option>
                                    <option value="Secondary Education">Secondary Education</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="TVL Teacher Education">TVL Teacher Education</option>
                                </select>
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">Major</label>
                                <select v-model="form.students_major"
                                        class="w-full border border-gray-300 p-2 w-fit min-w-[155px] rounded"
                                        :disabled="!form.students_program" required>
                                    <option  value="" disabled selected>Select Major</option>
                                    <option v-for="major in filteredMajors" :key="major" :value="major">
                                        {{ major }}
                                    </option>

                                </select>
                            </div>
                            <div class="w-1/3">
                                <label class="block text-sm font-medium mb-1">Unit</label>
                                <select v-model="form.students_unit" class="w-full border border-gray-300 p-2 w-fit min-w-[100px] rounded" required>
                                    <option value="" disabled selected>Select Unit</option>
                                    <option value="Tagum">Tagum</option>
                                    <option value="Mabini">Mabini</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4 flex space-x-4">
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input v-model="form.students_email" type="Text" class="w-full border border-gray-300 p-2 rounded" required />
                            </div>
                            <div class="w-1/2">
                                <label class="block text-sm font-medium mb-1">Contact Number</label>
                                <input v-model="form.students_phone_num" type="text" class="w-full border border-gray-300 p-2 rounded" required />
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
                            <button type="button" @click="goToParentInfo" class="px-10 py-3 bg-blue-500 text-white rounded">Next</button>
                        </div>



                    </div>
                </div>
                <!-- Parent Info Modal -->
                <div v-if="showParentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
                    <div class="bg-white w-full max-w-lg rounded-xl shadow-lg p-4">

                        <!-- Modal Header with Back Icon -->
                        <div class="flex items-center border-b pb-2 mb-4">
                            <button @click="backToStudentForm" class="mr-2 text-gray-600 hover:text-black">
                                <!-- Back arrow SVG -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <h2 class="text-lg font-semibold text-center flex-1">Parent credentials</h2>
                        </div>

                        <!-- Form Content -->
                        <div>
                            <p class="text-sm font-medium mb-2">Parent/Guardian</p>

                            <div class="mb-2 flex gap-2">
                                <input v-model="form.parent_last_name" type="text" placeholder="Last Name" class="w-1/3 border border-gray-300 p-2 rounded text-sm" required />
                                <input v-model="form.parent_first_name" type="text" placeholder="First Name" class="w-1/3 border border-gray-300 p-2 rounded text-sm" required />
                                <input v-model="form.parent_middle_initial" type="text" placeholder="M.I" class="w-1/6 border border-gray-300 p-2 rounded text-sm"  maxlength="1" />
                            </div>

                            <div class="mb-4 flex gap-2">
                                <input v-model="form.parent_phone_num" type="text" placeholder="Contact #" class="w-1/3 border border-gray-300 p-2 rounded text-sm" required />
                                <input v-model="form.parent_email" type="text" placeholder="Email" class="w-1/2 border border-gray-300 p-2 rounded text-sm" required />
                                <select v-model="form.parent_relation" class="w-1/4 border border-gray-300 p-2 rounded text-sm" required>
                                    <option value="" disabled selected>Relationship</option>
                                    <option value="father">Father</option>
                                    <option value="mother">Mother</option>
                                    <option value="guardian">Guardian</option>
                                </select>
                            </div>

                            <div class="text-center">
                                <button
                                    type="submit"
                                    class="px-4 py-1 bg-green-500 text-white text-sm font-medium rounded hover:bg-green-600 transition"
                                >
                                    CONFIRM
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>


        </div>
    </Frontend>
</template>

