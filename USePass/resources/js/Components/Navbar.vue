<script setup lang="ts">
import { ref } from 'vue';
import {router, usePage} from "@inertiajs/vue3";
import { route } from 'ziggy-js';
import { Link } from '@inertiajs/vue3';

const menuOpen = ref(false);

const logout = () => {
    router.post(route('logout'))
}
interface User {
    id: number;
    name: string;
    email?: string;
    role: 'admin' | 'guard' | 'user';
    first_name: string;
    last_name: string;
}

const page = usePage();
const user = page.props.auth.user as User;

</script>

<template>
    <nav class="relative flex justify-between items-center px-6 py-4 bg-red-800 h-15 min-h-15 flex-shrink-0 overflow-visible">
        <img src="/images/logo1.png" alt="Logo" class="w-20 sm:w-28 h-auto object-contain" />
        <div class="flex items-center space-x-2 sm:space-x-4">
            <img src="/images/profile.png" alt="Profile" class="h-10 w-10 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white" />
            <div class="relative"> <!-- Ensure this div is relative -->
                <button @click="menuOpen = !menuOpen" class="focus:outline-none" aria-label="Toggle menu">
                    <svg class="h-5 w-5 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <transition name="fade">
                    <div v-if="menuOpen" class="absolute right-0 top-full mt-2 w-40 sm:w-48 bg-[#760000] rounded-lg shadow-lg border border-[#a00000] z-[99999]">
                        <div class="flex items-center space-x-2 sm:space-x-3 px-3 py-2 border-b border-[#a00000]">
                            <img src="/images/profile.png" alt="Profile" class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white" />
                            <div>
                                <div class="text-white font-extrabold text-xs sm:text-sm">{{ user.first_name }} {{ user.last_name}}</div>
                                <div class="text-white text-opacity-80 italic text-[10px] sm:text-xs -mt-1">{{ user.role }}</div>
                            </div>
                        </div>
                        <nav class="flex flex-col px-3 py-2 space-y-1 sm:space-y-2">
                            <a href="#" class="text-white font-bold text-xs sm:text-sm hover:underline">CHANGE PASSWORD</a>
                            <button @click="logout" class="text-white font-bold text-xs sm:text-sm hover:underline text-left">LOGOUT</button>
                        </nav>
                    </div>
                </transition>
            </div>
        </div>
    </nav>
</template>
