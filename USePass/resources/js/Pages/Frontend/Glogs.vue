
<template xmlns="http://www.w3.org/1999/html">
    <Head title="USePass" />
    <Frontend>
        <div class="min-h-screen bg-gray-50">
            <!-- Header Section with Controls -->
            <div class="bg-white shadow-sm border-b sticky top-0 z-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                        <h1 class="text-2xl font-bold text-gray-900">Campus Access Logs</h1>

                        <!-- Filters -->
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Date:</label>
                                <input
                                    type="date"
                                    v-model="selectedDate"
                                    class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>

                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Programs:</label>
                                <div class="relative">
                                    <select
                                        v-model="selectedProgram"
                                        class="appearance-none border border-gray-300 rounded-md px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white min-w-[250px]"
                                    >
                                        <option value="">All Programs</option>
                                        <option value="BSIT">Bachelor of Science in Information Technology</option>
                                        <option value="BSCS">Bachelor of Science in Computer Science</option>
                                        <option value="BSIS">Bachelor of Science in Information Systems</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">


                <!-- Logs Table -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Access Logs</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="(log, index) in paginatedLogs"
                                    :key="index"
                                    class="hover:bg-gray-50 transition-colors duration-150"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ log.student }}</div>
                                                <div class="text-sm text-gray-500">{{ log.program }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        {{ log.action }}
                      </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ log.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatTime(log.time) }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button
                                @click="prevPage"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <button
                                @click="nextPage"
                                :disabled="currentPage === totalPages"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                                    to
                                    <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredLogs.length) }}</span>
                                    of
                                    <span class="font-medium">{{ filteredLogs.length }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button
                                        @click="prevPage"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                    Page {{ currentPage }} of {{ totalPages }}
                  </span>
                                    <button
                                        @click="nextPage"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Frontend>

</template>

<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue'
import { Head } from '@inertiajs/vue3'
</script>

<script>
import { Link, router } from '@inertiajs/vue3'
import { route } from "ziggy-js";

export default {
    components: {
        Link
    },
    data() {
        return {
            menuOpen: false,
            currentPage: 1,
            itemsPerPage: 10,
            searchText: '',
            selectedDate: '2025-06-27',
            selectedProgram: '',
            logs: [
                { time: "06/27/2025|11:35 AM", student: "Froilan Canete", program: "Bachelor of Science in Information Technology", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:36 AM", student: "Maria Santos", program: "Bachelor of Science in Computer Science", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:37 AM", student: "Jose Rivera", program: "Bachelor of Science in Information Systems", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:38 AM", student: "Anna Cruz", program: "Bachelor of Science in Information Technology", action: "Barcode Scan", description: "Exit the Campus" },
                { time: "06/27/2025|11:39 AM", student: "Mark Lopez", program: "Bachelor of Science in Computer Science", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:40 AM", student: "Lisa Garcia", program: "Bachelor of Science in Information Systems", action: "Barcode Scan", description: "Exit the Campus" },
                { time: "06/27/2025|11:41 AM", student: "Tom Anderson", program: "Bachelor of Science in Information Technology", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:42 AM", student: "Amy Gonzalez", program: "Bachelor of Science in Computer Science", action: "Barcode Scan", description: "Enter the Campus" },
                { time: "06/27/2025|11:43 AM", student: "Chris Martinez", program: "Bachelor of Science in Information Systems", action: "Barcode Scan", description: "Exit the Campus" }
            ]
        };
    },

    computed: {
        filteredLogs() {
            const date = this.selectedDate;
            const program = this.selectedProgram;

            // Create a mapping for program codes to full names
            const programMapping = {
                'BSIT': 'Bachelor of Science in Information Technology',
                'BSCS': 'Bachelor of Science in Computer Science',
                'BSIS': 'Bachelor of Science in Information Systems'
            };

            return this.logs.filter(log => {
                const matchesDate = !date || log.time.includes(date.replace(/-/g, '/').slice(5));

                // Fix the program matching logic
                const matchesProgram = !program || log.program === programMapping[program];

                return matchesDate && matchesProgram;
            });
        },

        totalPages() {
            return Math.ceil(this.filteredLogs.length / this.itemsPerPage);
        },

        paginatedLogs() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredLogs.slice(start, start + this.itemsPerPage);
        },

        uniqueUsers() {
            const students = new Set(this.filteredLogs.map(log => log.student));
            return students.size;
        }
    },

    methods: {
        formatTime(timeString) {
            // Convert "06/27/2025|11:35 AM" to "11:35 AM | 06/27/2025"
            const parts = timeString.split('|');
            if (parts.length > 1) {
                const time = parts[1]; // "11:35 AM"
                const date = parts[0]; // "06/27/2025"
                return `${time} | ${date}`;
            }
            return timeString;
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        logout() {
            router.post(route('logout'));
        }
    },

    watch: {
        searchText() {
            this.currentPage = 1;
        },
        selectedDate() {
            this.currentPage = 1;
        },
        selectedProgram() {
            this.currentPage = 1;
        }
    },

};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
