<script setup>
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import axios from 'axios';

const loading = ref(false);
const error = ref(null);
const profileImage = ref(null);
const profileImagePreview = ref('');
const showSuccess = ref(false);
const showQRCode = ref(false);
const facultyData = ref(null);

const form = ref({
    faculty_id: '',
    faculty_first_name: '',
    faculty_last_name: '',
    faculty_middle_initial: '',
    faculty_gender: '',
    faculty_college: '',
    faculty_department: '',
    faculty_unit: '',
    faculty_email: '',
    faculty_phone_num: ''
});

const departmentData = {
    "College of Teacher Education and Technology (CTET)": [
        "Bachelor of Early Childhood Education Department",
        "Bachelor of Elementary Education Department",
        "Bachelor of Secondary Education - English Department",
        "Bachelor of Secondary Education - Filipino Department",
        "Bachelor of Secondary Education - Mathematics Department",
        "Bachelor of Science in Information Technology Department",
        "Bachelor of Special Needs Education Department",
        "Bachelor of Technical-Vocational Teacher Education Department",
        "General Education Department",
        "Graduate School - Doctor of Education Department",
        "Graduate School - Master of Education in Language Teaching Department",
        "Graduate School - Master of Education in Educational Management Department"
    ],
    "School of Medicine (SoM)": [
        "Medical Faculty",
        "Administrative Staff",
        "Laboratory Services",
        "Support Staff"
    ],
    "College of Agriculture and Related Sciences (CARS)": [
        "Agriculture Program",
        "Animal Science Program",
        "Crop Protection Program",
        "Environmental Science Program",
        "Food Science Program",
        "Forestry Program",
        "Horticulture Program",
        "Soil Science Program",
        "Agricultural Extension Program",
        "Administrative Staff",
        "Support Staff"
    ]
};

const availableDepartments = computed(() => {
    return form.value.faculty_college ? departmentData[form.value.faculty_college] || [] : [];
});

const isFormValid = computed(() => {
    return form.value.faculty_id.trim() &&
        form.value.faculty_first_name.trim() &&
        form.value.faculty_last_name.trim() &&
        form.value.faculty_gender &&
        form.value.faculty_college &&
        form.value.faculty_department.trim() &&
        form.value.faculty_unit &&
        form.value.faculty_email.trim() &&
        form.value.faculty_phone_num.trim();
});

const onCollegeChange = () => {
    form.value.faculty_department = '';
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file.');
        event.target.value = '';
        return;
    }

    if (file.size > 5120 * 1024) {
        alert('Image size must be less than 5MB.');
        event.target.value = '';
        return;
    }

    profileImage.value = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        profileImagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    profileImage.value = null;
    profileImagePreview.value = '';
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
};

const submitRegistration = async () => {
    if (!isFormValid.value) {
        error.value = 'Please fill in all required fields.';
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const formData = new FormData();

        // Add all form fields
        Object.keys(form.value).forEach(key => {
            formData.append(key, form.value[key]);
        });

        // Add profile image if exists
        if (profileImage.value) {
            formData.append('profile_image', profileImage.value);
        }

        const response = await axios.post('/faculty/send-otp', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.data.success) {
            // Redirect to OTP verification
            router.visit('/faculty/otp/verify', {
                method: 'get',
                replace: true
            });
        }
    } catch (err) {

        if (err.response?.data?.error) {
            // Handle single error message
            error.value = formatErrorMessage(err.response.data.error);
        } else if (err.response?.data?.errors) {
            // Handle validation errors object
            const errors = err.response.data.errors;
            error.value = formatValidationErrors(errors);
        } else if (err.response?.status === 422) {
            error.value = 'Please check your input data and try again.';
        } else if (err.response?.status >= 500) {
            // Handle server errors
            error.value = 'Server error occurred. Please try again later.';
        } else {
            error.value = 'Registration failed. Please check your connection and try again.';
        }
    } finally {
        loading.value = false;
    }
};


const formatErrorMessage = (message) => {
    // Convert backend messages
    if (message.includes('Faculty ID') && message.includes('already registered')) {
        return 'This Faculty ID is already in use. Please use a different Faculty ID.';
    }
    if (message.includes('email') && message.includes('already registered')) {
        return 'This email address is already registered. Please use a different email.';
    }
    return message;
};

const formatValidationErrors = (errors) => {
    const userFriendlyMessages = {
        'faculty_id': 'Faculty ID issue',
        'faculty_email': 'Email address issue',
        'faculty_first_name': 'First name issue',
        'faculty_last_name': 'Last name issue',
        'faculty_college': 'College selection issue',
        'faculty_department': 'Department issue',
        'faculty_unit': 'Unit selection issue',
        'faculty_phone_num': 'Phone number issue',
        'profile_image': 'Profile image issue'
    };

    const formattedErrors = [];

    Object.keys(errors).forEach(field => {
        const fieldErrors = errors[field];
        const fieldName = userFriendlyMessages[field] || field;

        fieldErrors.forEach(errorMsg => {
            if (errorMsg.includes('already')) {
                if (field === 'faculty_id') {
                    formattedErrors.push('Faculty ID is already registered. Please use a different ID.');
                } else if (field === 'faculty_email') {
                    formattedErrors.push('Email address is already registered. Please use a different email.');
                }
            } else {
                formattedErrors.push(`${fieldName}: ${errorMsg}`);
            }
        });
    });

    return formattedErrors.length > 0 ? formattedErrors.join(' | ') : 'Please check your input and try again.';
};

const generateQRCode = async () => {
    if (!facultyData.value?.faculty_id) return;

    await nextTick();

    try {
        const canvas = document.querySelector('#faculty-qrcode');
        if (!canvas) {
            setTimeout(() => generateQRCode(), 500);
            return;
        }

        await QRCode.toCanvas(canvas, facultyData.value.faculty_id, {
            width: 200,
            height: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });
    } catch (error) {
        console.error('QR code generation error:', error);
    }
};

const downloadQRCode = () => {
    const canvas = document.querySelector('#faculty-qrcode');
    if (!canvas) {
        alert('QR code not ready yet');
        return;
    }

    const link = document.createElement('a');
    link.download = `faculty_qrcode_${facultyData.value.faculty_id}.png`;
    link.href = canvas.toDataURL();
    link.click();
};

const registerAnother = () => {
    // Reset form
    Object.keys(form.value).forEach(key => {
        form.value[key] = '';
    });
    profileImage.value = null;
    profileImagePreview.value = '';
    showQRCode.value = false;
    showSuccess.value = false;
    facultyData.value = null;
    error.value = null;
};

// Handle successful registration from OTP verification
const handleRegistrationSuccess = (data) => {
    showSuccess.value = true;
    showQRCode.value = true;
    facultyData.value = data;

    setTimeout(() => {
        generateQRCode();
    }, 100);
};

// Check if coming back from successful OTP verification
if (window.location.search.includes('success=true')) {
    // This would be handled by the OTP component redirecting back with data
}
</script>


<template>
    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 z-10 flex flex-col items-center mt-2 text-white">
            <div class="relative z-10 p-2 rounded-lg text-center max-w-lg w-full mb-20">
                <img
                    src="/images/logo3.png"
                    alt="USePass Logo"
                    class="mx-auto mb-7 w-[250px] min-w-64 h-auto"
                />
            </div>
        </div>

        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Registration Form -->
        <div class="relative z-10 mt-40 bg-white bg-opacity-90 rounded-md shadow-md p-6 max-w-4xl mx-auto space-y-6 mb-8">

            <!-- Success Message -->
            <div v-if="showSuccess" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Registration completed successfully! You can now download your QR code.
                </div>
            </div>

            <!-- QR Code Display -->
            <div v-if="showQRCode" class="text-center mb-6">
                <h3 class="font-bold mb-4 text-green-600">✓ Registration Complete!</h3>
                <div class="border-2 border-gray-300 rounded p-4 mb-4 inline-block">
                    <canvas id="faculty-qrcode" width="200" height="200"></canvas>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Faculty ID: <strong>{{ facultyData?.faculty_id }}</strong>
                </p>
                <div class="space-y-3">
                    <button
                        @click="downloadQRCode"
                        class="bg-red-700 hover:bg-red-800 text-white px-6 py-2 text-sm rounded font-medium"
                    >
                        Download QR Code
                    </button>
                    <br>
                    <button
                        @click="registerAnother"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 text-sm rounded font-medium"
                    >
                        Register Another Faculty
                    </button>
                </div>
            </div>

            <!-- Registration Form -->
            <form v-if="!showQRCode" @submit.prevent="submitRegistration" class="space-y-4">

                <!-- Personal Information -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Personal Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">First Name*</label>
                            <input
                                type="text"
                                v-model="form.faculty_first_name"
                                placeholder="Enter first name"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Last Name*</label>
                            <input
                                type="text"
                                v-model="form.faculty_last_name"
                                placeholder="Enter last name"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Middle Initial</label>
                            <input
                                type="text"
                                v-model="form.faculty_middle_initial"
                                placeholder="M.I."
                                maxlength="1"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                :disabled="loading"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Faculty/Staff ID*</label>
                            <input
                                type="text"
                                v-model="form.faculty_id"
                                placeholder="Enter Faculty ID"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Gender*</label>
                            <select
                                v-model="form.faculty_gender"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            >
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Unit*</label>
                            <select
                                v-model="form.faculty_unit"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            >
                                <option value="">Select Unit</option>
                                <option value="Tagum">Tagum</option>
                                <option value="Mabini">Mabini</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Professional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- College Selection -->
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">College*</label>
                            <select
                                v-model="form.faculty_college"
                                @change="onCollegeChange"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            >
                                <option value="">Select College</option>
                                <option value="College of Teacher Education and Technology (CTET)">
                                    College of Teacher Education and Technology (CTET)
                                </option>
                                <option value="School of Medicine (SoM)">
                                    School of Medicine (SoM)
                                </option>
                                <option value="College of Agriculture and Related Sciences (CARS)">
                                    College of Agriculture and Related Sciences (CARS)
                                </option>
                            </select>
                        </div>

                        <!-- Department Selection -->
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Department/Program*</label>
                            <select
                                v-model="form.faculty_department"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading || !form.faculty_college"
                            >
                                <option value="">
                                    {{ form.faculty_college ? 'Select Department/Program' : 'Select College first' }}
                                </option>
                                <option
                                    v-for="department in availableDepartments"
                                    :key="department"
                                    :value="department"
                                >
                                    {{ department }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!--Show selected info -->
                    <div v-if="form.faculty_college && form.faculty_department"
                         class="mt-3 text-sm text-gray-600 bg-blue-50 p-3 rounded-md border-l-4 border-blue-400">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong>Selected:</strong> {{ form.faculty_department }}<br>
                                <span class="text-gray-500 text-xs">{{ form.faculty_college }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="font-bold text-lg mb-4 text-gray-800">Contact Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Email Address*</label>
                            <input
                                type="email"
                                v-model="form.faculty_email"
                                placeholder="Enter email address"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">Phone Number*</label>
                            <input
                                type="tel"
                                v-model="form.faculty_phone_num"
                                placeholder="Enter phone number"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                    </div>
                </div>

                <!-- Profile Image -->
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700">Profile Image</label>

                    <div class="mb-4" v-if="profileImagePreview">
                        <div class="relative inline-block">
                            <img
                                :src="profileImagePreview"
                                alt="Profile Preview"
                                class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300"
                            />
                            <button
                                @click="removeImage"
                                type="button"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                            >
                                ×
                            </button>
                        </div>
                    </div>

                    <input
                        type="file"
                        accept="image/*"
                        @change="handleImageUpload"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        :disabled="loading"
                    />
                    <p class="text-xs text-gray-500 mt-1">Maximum file size: 5MB. Supported formats: JPG, PNG</p>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-md border-l-4 border-red-400">
                    {{ error }}
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="loading || !isFormValid"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-md font-semibold transition"
                    >
                        {{ loading ? 'Sending OTP...' : 'Register Faculty' }}
                    </button>
                </div>

                <p class="text-sm text-gray-600 text-center">
                    All fields marked with * are required. An OTP will be sent to your email for verification.
                </p>
            </form>
        </div>
    </div>
</template>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
select:disabled {
    background-color: #f9fafb;
    cursor: not-allowed;
    opacity: 0.6;
}
.bg-blue-50 {
    background-color: #eff6ff;
}
.space-y-4 > * {
    transition: all 0.2s ease-in-out;
}
</style>

