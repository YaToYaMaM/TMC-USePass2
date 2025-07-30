<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import Navbar from "@/Components/Navbar.vue";
</script>

<template>
    <nav class="relative flex justify-between items-center px-6 py-4 bg-red-800 h-[56px] min-h-15 flex-shrink-0 overflow-hidden">
        <div class="absolute left-6 top-1/2 -translate-y-1/2">
            <img src="/images/Logo1.png" alt="Logo" class="w-28 h-auto object-contain" />
        </div>
    </nav>
    <div class="flex min-h-screen bg-[#F5F5F5]">
        <div class="w-full hidden lg:flex items-center justify-center relative overflow-hidden">
            <img src="/images/bg.png" class="absolute inset-0 w-full h-full object-cover" alt="Background" />

            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        </div>
        <div class="absolute inset-0 z-50 flex items-center justify-center">
            <slot />
        </div>
    </div>
</template>
