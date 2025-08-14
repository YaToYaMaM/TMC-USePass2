<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from "vue";
import { router } from '@inertiajs/vue3';
import QRCode from "qrcode";
import axios from 'axios';

const props = defineProps<{
    studentData: any;
    parentData: any;
    requiresOtp?: boolean;
    studentHasContact?: boolean;
    parentHasContact?: boolean;
}>();

// Get step from URL params or default to 1
const urlParams = new URLSearchParams(window.location.search);
const initialStep = parseInt(urlParams.get('step') || '1');
const studentId = ref(props.studentData?.students_id || '');

// Authentication states
const currentStep = ref(initialStep);
const studentEmail = ref("");
const studentPhone = ref("");
const profileImage = ref(null);
const profileImagePreview = ref("");

const loading = ref(false);
const mode = urlParams.get('mode') || '';
const requiresOtp = ref(props.requiresOtp || false);

// Parent verification
const guardianFirstName = ref("");
const guardianMiddleInitial = ref("");
const guardianLastName = ref("");
const guardianRelation = ref("");
const guardianEmail = ref("");
const guardianPhone = ref("");
const parentVerified = ref(false);
const savingData = ref(false);
const qrcodeDownloaded = ref(false);
// Check if student is already authenticated (from session)
const checkAuthentication = () => {
    // If we're on step 2 and came from OTP verification, we're authenticated
    if (currentStep.value === 2) {
        return true;
    }
    return false;
};

watch(currentStep, async (newStep) => {
    if (newStep === 3) {

        await nextTick();
        // Generate QR code with multiple retry attempts
        setTimeout(() => generateQRCode(), 100);
        setTimeout(() => generateQRCode(), 300);
        setTimeout(() => generateQRCode(), 600);
    }
});


const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;

    if (!files || files.length === 0) {
        console.log('No file selected');
        return;
    }

    const file = files[0];

    if (file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file.');
            target.value = '';
            return;
        }

        if (file.size > 5120 * 1024) {
            alert('Image size must be less than 5MB.');
            target.value = '';
            return;
        }

        profileImage.value = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            // Type-safe result handling
            const result = e.target?.result;
            if (result && typeof result === 'string') {
                profileImagePreview.value = result;
            }
        };

        reader.onerror = () => {
            console.error('Error reading file');
            alert('Error reading the selected file.');
        };

        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    profileImage.value = null;
    profileImagePreview.value = "";

    // ✅ Type-safe element access
    const fileInput = document.getElementById('profile-image-input') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

// Student authentication - send OTP
const authenticateStudent = async () => {
    if (studentEmail.value.trim() === "" || studentPhone.value.trim() === "") {
        alert("Please fill in both email and phone number.");
        return;
    }

    if (!profileImage.value && !props.studentData?.students_profile_image) {
        alert("Please upload a profile image.");
        return;
    }

    loading.value = true;

    try {
        const formData = new FormData();
        formData.append('email', studentEmail.value);
        formData.append('phone', studentPhone.value);
        formData.append('student_id', props.studentData.students_id);

        if (profileImage.value) {
            formData.append('profile_image', profileImage.value);
        }

        const response = await axios.post('/student/send-otp', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.data.success) {
            // Redirect to OTP verification page
            router.visit('/otp/verify', {
                method: 'get',
                replace: true
            });
        }
    } catch (error) {
        if (error.response?.data?.error) {
            alert(error.response.data.error);
        } else {
            alert('Failed to send OTP. Please try again.');
        }
        console.error('Error:', error);
    } finally {
        loading.value = false;
    }
};

const isParentFormValid = computed(() => {
    return guardianFirstName.value.trim() !== "" &&
        guardianLastName.value.trim() !== "" &&
        guardianRelation.value.trim() !== "" &&
        guardianEmail.value.trim() !== "" &&
        guardianPhone.value.trim() !== "";
});


// Parent verification and data saving
const verifyParent = async () => {
    if (guardianEmail.value.trim() === "" || guardianPhone.value.trim() === "") {
        alert("Please enter both guardian email and phone number.");
        return;
    }

    if (!isParentFormValid.value) {
        alert("Please fill in all required fields (First Name, Last Name, Relationship, Email, and Phone Number).");
        return;
    }

    if (!props.studentData?.students_id && !studentId.value) {
        alert("Student data not found. Please start over.");
        router.visit('/user');
        return;
    }

    savingData.value = true;

    try {
        const response = await axios.post('/student/save-data', {
            student_id: props.studentData.students_id,
            parent_first_name: guardianFirstName.value.trim(),
            parent_middle_initial: guardianMiddleInitial.value.trim() || null,
            parent_last_name: guardianLastName.value.trim(),
            parent_relation: guardianRelation.value.trim(),
            parent_email: guardianEmail.value.trim(),
            parent_phone: guardianPhone.value.trim()
        });

        if (response.data.success) {
            parentVerified.value = true;
            currentStep.value = 3;


            await nextTick();

            const studentIdForQR = response.data.student_id || props.studentData.students_id;

            // Multiple attempts to generate QR code
            setTimeout(() => generateQRCode(studentIdForQR), 200);
            setTimeout(() => generateQRCode(studentIdForQR), 500);
            setTimeout(() => generateQRCode(studentIdForQR), 1000);
        }
    } catch (error) {
        console.error('Save data error:', error);

        if (error.response?.data) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors || {};
                let errorMessage = 'Validation failed:\n';
                Object.keys(errors).forEach(key => {
                    errorMessage += `${key}: ${errors[key][0]}\n`;
                });
                alert(errorMessage);
            } else {
                alert(error.response.data.error || 'Server error occurred');
            }
        } else if (error.request) {
            // Network error
            alert('Network error. Please check your connection.');
        } else {
            // Request setup error
            alert('Request failed. Please try again.');
        }
    } finally {
        savingData.value = false;
    }
};

const goBackToStep1 = async () => {
    try {
        // Clear authentication session on backend
        await axios.post('/student/clear-auth-session', {
            student_id: props.studentData.students_id
        });

        // Then redirect to start fresh
        router.visit('/Details?step=1', {
            method: 'get',
            replace: true
        });
    } catch (error) {
        console.error('Error clearing session:', error);
        // Fallback: just reload the page to start fresh
        window.location.href = '/Details?step=1';
    }
};

const generateQRCode = async (studentIdParam: string | null = null) => {
    const id = studentIdParam || props.studentData?.students_id || studentId.value;
    if (!id) {
        console.error('No student ID available for QR code generation');
        return;
    }

    console.log('Generating QR code for ID:', id);

    // Wait for DOM
    await new Promise(resolve => setTimeout(resolve, 100));

    try {
        const canvas = document.querySelector("#qrcode") as HTMLCanvasElement;
        if (!canvas) {
            console.error('QR code canvas element not found');
            setTimeout(() => generateQRCode(studentIdParam), 500);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // Generate QR code
        await QRCode.toCanvas(canvas, id, {
            width: 200,
            height: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });

        console.log('QR code generated successfully');
    } catch (error) {
        console.error('Error generating QR code:', error);

        setTimeout(() => generateQRCode(studentIdParam), 1000);
    }
};

// NEW QR Code download function
const downloadQRCode = () => {
    const canvas = document.querySelector("#qrcode") as HTMLCanvasElement;

    if (!canvas) {
        alert('QR code not generated yet. Please wait a moment and try again.');
        generateQRCode();
        return;
    }

    try {
        // Create a new canvas for the combined image
        const combinedCanvas = document.createElement('canvas');
        const ctx = combinedCanvas.getContext('2d');

        if (!ctx) {
            alert('Failed to create download canvas. Please try again.');
            return;
        }


        const qrSize = 200;
        const textHeight = 60;
        const padding = 20;

        combinedCanvas.width = qrSize + (padding * 2);
        combinedCanvas.height = qrSize + textHeight + (padding * 2);

        // Fill background with white
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height);

        // Draw the QR code
        ctx.drawImage(canvas, padding, padding, qrSize, qrSize);

        // Add student ID text
        const studentIdText = props.studentData?.students_id || studentId.value;

        // Configure text style
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 16px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Calculate text position (centered below QR code)
        const textX = combinedCanvas.width / 2;
        const textY = qrSize + padding + (textHeight / 2);

        // Draw the student ID text
        ctx.fillText(`Student ID: ${studentIdText}`, textX, textY);

        // Optional: Add a border around the entire image
        ctx.strokeStyle = '#CCCCCC';
        ctx.lineWidth = 2;
        ctx.strokeRect(1, 1, combinedCanvas.width - 2, combinedCanvas.height - 2);

        // Convert combined canvas to blob and download
        combinedCanvas.toBlob((blob: Blob | null) => {
            if (!blob) {
                alert('Failed to generate image. Please try again.');
                return;
            }

            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `student_qrcode_${studentIdText}.png`;
            link.click();

            // Cleanup
            URL.revokeObjectURL(url);
        }, 'image/png');

        qrcodeDownloaded.value = true;
    } catch (error) {
        console.error('Error downloading QR code:', error);
        alert('Failed to download QR code. Please try again.');
    }
};

const startOver = () => {
    if (!qrcodeDownloaded.value) return;

    router.visit('/user', {
        method: 'get',
        replace: true
    });
};
// Navigation helpers
const goToStep = (step: number) => {
    currentStep.value = step;
    // Update URL without page reload
    const url = new URL(window.location.href);
    url.searchParams.set('step', step.toString());
    window.history.replaceState({}, '', url.toString());
};

onMounted(() => {
    if (props.studentData?.students_id) {
        studentId.value = props.studentData.students_id;
    }
    if (props.studentData?.students_profile_image) {
        profileImagePreview.value = `/storage/${props.studentData.students_profile_image}`;
    }

    if (currentStep.value === 2) {
        // Pre-fill parent email if available
        if (props.parentData) {
            guardianFirstName.value = props.parentData.parent_first_name || "";
            guardianMiddleInitial.value = props.parentData.parent_middle_initial || "";
            guardianLastName.value = props.parentData.parent_last_name || "";
            guardianRelation.value = props.parentData.parent_relation || "";
            guardianEmail.value = props.parentData.parent_email || "";
            guardianPhone.value = props.parentData.parent_phone_num || "";
        }
    }
    if (currentStep.value === 3) {

        setTimeout(() => generateQRCode(), 100);
        setTimeout(() => generateQRCode(), 500);
        setTimeout(() => generateQRCode(), 1000);
    }

    if (mode === 'parent_update' && props.studentData) {
        studentEmail.value = props.studentData.students_email || "";
        studentPhone.value = props.studentData.students_phone_num || "";
    }
});
</script>

<template>
    <div
        class="relative min-h-screen bg-cover bg-center px-4 py-8"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >

        <div class="absolute top-10 left-1/2 transform -translate-x-1/2 z-10 flex flex-col items-center mt-2 text-white ">
            <div class="relative z-10 p-2 rounded-lg text-center max-w-lg w-full mb-20">
                <img
                    src="/images/logo3.png"
                    alt="USePass Logo"
                    class="mx-auto mb-7 w-[250px] min-w-64 h-auto"
                />
            </div>
        </div>

        <div class="absolute inset-0 bg-black bg-opacity-60 z-0"></div>

        <div class="relative z-10 mt-40 bg-white bg-opacity-90 rounded-md shadow-md p-6 max-w-4xl mx-auto space-y-6">

            <!-- Progress -->
            <div class="flex justify-center mb-6">
                <div class="flex items-center space-x-4">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                 currentStep >= 1 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600']">
                        1
                    </div>
                    <div :class="['w-16 h-1', currentStep > 1 ? 'bg-green-500' : 'bg-gray-300']"></div>
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                 currentStep >= 2 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600']">
                        2
                    </div>
                    <div :class="['w-16 h-1', currentStep > 2 ? 'bg-green-500' : 'bg-gray-300']"></div>
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                 currentStep >= 3 ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600']">
                        3
                    </div>
                </div>
            </div>

            <!-- STEP 1: Student Information & Email Verification -->
            <div v-if="currentStep === 1">
                <h2 class="text-black font-semibold text-lg mb-4">Step 1: Student Information & Verification</h2>

                <h3 class="text-black font-medium text-base mb-4">Student ID: {{ studentData.students_id }}</h3>

                <!-- Personal Info  -->
                <div class="mb-6">
                    <h3 class="font-bold mb-2">Personal Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm bg-gray-50 p-4 rounded">
                        <div><strong>First Name:</strong> {{ studentData.students_first_name || 'N/A' }}</div>
                        <div><strong>Middle Name:</strong> {{ studentData.students_middle_initial || 'N/A' }}</div>
                        <div><strong>Surname:</strong> {{ studentData.students_last_name || 'N/A' }}</div>
                        <div><strong>Sex:</strong> {{ studentData.students_gender || 'N/A' }}</div>
                        <div><strong>Program:</strong> {{ studentData.students_program || 'N/A' }}</div>
                        <div><strong>Major:</strong> {{ studentData.students_major || 'N/A' }}</div>
                        <div><strong>Unit:</strong> {{ studentData.students_unit || 'N/A' }}</div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="font-bold mb-4 text-red-600">
                        {{ mode === 'parent_update' ? 'Verify Your Email to Update Parent Information' : 'Please Provide Your Contact Details for Verification' }}
                    </h3>

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-2">Profile Image*</label>

                        <div class="mb-4">
                            <div v-if="profileImagePreview || studentData?.students_profile_image"
                                 class="relative inline-block">
                                <img
                                    :src="profileImagePreview || `/storage/${studentData.students_profile_image}`"
                                    alt="Profile Preview"
                                    class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300"
                                />
                                <button
                                    v-if="profileImagePreview"
                                    @click="removeImage"
                                    type="button"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                            <div v-else class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                <span class="text-gray-400 text-sm">No image</span>
                            </div>
                        </div>


                        <input
                            id="profile-image-input"
                            type="file"
                            accept="image/*"
                            @change="handleImageUpload"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            :disabled="loading"
                        />
                        <p class="text-xs text-gray-500 mt-1">Maximum file size: 5MB. Supported formats: JPG, PNG</p>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Email Address*</label>
                            <input
                                type="email"
                                v-model="studentEmail"
                                placeholder="Enter your email account"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Contact Number*</label>
                            <input
                                type="tel"
                                v-model="studentPhone"
                                placeholder="Enter your phone number"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="loading"
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <button
                            @click="authenticateStudent"
                            :disabled="loading || !studentEmail.trim() || !studentPhone.trim() || (!profileImage && !studentData?.students_profile_image)"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-6 py-2 rounded text-sm font-medium"
                        >
                            {{ loading ? 'Sending OTP...' : 'Send OTP to Email' }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ mode === 'parent_update' ? 'An OTP will be sent to verify your identity before updating parent information.' : 'An OTP will be sent to your email for verification before proceeding to the next step.' }}
                    </p>
                </div>
            </div>

            <!-- STEP 2: Parent/Guardian Information -->
            <div v-if="currentStep === 2">
                <h2 class="text-black font-semibold text-lg mb-4">Step 2: Parent/Guardian Information</h2>

                <div>
                    <h3 class="font-bold mb-4 text-red-600">Please Fill Parent/Guardian Information</h3>

                    <!-- Parent Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">First Name*</label>
                            <input
                                type="text"
                                v-model="guardianFirstName"
                                :placeholder="parentData?.parent_first_name || 'Enter first name'"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Middle Initial</label>
                            <input
                                type="text"
                                v-model="guardianMiddleInitial"
                                :placeholder="parentData?.parent_middle_initial || 'M.I.'"
                                maxlength="1"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                :disabled="savingData"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Last Name*</label>
                            <input
                                type="text"
                                v-model="guardianLastName"
                                :placeholder="parentData?.parent_last_name || 'Enter last name'"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                    </div>

                    <!-- Relation Field -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Relationship*</label>
                            <select
                                v-model="guardianRelation"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            >
                                <option value="">Select relationship</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Guardian">Guardian</option>
                            </select>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Email Address*</label>
                            <input
                                type="email"
                                v-model="guardianEmail"
                                :placeholder="parentData?.parent_email || 'Enter email address'"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Phone Number*</label>
                            <input
                                type="tel"
                                v-model="guardianPhone"
                                :placeholder="parentData?.parent_phone_num || 'Enter phone number'"
                                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-4">
                        <button
                            @click="verifyParent"
                            :disabled="savingData || !isParentFormValid"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-6 py-2 rounded text-sm font-medium"
                        >
                            {{ savingData ? 'Saving...' : 'Save & Continue' }}
                        </button>
                    </div>

                    <p class="text-sm text-gray-600 mt-2">
                        All fields marked with * are required.
                    </p>
                </div>
            </div>

            <!-- QR Code Generation -->
            <div v-if="currentStep === 3">
                <h2 class="text-black font-semibold text-lg mb-4">Step 3:  Your Student QR Code</h2>

                <div class="bg-white p-6 rounded shadow-md text-center">
                    <h3 class="font-bold mb-4 text-green-600">✓ Registration Complete!</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Your information has been saved successfully. Here's your student QR code:
                    </p>

                    <div class="border-2 border-gray-300 rounded p-4 mb-4 inline-block">
                        <canvas id="qrcode" width="200" height="200"></canvas>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        Student ID: <strong>{{ studentData?.students_id || studentId }}</strong>
                    </p>

                    <div class="space-y-3">
                        <button
                            @click="downloadQRCode"
                            class="bg-red-700 hover:bg-red-800 text-white px-6 py-2 text-sm rounded font-medium block mx-auto"
                        >
                            Download QR Code
                        </button>
                        <p class="text-xs text-gray-500">
                            Save this barcode for future campus access
                        </p>

                        <!-- New Student Button - enabled after download -->
                        <button
                            @click="startOver"
                            :disabled="!qrcodeDownloaded"
                            :class="[
                    'px-6 py-2 text-sm rounded font-medium transition-all duration-200',
                     qrcodeDownloaded
                        ? 'bg-blue-600 hover:bg-blue-700 text-white'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                ]"
                        >
                            {{ qrcodeDownloaded ? 'Register New Student' : 'Download QR code first' }}

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>
