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
                Faculty/Staff Registration Complete!
            </h1>
            <p class="text-white italic text-base">
                Your QR code is ready for download
            </p>
        </div>

        <!-- Success Content -->
        <div class="relative z-10 bg-white bg-opacity-95 rounded-lg shadow-lg p-6 max-w-lg w-full text-center">

            <!-- Success Message -->
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <div class="flex items-center justify-center">
                    <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <div class="font-bold text-lg">Registration Successful!</div>
                        <div class="text-sm">Welcome to USePass System</div>
                    </div>
                </div>
            </div>

            <!-- Faculty Information Display -->
            <div class="mb-6 p-4 bg-gray-100 rounded-lg text-left">
                <h3 class="font-bold text-lg mb-3 text-center text-gray-800">Faculty/Staff Information</h3>
                <div class="space-y-2 text-sm">
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

                    <transition name="fade">
                        <div v-if="showMoreInfo">
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
                            <div class="flex justify-between">
                                <span class="font-medium">
                                    Email:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_email }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                 <span class="font-medium">
                                    Phone:
                                    <span class="italic text-gray-600">{{ facultyData?.faculty_phone_num }}</span>
                                </span>
                            </div>
                        </div>
                    </transition>

                    <button
                        @click="toggleMoreInfo"
                        class="mt-4 text-blue-700 hover:underline font-medium text-sm focus:outline-none"
                    >
                        {{ showMoreInfo ? 'See Less' : 'See More' }}
                    </button>
                </div>
            </div>

            <!-- QR Code Display -->
            <div class="mb-6">
                <h3 class="font-bold text-lg mb-4 text-green-600">Your Faculty/Staff QR Code</h3>
                <div class="border-2 border-gray-300 rounded-lg p-6 mb-4 bg-white">
                    <canvas id="faculty-qrcode" width="200" height="200" class="mx-auto"></canvas>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    This QR code contains your Faculty/Staff ID: <strong>{{ facultyData?.faculty_id }}</strong>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button
                    @click="downloadQRCode"
                    class="bg-red-700 hover:bg-red-800 text-white px-6 py-2 text-sm rounded font-medium block mx-auto"
                >
                    Download QR Code
                </button>

                <div class="pt-4 border-t">
                    <button
                        @click="registerAnother"
                        class=" bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 text-sm rounded font-medium transition block mx-auto"
                    >
                        Back on Register
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import QRCode from 'qrcode';

const props = defineProps({
    facultyData: {
        type: Object,
        required: true
    }
});

const facultyData = ref(props.facultyData);
const showMoreInfo = ref(false);

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

const toggleMoreInfo = () => {
    showMoreInfo.value = !showMoreInfo.value;
};

const generateQRCode = async () => {
    if (!facultyData.value?.faculty_id) {
        console.error('No faculty ID available for QR code generation');
        return;
    }

    await nextTick();

    try {
        const canvas = document.querySelector('#faculty-qrcode');
        if (!canvas) {
            console.error('QR code canvas element not found');
            setTimeout(() => generateQRCode(), 500);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // Generate QR code
        await QRCode.toCanvas(canvas, facultyData.value.faculty_id, {
            width: 200,
            height: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        });

        console.log('Faculty QR code generated successfully');
    } catch (error) {
        console.error('Error generating QR code:', error);
        setTimeout(() => generateQRCode(), 1000);
    }
};

const downloadQRCode = () => {
    const canvas = document.querySelector('#faculty-qrcode');

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
        const textHeight = 80;
        const padding = 20;

        combinedCanvas.width = qrSize + (padding * 2);
        combinedCanvas.height = qrSize + textHeight + (padding * 2);

        // Fill background with white
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height);

        // Draw the QR code
        ctx.drawImage(canvas, padding, padding, qrSize, qrSize);

        // Add faculty information text
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 14px Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        const textX = combinedCanvas.width / 2;
        let textY = qrSize + padding + 20;

        // Draw faculty ID
        ctx.fillText(`Faculty ID: ${facultyData.value.faculty_id}`, textX, textY);

        // Draw name
        ctx.font = '12px Arial, sans-serif';
        textY += 20;
        ctx.fillText(fullName.value, textX, textY);

        // Draw department
        textY += 15;
        ctx.fillText(facultyData.value.faculty_department, textX, textY);

        // Optional: Add a border around the entire image
        ctx.strokeStyle = '#CCCCCC';
        ctx.lineWidth = 2;
        ctx.strokeRect(1, 1, combinedCanvas.width - 2, combinedCanvas.height - 2);

        // Convert combined canvas to blob and download
        combinedCanvas.toBlob((blob) => {
            if (!blob) {
                alert('Failed to generate image. Please try again.');
                return;
            }

            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = `faculty_qrcode_${facultyData.value.faculty_id}.png`;
            link.click();

            // Cleanup
            URL.revokeObjectURL(url);
        }, 'image/png');

    } catch (error) {
        console.error('Error downloading QR code:', error);
        alert('Failed to download QR code. Please try again.');
    }
};

const downloadInfoCard = () => {
    // Create a detailed information card
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    canvas.width = 400;
    canvas.height = 300;

    // Background
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Header
    ctx.fillStyle = '#760000';
    ctx.fillRect(0, 0, canvas.width, 40);

    // Header text
    ctx.fillStyle = '#FFFFFF';
    ctx.font = 'bold 16px Arial, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('USePass Faculty Information', canvas.width / 2, 25);

    // Information
    ctx.fillStyle = '#000000';
    ctx.font = '14px Arial, sans-serif';
    ctx.textAlign = 'left';

    let y = 70;
    const lineHeight = 25;

    const info = [
        `Faculty ID: ${facultyData.value.faculty_id}`,
        `Name: ${fullName.value}`,
        `Department: ${facultyData.value.faculty_department}`,
        `Position: ${facultyData.value.faculty_position}`,
        `Unit: ${facultyData.value.faculty_unit}`,
        `Email: ${facultyData.value.faculty_email}`,
        `Phone: ${facultyData.value.faculty_phone_num}`,
    ];

    info.forEach(line => {
        ctx.fillText(line, 20, y);
        y += lineHeight;
    });

    // Border
    ctx.strokeStyle = '#CCCCCC';
    ctx.lineWidth = 2;
    ctx.strokeRect(1, 1, canvas.width - 2, canvas.height - 2);

    // Download
    canvas.toBlob((blob) => {
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `faculty_info_${facultyData.value.faculty_id}.png`;
        link.click();
        URL.revokeObjectURL(url);
    }, 'image/png');
};

const registerAnother = () => {
    router.visit('/faculty-staff', {
        method: 'get',
        replace: true
    });
};

onMounted(() => {
    // Generate QR code with multiple attempts
    setTimeout(() => generateQRCode(), 100);
    setTimeout(() => generateQRCode(), 500);
    setTimeout(() => generateQRCode(), 1000);
});
</script>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
