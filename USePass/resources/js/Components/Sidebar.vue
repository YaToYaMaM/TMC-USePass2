<script setup lang="ts">
import { ref } from 'vue';
const isCollapsed = ref(false);

</script>

<template>
    <div class="flex h-full">
        <aside
        :class="[
          'bg-gray-100 text-black p-4 transition-all duration-300 ease-in-out h-full',
          isCollapsed ? 'w-16' : 'w-52'
        ]"

    >
        <!-- Toggle Button -->
        <button
            @click="isCollapsed = !isCollapsed"
            class="text-black mb-6 focus:outline-none"
        >
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

        <!-- Nav Items -->
        <nav>
            Menu
            <ul class="space-y-4">

                <li class="flex items-center space-x-3 text-sm">
                    <a
                        href="/dashboard"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
<!--                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">-->
<!--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" />-->
<!--                        </svg>-->
                        <img src="@/Icons/dashboard.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Dashboard</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3">
                    <a
                        href="/guard"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
                        <img src="@/Icons/secguard.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Security Guard</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3">
                    <a
                        href="/students"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
                        <img src="@/Icons/students.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Students</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3">
                    <a
                        href="/statistics"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
                        <img src="@/Icons/statistics.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Statistics</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3">
                    <a
                        href="/reports"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
                        <img src="@/Icons/statistics.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Reports</span>
                    </a>
                </li>
                <li class="flex items-center space-x-3">
                    <a
                        href="/logs"
                        class="w-48 flex items-center space-x-3 p-2 rounded-md hover:bg-red-800 hover:text-white transition"
                    >
                        <img src="@/Icons/logs.png" alt="User Icon" width="24" height="24" />
                        <span v-if="!isCollapsed" class="whitespace-nowrap">Logs</span>
                    </a>
                </li>
            </ul>
        </nav>

    </aside>

        <div class="px-10 py-1 w-full">
            <slot />
        </div>
    </div>
</template>
<style scoped>

</style>
