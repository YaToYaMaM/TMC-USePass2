<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-[#F5F5F5]">
        <div class="w-3/4 hidden lg:flex items-center justify-center relative overflow-hidden [clip-path:polygon(0%_0%,100%_0%,80%_100%,0%_100%)]">
            <img src="/images/bg.png" class="absolute inset-0 w-full h-full object-cover" alt="Background" />

            <div class="absolute inset-0 bg-black bg-opacity-60"></div>


            <div class="relative z-10 text-white px-6 w-full max-w-[746px] mb-9 mx-auto">
                <img src="/images/Logo1.png" class="w-full" alt="Logo" />

                <p class="absolute bottom-[5px] left-1/2 transform -translate-x-1/2 text-center text-white text-xl italic leading-tight">
                    "Track Student. Ensure Safety.<br />
                    <span class="font-semibold">USePass."</span>
                </p>
            </div>

        </div>


        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 bg-[#F5F5F5]">

            <slot />
        </div>
    </div>
</template>
