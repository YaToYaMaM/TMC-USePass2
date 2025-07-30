<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { router } from '@inertiajs/vue3';
import JsBarcode from "jsbarcode";
import axios from 'axios';

const props = defineProps<{
    studentData: any;
    parentData: any;
}>();

// Get step from URL params or default to 1
const urlParams = new URLSearchParams(window.location.search);
const initialStep = parseInt(urlParams.get('step') || '1');
const studentId = ref(props.studentData?.students_id || '');

// Authentication states
const currentStep = ref(initialStep);
const studentEmail = ref("");
const studentPhone = ref("");
const loading = ref(false);

// Parent verification
const guardianEmail = ref("");
const guardianPhone = ref("");
const parentVerified = ref(false);
const savingData = ref(false);
const barcodeDownloaded = ref(false);
// Check if student is already authenticated (from session)
const checkAuthentication = () => {
    // If we're on step 2 and came from OTP verification, we're authenticated
    if (currentStep.value === 2) {
        return true;
    }
    return false;
};

// Student authentication - send OTP
const authenticateStudent = async () => {
    if (studentEmail.value.trim() === "" || studentPhone.value.trim() === "") {
        alert("Please fill in both email and phone number.");
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post('/student/send-otp', {
            email: studentEmail.value,
            phone: studentPhone.value,
            student_id: props.studentData.students_id
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

// Parent verification and data saving
const verifyParent = async () => {
    if (guardianEmail.value.trim() === "" || guardianPhone.value.trim() === "") {
        alert("Please enter both guardian email and phone number.");
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
            parent_email: guardianEmail.value,
            parent_phone: guardianPhone.value
        });

        if (response.data.success) {
            parentVerified.value = true;
            currentStep.value = 3;
            const studentId = response.data.student_id || props.studentData.students_id;
            generateBarcode(studentId);
        }
    } catch (error) {
        console.error('Save data error:', error);

        if (error.response?.data) {
            // Server responded with error
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

const generateBarcode = (studentIdParam  = null) => {
    const id = studentIdParam || props.studentData?.students_id || studentId.value;
    if (!id) {
        console.error('No student ID available for barcode generation');
        return;
    }

    console.log('Generating barcode for ID:', id);

    const generateCode = () => {
        const barcodeElement = document.querySelector("#barcode");

        if (!barcodeElement) {
            console.error('Barcode element not found');
            return;
        }

        try {
            JsBarcode(barcodeElement, id, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 80,
                displayValue: true,
                fontSize: 16,
                textMargin: 2
            });
            console.log('Barcode generated successfully');
        } catch (error) {
            console.error('Error generating barcode:', error);
        }
    };

    setTimeout(generateCode, 100);
    setTimeout(generateCode, 300);
    setTimeout(generateCode, 500);
};

const downloadBarcode = () => {
    const svg = document.querySelector("#barcode");

    if (!svg || !svg.innerHTML.trim()) {
        alert('Barcode not generated yet. Please wait a moment and try again.');
        generateBarcode();
        return;
    }
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    canvas.width = 300;
    canvas.height = 120;
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(svg);
    const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
    const svgUrl = URL.createObjectURL(svgBlob);

    img.onload = function() {

        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        // Convert canvas to PNG blob
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `barcode_${props.studentData?.students_id || studentId.value}.png`;
            link.click();

            // Cleanup
            URL.revokeObjectURL(url);
            URL.revokeObjectURL(svgUrl);
        }, 'image/png');
    };

    img.src = svgUrl;
    barcodeDownloaded.value = true;
};

const startOver = () => {
    if (!barcodeDownloaded.value) return;


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

    if (currentStep.value === 2) {
        // Pre-fill parent email if available
        if (props.parentData?.parent_email) {
            guardianEmail.value = props.parentData.parent_email;
        }
        if (props.parentData?.parent_phone_num) {
            guardianPhone.value = props.parentData.parent_phone_num;
        }
    }
    if (currentStep.value === 3) {
        // Add a small delay
        setTimeout(() => {
            generateBarcode();
        }, 200);
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
                    <h3 class="font-bold mb-4 text-red-600">Please Provide Your Contact Details for Verification</h3>
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
                            :disabled="loading || !studentEmail.trim() || !studentPhone.trim()"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-6 py-2 rounded text-sm font-medium"
                        >
                            {{ loading ? 'Sending OTP...' : 'Send OTP to Email' }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        An OTP will be sent to your email for verification before proceeding to the next step.
                    </p>
                </div>
            </div>

            <!-- STEP 2: Parent/Guardian Information -->
            <div v-if="currentStep === 2">
                <h2 class="text-black font-semibold text-lg mb-4">Step 2: Parent/Guardian Information</h2>

                <div v-if="parentData" class="mb-6">
                    <h3 class="font-bold mb-2">Parent/Guardian Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm bg-gray-50 p-4 rounded">
                        <div><strong>First Name:</strong> {{ parentData.parent_first_name || 'N/A' }}</div>
                        <div><strong>Middle Name:</strong> {{ parentData.parent_middle_initial || 'N/A' }}</div>
                        <div><strong>Surname:</strong> {{ parentData.parent_last_name || 'N/A' }}</div>
                        <div><strong>Relation:</strong> {{ parentData.parent_relation || 'N/A' }}</div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <h3 class="font-bold mb-4 text-red-600">Update Parent/Guardian Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Parent/Guardian Email*</label>
                            <input
                                type="email"
                                v-model="guardianEmail"
                                :placeholder="parentData?.parent_email || 'Enter parent/guardian email'"
                                class="w-full px-3 py-2 border border-gray-400 focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Parent/Guardian Phone*</label>
                            <input
                                type="tel"
                                v-model="guardianPhone"
                                :placeholder="parentData?.parent_phone_num || 'Enter parent/guardian phone'"
                                class="w-full px-3 py-2 border border-gray-400 rounded-sm focus:outline-none focus:border-red-600"
                                required
                                :disabled="savingData"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <!--                        <button-->
                        <!--                            @click="goBackToStep1"-->
                        <!--                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm"-->
                        <!--                            :disabled="savingData"-->
                        <!--                        >-->
                        <!--                            Back-->
                        <!--                        </button>-->
                        <button
                            @click="verifyParent"
                            :disabled="savingData || !guardianEmail.trim() || !guardianPhone.trim()"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-6 py-2 rounded text-sm font-medium"
                        >
                            {{ savingData ? 'Saving...' : 'Save & Continue' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Barcode Generation -->
            <div v-if="currentStep === 3">
                <h2 class="text-black font-semibold text-lg mb-4">Step 3: Your Student Barcode</h2>

                <div class="bg-white p-6 rounded shadow-md text-center">
                    <h3 class="font-bold mb-4 text-green-600">✓ Registration Complete!</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Your information has been saved successfully. Here's your student barcode:
                    </p>

                    <div class="border-2 border-gray-300 rounded p-4 mb-4 inline-block">
                        <svg id="barcode" width="300" height="120"></svg>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        Student ID: <strong>{{ studentData?.students_id || studentId }}</strong>
                    </p>

                    <div class="space-y-3">
                        <button
                            @click="downloadBarcode"
                            class="bg-red-700 hover:bg-red-800 text-white px-6 py-2 text-sm rounded font-medium block mx-auto"
                        >
                            Download Barcode
                        </button>
                        <p class="text-xs text-gray-500">
                            Save this barcode for future campus access
                        </p>

                        <!-- New Student Button - enabled after download -->
                        <button
                            @click="startOver"
                            :disabled="!barcodeDownloaded"
                            :class="[
                    'px-6 py-2 text-sm rounded font-medium transition-all duration-200',
                    barcodeDownloaded
                        ? 'bg-blue-600 hover:bg-blue-700 text-white'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                ]"
                        >
                            {{ barcodeDownloaded ? 'Register New Student' : 'Download barcode first' }}

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
