<template>
    <Head title="USePass" />
    <div
        class="relative min-h-screen bg-cover bg-center flex items-center justify-center"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Responsive Foreground Content -->
        <div
            class="
        relative z-10 flex flex-col items-center
        px-4 sm:px-8 md:px-12 lg:px-20
        py-6 md:py-10
        w-full
        max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl 2xl:max-w-2xl
        space-y-6 md:space-y-8
      "
        >
            <!-- Logo
            -->
            <img
                src="/images/logo4.png"
                alt="USePass Logo"
                class="mb-2 w-48 sm:w-56 md:w-64 lg:w-80 h-auto"
            />

            <!-- Profile Image (Bigger and Responsive) -->
            <div
                class="
          rounded-lg overflow-hidden shadow border-4 border-white
          w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64
        "
            >
                <img :src="profileImage" alt="Profile" class="object-cover w-full h-full" />
            </div>

            <!-- User Info (responsive text) -->
            <div class="text-white text-center">
                <div class="text-lg sm:text-xl md:text-2xl font-bold tracking-wide">{{ fullName }}</div>
                <div class="text-sm md:text-base lg:text-lg font-medium italic">{{ course }}</div>
                <div class="text-base md:text-lg mt-2 font-mono tracking-widest">{{ idNumber }}</div>
            </div>

            <div class="flex flex-row space-x-10 mt-2">
                <button
                    class="bg-[#26C60C] hover:bg-green-600 text-white font-bold py-2 px-0 w-32 rounded focus:outline-none shadow text-base md:text-lg transition flex items-center justify-center"
                >
                    IN
                </button>
                <button
                    class="bg-[#D6542D] hover:bg-red-600 text-white font-bold py-2 px-0 w-32 rounded focus:outline-none shadow text-base md:text-lg transition flex items-center justify-center"
                >
                    VOID
                </button>
            </div>

        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";

export default {
    name: "USePassCardView",
    components: {Head},
    data() {
        return {
            profileImage: "/images/profile.png",
            fullName: "Boliros, Shannen Ann",
            course: "Bachelor of Science in Information Technology",
            idNumber: "2022-00190",
        };
    },
};
</script>
