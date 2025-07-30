<script setup lang="ts">
import { ref, computed } from 'vue';
import Frontend from "@/Layouts/FrontendLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import IncidentTable from "@/Components/IncidentTable.vue";
import SpotTable from "@/Components/SpotTable.vue";

const selectedLocation = ref('Tagum');
const selectedDate = ref<string>('');

const props = defineProps<{
    reports: any[];
    spot?: any[];
    auth: any;
}>();

// Check if user is admin
const isAdmin = computed(() => {
    return props.auth?.user?.role === 'admin' || props.auth?.user?.is_admin || false;
});

// Determine current route based on URL
const currentRoute = computed(() => {
    return window.location.pathname;
});

const selectedIncident = computed(() => {
    return currentRoute.value === '/spot-reports' ? 'Spot' : 'Incident';
});

// Navigation functions
const navigateToIncident = () => {
    router.visit('/incident');
};

const navigateToSpot = () => {
    router.visit('/spot-reports');
};
</script>

<template>
    <Head title="USePass" />
    <Frontend>
        <Head title="Incident Report" />
        <div class="flex flex-col p-3 px-12">
            <!-- Top Control Buttons (Admin Only) -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4" v-if="isAdmin">
                <span></span>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Button Group -->
                    <div class="inline-flex justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
                        <button
                            @click="selectedLocation = 'Tagum'"
                            :class="[
        'px-3 py-1 text-sm border-r border-gray-300',
        selectedLocation === 'Tagum'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Tagum
                        </button>
                        <button
                            @click="selectedLocation = 'Mabini'"
                            :class="[
        'px-3 py-1 text-sm',
        selectedLocation === 'Mabini'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Mabini
                        </button>
                    </div>
                </div>

            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Title on the left -->
                <h1 class="text-2xl font-extrabold mb-2 md:mb-0">Incident Report</h1>

                <!-- Date & Dropdown aligned right -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 ml-auto w-full sm:w-auto">
                    <div class="relative w-full md:w-64">
                        <input
                            type="text"
                            placeholder="Search a Incident..."
                            class="w-full border border-gray-300 pl-10 pr-4 py-2 rounded-3xl focus:outline-none"
                        />
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                            <!-- Magnifying Glass SVG Icon -->
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <input
                        type="date"
                        class="border border-gray-300 p-2 rounded w-full sm:w-36 text-sm"
                        v-model="selectedDate"
                    />
                    <div class="inline-flex justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
                        <button
                            @click="navigateToIncident"
                            :class="[
        'px-3 py-1 text-sm border-r border-gray-300',
        selectedIncident === 'Incident'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Incident Report
                        </button>
                        <button
                            @click="navigateToSpot"
                            :class="[
        'px-3 py-1 text-sm',
        selectedIncident === 'Spot'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
                        >
                            Spot Report
                        </button>
                    </div>

                </div>

            </div>
            <div>
                <IncidentTable :reports="props.reports" v-if="selectedIncident === 'Incident'"  :selectedDate="selectedDate" />
                <SpotTable :reports="props.reports" :spot="props.spot || []" v-else-if="selectedIncident === 'Spot'" :selectedDate="selectedDate"/>
            </div>
        </div>
    </Frontend>
</template>
