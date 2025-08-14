<template xmlns="http://www.w3.org/1999/html">
    <Head title="USePass" />
    <div class="min-h-screen bg-gray-50">
        <!-- Header Section with Controls -->
        <div class="bg-white shadow-sm border-b top-0 z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end space-y-4 sm:space-y-0">
                    <!-- Date Filter and Refresh Button -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Date:</label>
                            <input
                                type="date"
                                v-model="selectedDate"
                                @change="fetchLogs"
                                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>
                        <button
                            @click="fetchLogs"
                            :disabled="loading"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ loading ? 'Loading...' : 'Refresh' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

            <!-- Error Message -->
            <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> {{ error }}</span>
            </div>

            <!-- Logs Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">My Recent Activity</h3>
                    <p class="mt-1 text-sm text-gray-500">Total: {{ totalLogs }} logs</p>
                </div>

                <div class="overflow-x-auto">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date | Time</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="loading && logs.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                    <div class="flex items-center justify-center">
                                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Loading logs...
                                    </div>
                                </td>
                            </tr>
                            <tr v-else-if="filteredLogs.length === 0">
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                    No logs found for the selected date.
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="(log, index) in paginatedLogs"
                                :key="log.log_id || index"
                                class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <td class="px-2 py-4 whitespace-nowrap">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ log.user ? log.user.name : 'Unknown User' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-4 whitespace-nowrap">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ log.role }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="getActionClass(log.log_action)">
                                        {{ log.log_action }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ log.log_description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatTime(log.created_at) }}
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
</template>

<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue'
import { Head } from '@inertiajs/vue3'

// Props to receive current user data from backend
const props = defineProps({
    auth: Object, // Contains authenticated user data
});
</script>

<script>
import { Link, router } from '@inertiajs/vue3'
import { route } from "ziggy-js";
import axios from 'axios';

export default {
    components: {
        Link
    },
    props: {
        auth: Object
    },
    data() {
        return {
            menuOpen: false,
            currentPage: 1,
            itemsPerPage: 10,
            selectedDate: new Date().toISOString().split('T')[0], // Today's date
            logs: [],
            loading: false,
            error: null,
            totalLogs: 0
        };
    },

    computed: {
        currentUserId() {
            return this.auth?.user?.id || this.$page.props.auth?.user?.id;
        },

        filteredLogs() {
            let filtered = this.logs;

            // Filter by current user ID
            if (this.currentUserId) {
                filtered = filtered.filter(log => log.user_id === this.currentUserId);
            }

            // Filter by selected date
            if (this.selectedDate) {
                filtered = filtered.filter(log => {
                    const logDate = new Date(log.created_at).toISOString().split('T')[0];
                    return logDate === this.selectedDate;
                });
            }

            return filtered;
        },

        totalPages() {
            return Math.ceil(this.filteredLogs.length / this.itemsPerPage);
        },

        paginatedLogs() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredLogs.slice(start, start + this.itemsPerPage);
        }
    },

    methods: {
        async fetchLogs() {
            this.loading = true;
            this.error = null;

            try {
                // Send current user ID as parameter to backend
                const response = await axios.get('/activity-logs', {
                    params: {
                        per_page: 100, // Fetch more records
                        user_id: this.currentUserId // Send current user ID to backend
                    }
                });

                if (response.data.success) {
                    this.logs = response.data.data.data || response.data.data;
                    this.totalLogs = this.filteredLogs.length; // Update to show filtered count
                } else {
                    this.error = response.data.message || 'Failed to fetch logs';
                }
            } catch (error) {
                console.error('Error fetching logs:', error);
                this.error = error.response?.data?.message || 'Failed to fetch activity logs';
            } finally {
                this.loading = false;
            }
        },

        formatTime(timestamp) {
            if (!timestamp) return 'N/A';

            const date = new Date(timestamp);
            const dateStr = date.toLocaleDateString('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric'
            });
            const timeStr = date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            return `${dateStr} | ${timeStr}`;
        },

        getActionClass(action) {
            const actionClasses = {
                'Log In': 'bg-green-100 text-green-800',
                'Log Out': 'bg-red-100 text-red-800',
                'Barcode Scan': 'bg-blue-100 text-blue-800'
            };
            return actionClasses[action] || 'bg-blue-300 text-gray-800';
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
        selectedDate() {
            this.currentPage = 1;
        }
    },

    mounted() {
        if (!this.currentUserId) {
            this.error = 'User not authenticated';
            return;
        }
        this.fetchLogs();
    }
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
