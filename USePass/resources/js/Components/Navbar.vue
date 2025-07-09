<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
</script>

<template>
    <nav class="relative flex justify-between items-center px-6 py-4 bg-red-800 h-20 overflow-hidden">
        <div class="absolute left-6 top-1/2 -translate-y-1/2">
            <img src="/images/Logo1.png" alt="Logo" class="w-28 h-auto object-contain" />
        </div>
        <!-- Spacer to prevent overlap -->
        <div class="flex-1"></div>

        <div class="ml-36">
            <ul class="flex gap-x-4">
                <li class="nav-item">
                    <Link class="text-white" :href="route('home')">Home</Link>
                </li>
            </ul>
        </div>
    </nav>
</template>

<style scoped>

</style>
