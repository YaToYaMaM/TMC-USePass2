<!-- Sidebar.vue -->
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3'

const isCollapsed = ref(false);
const isMobile = ref(false);

// Define the expected shape of the user
interface User {
    id: number
    name: string
    email: string
    role: string
}

const page = usePage<{ auth: { user: User | null } }>()

const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => user.value?.role === 'admin')
const isGuard = computed(() => user.value?.role === 'guard')

// Get current URL path for active state
const currentPath = computed(() => page.url)

// Helper function to check if a route is active
const isActiveRoute = (path: string) => {
    const current = currentPath.value || ''
    return current === path ||
        (path === '/' && current === '/') ||
        current.indexOf(path + '/') === 0 ||
        current.indexOf(path + '?') === 0
}

// Check screen size for responsive behavior
const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        isCollapsed.value = true;
    }
}

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
})

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
})

// Toggle sidebar with overlay handling for mobile
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
}

// Close sidebar when clicking outside on mobile
const closeSidebarOnMobile = () => {
    if (isMobile.value && !isCollapsed.value) {
        isCollapsed.value = true;
    }
}
</script>

<template>
    <!-- Mobile Overlay -->
    <div
        v-if="isMobile && !isCollapsed"
        @click="closeSidebarOnMobile"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
        :class="[
            'bg-white border-r border-gray-200 transition-all duration-300 ease-in-out shadow-xl z-50 flex-shrink-0',
            // Desktop behavior - fixed positioning
            !isMobile ? 'fixed left-0 top-20 h-screen' : '',
            !isMobile && (isCollapsed ? 'w-16' : 'w-64'),
            // Mobile behavior
            isMobile && (isCollapsed ? 'hidden' : 'fixed left-0 top-0 w-64 h-screen')
        ]"
    >
        <!-- Header Section -->
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <!-- Toggle Button -->
                <button
                    @click="toggleSidebar"
                    :class="[
                            'text-gray-600 hover:text-[#760000] focus:outline-none transition-all duration-200 transform hover:scale-110 p-2 rounded-lg hover:bg-gray-100',
                            isCollapsed && !isMobile ? 'mx-auto' : ''
                        ]"
                >
                    <svg
                        :class="[
                                'w-5 h-5 transition-transform duration-300',
                                !isCollapsed ? 'rotate-0' : 'rotate-180'
                            ]"
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

                <!-- Logo/Brand - Only show when not collapsed -->
                <div
                    v-if="!isCollapsed"
                    class="text-[#760000] font-bold text-lg ml-2 truncate"
                >
                </div>
            </div>
        </div>

        <!-- Navigation Section -->
        <nav class="p-4 flex-1 overflow-y-auto">
            <!-- Menu Label -->
            <div
                v-if="!isCollapsed"
                class="mb-6 text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
                Menu
            </div>

            <ul class="space-y-2">
                <!-- Dashboard - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/dashboard"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/dashboard')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <!-- Active indicator -->
                        <div
                            v-if="isActiveRoute('/dashboard')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <img
                                src="@/Icons/dashboard.png"
                                alt="Dashboard Icon"
                                width="20"
                                height="20"
                                :class="[
                                    'transition-transform duration-300',
                                    isActiveRoute('/dashboard') ? 'scale-110' : 'group-hover:scale-110'
                                ]"
                            />
                        </div>

                        <!-- Text with responsive handling -->
                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Dashboard
                            </span>

                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Dashboard
                        </div>
                    </a>
                </li>

                <!-- Guard Home - Guard only -->
                <li v-if="isGuard" class="relative">
                    <a
                        href="/"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Guard Home
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Guard Home
                        </div>
                    </a>
                </li>

                <!-- Scanner - Guard Only -->
                <li v-if="isGuard" class="relative">
                    <a
                        href="/scan"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/scan')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/scan')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Scanner
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Scanner
                        </div>
                    </a>
                </li>

                <!-- Security Guard Management - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/guard"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/guard')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/guard')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <img
                                src="@/Icons/secguard.png"
                                alt="Security Guard Icon"
                                width="20"
                                height="20"
                                :class="[
                                    'transition-transform duration-300',
                                    isActiveRoute('/guard') ? 'scale-110' : 'group-hover:scale-110'
                                ]"
                            />
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Security Guard
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Security Guard
                        </div>
                    </a>
                </li>

                <!-- Students Management - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/students"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/students')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/students')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <img
                                src="@/Icons/students.png"
                                alt="Students Icon"
                                width="20"
                                height="20"
                                :class="[
                                    'transition-transform duration-300',
                                    isActiveRoute('/students') ? 'scale-110' : 'group-hover:scale-110'
                                ]"
                            />
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Students
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Students
                        </div>
                    </a>
                </li>

                <!-- Statistics - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/statistics"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/statistics')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/statistics')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Statistics
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Statistics
                        </div>
                    </a>
                </li>

                <!-- Attendance Reports - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/reports"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/reports')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/reports')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Attendance Reports
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Attendance Reports
                        </div>
                    </a>
                </li>

                <!-- Incident Reports - Available for both Admin and Guard -->
                <li v-if="isAdmin || isGuard" class="relative">
                    <a
                    href="/incident"
                    :class="[
                    'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                    isCollapsed ? 'justify-center' : 'space-x-3',
                    isActiveRoute('/incident') || isActiveRoute('/spot-reports')
                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                    ]"
                    >
                    <div
                        v-if="isActiveRoute('/incident') || isActiveRoute('/spot-reports')"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                    ></div>

                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <span
                        v-if="!isCollapsed"
                        class="font-medium truncate flex-1 transition-all duration-300"
                    >
                {{ isGuard ? 'Report Incident' : 'Incident Reports' }}
            </span>

                    <div
                        v-if="isCollapsed"
                        class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                    >
                        {{ isGuard ? 'Report Incident' : 'Incident Reports' }}
                    </div>
                    </a>
                </li>

                <!-- Logs - Admin only -->
                <li v-if="isAdmin" class="relative">
                    <a
                        href="/logs"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/logs')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/logs')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Logs
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Logs
                        </div>
                    </a>
                </li>

                <!-- Guard only logs -->
                <li v-if="isGuard" class="relative">
                    <a
                        href="/glog"
                        :class="[
                                'flex items-center p-3 rounded-xl transition-all duration-300 ease-in-out group relative overflow-hidden',
                                isCollapsed ? 'justify-center' : 'space-x-3',
                                isActiveRoute('/glog')
                                    ? 'bg-[#760000] text-white shadow-lg transform scale-105'
                                    : 'hover:bg-red-50 hover:text-[#760000] text-gray-700'
                            ]"
                    >
                        <div
                            v-if="isActiveRoute('/glog')"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-white rounded-r-full"
                        ></div>

                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>

                        <span
                            v-if="!isCollapsed"
                            class="font-medium truncate flex-1 transition-all duration-300"
                        >
                                Logs
                            </span>

                        <div
                            v-if="isCollapsed"
                            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50"
                        >
                            Logs
                        </div>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="min-h-screen bg-gray-50">
        <!-- Content wrapper with proper padding and margin -->
        <div
            :class="[
                    'h-full transition-all duration-300 ease-in-out',
                    // Desktop margin adjustments
                    !isMobile && (isCollapsed ? 'ml-16' : 'ml-64'),
                    // Mobile - no margin when sidebar is hidden
                    isMobile ? 'ml-0' : ''
                ]"
        >
            <slot />
        </div>
    </main>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
    width: 4px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

/* Ensure smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Fix potential layout issues */
.flex {
    display: flex;
}

.flex-1 {
    flex: 1 1 0%;
}

.flex-shrink-0 {
    flex-shrink: 0;
}
</style>
