<template>
    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Logo -->
        <div class="relative z-10 p-4 rounded-lg text-center max-w-lg w-full mb-8">
            <img
                src="/images/logo3.png"
                alt="USePass Logo"
                class="mx-auto mb-4 w-[300px] h-auto"
            />
            <h1 class="text-white font-extrabold text-2xl mb-2">
                Faculty/Staff Registration
            </h1>
            <p class="text-white italic text-base">
                Access your QR code or register for the first time
            </p>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 bg-white bg-opacity-95 rounded-lg shadow-lg p-6 max-w-lg w-full mb-8">

            <!-- Existing Faculty Section -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 text-center">Already Registered?</h2>
                <p class="text-sm text-gray-600 mb-4 text-center">
                    Enter your Faculty/Staff ID to download your QR code
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700">Faculty/Staff ID</label>
                        <input
                            type="text"
                            v-model="facultyId"
                            placeholder="Enter your Faculty/Staff ID"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:border-red-600"
                            :disabled="loading"
                            @keyup.enter="checkFacultyId"
                        />
                    </div>

                    <button
                        @click="checkFacultyId"
                        :disabled="loading || !facultyId.trim()"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-3 rounded-md font-semibold transition"
                    >
                        {{ loading ? 'Checking...' : 'Get My QR Code' }}
                    </button>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                    {{ errorMessage }}
                </div>
            </div>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-gray-300"></div>
                <div class="px-4 text-gray-500 text-sm">OR</div>
                <div class="flex-1 border-t border-gray-300"></div>
            </div>

            <!-- New Registration Section -->
            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-800 mb-4">First Time Registration</h2>
                <p class="text-sm text-gray-600 mb-6">
                    New faculty or staff member? Register to get your QR code
                </p>

                <button
                    @click="goToRegistration"
                    class="w-full bg-red-800 hover:bg-[#760000] text-white px-6 py-3 rounded-md font-semibold transition"
                >
                    Register as Faculty/Staff
                </button>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Your QR Code</h3>

                    <!-- Faculty Info -->
                    <div class="mb-4 p-4 bg-gray-100 rounded-lg text-left">
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span class="font-medium">
                                    Faculty/Staff ID:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_id }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                 <span class="font-medium">
                                    Name:
                                    <span class="italic text-gray-600">{{ fullName }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                 <span class="font-medium">
                                    College:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_college }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                 <span class="font-medium">
                                    Department:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_department }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                 <span class="font-medium">
                                    Unit:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_unit }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mb-4 flex justify-center">
                        <div class="border-2 border-gray-300 rounded-lg p-4 bg-white">
                            <canvas id="faculty-qrcode" width="200" height="200"></canvas>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3">
                        <button
                            @click="downloadQRCode"
                            class="flex-1 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded font-medium"
                        >
                            Download QR Code
                        </button>
                        <button
                            @click="closeModal"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded font-medium"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import axios from 'axios';

const facultyId = ref('');
const loading = ref(false);
const errorMessage = ref('');
const showQRModal = ref(false);
const facultyData = ref(null);

const fullName = computed(() => {
    if (!facultyData.value) return '';

    const { faculty_first_name, faculty_middle_initial, faculty_last_name } = facultyData.value;
    let name = `${faculty_first_name} `;

    if (faculty_middle_initial) {
        name += `${faculty_middle_initial}. `;
    }

    name += faculty_last_name;
    return name;
});

const checkFacultyId = async () => {
    if (!facultyId.value.trim()) return;

    loading.value = true;
    errorMessage.value = '';

    console.log('Checking faculty ID:', facultyId.value.trim());

    try {
        const response = await axios.get(`/faculty/qr/${facultyId.value.trim()}`);


        if (response.data.success && response.data.faculty) {
            facultyData.value = response.data.faculty;
            showQRModal.value = true;

            // Generate QR code after modal is shown
            nextTick(() => {
                generateQRCode();
            });
        } else {
            errorMessage.value = 'Faculty/Staff ID not found. Please check your ID or register first.';
        }
    } catch (error) {
        console.error('Full error object:', error);
        console.error('Error response:', error.response);

        if (error.response?.status === 404) {
            errorMessage.value = 'Faculty/Staff ID not found. Please check your ID or register first.';
        } else if (error.response?.status === 500) {
            errorMessage.value = 'Server error. Please try again or contact support.';
        } else {
            errorMessage.value = 'Unable to retrieve your information. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};

const generateQRCode = async () => {
    if (!facultyData.value?.faculty_id) return;

    try {
        const canvas = document.querySelector('#faculty-qrcode');
        if (!canvas) {
            setTimeout(() => generateQRCode(), 500);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
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
        console.error('Error generating QR code:', error);
    }
};

const downloadQRCode = () => {
    const canvas = document.querySelector('#faculty-qrcode');

    if (!canvas) {
        alert('QR code not ready. Please wait a moment and try again.');
        return;
    }

    try {
        // Create combined canvas with faculty info
        const combinedCanvas = document.createElement('canvas');
        const ctx = combinedCanvas.getContext('2d');

        const qrSize = 200;
        const textHeight = 100;
        const padding = 20;

        combinedCanvas.width = qrSize + (padding * 2);
        combinedCanvas.height = qrSize + textHeight + (padding * 2);

        // White background
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height);

        // Draw QR code
        ctx.drawImage(canvas, padding, padding, qrSize, qrSize);

        // Add faculty information
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 14px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const textX = combinedCanvas.width / 2;
        let textY = qrSize + padding + 20;

        // Faculty ID
        ctx.fillText(`Faculty ID: ${facultyData.value.faculty_id}`, textX, textY);

        // Name
        ctx.font = '12px Arial, sans-serif';
        textY += 20;
        ctx.fillText(fullName.value, textX, textY);

        // Department
        textY += 15;
        ctx.fillText(facultyData.value.faculty_department, textX, textY);

        // Unit
        textY += 15;
        ctx.fillText(`Unit: ${facultyData.value.faculty_unit}`, textX, textY);

        // Border
        ctx.strokeStyle = '#CCCCCC';
        ctx.lineWidth = 2;
        ctx.strokeRect(1, 1, combinedCanvas.width - 2, combinedCanvas.height - 2);

        // Download
        combinedCanvas.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `faculty_qrcode_${facultyData.value.faculty_id}.png`;
            link.click();
            URL.revokeObjectURL(url);
        }, 'image/png');

    } catch (error) {
        console.error('Error downloading QR code:', error);
        alert('Failed to download QR code. Please try again.');
    }
};

const closeModal = () => {
    showQRModal.value = false;
    facultyData.value = null;
    facultyId.value = '';
    errorMessage.value = '';
};

const goToRegistration = () => {
    router.visit('/faculty-staff/register');
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>
