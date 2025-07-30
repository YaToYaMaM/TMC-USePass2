<template>
    <Head title="Statistics" />
    <Frontend>
        <div class="h-screen overflow-y-auto">
            <div class="px-10 pb-12">
                <h1 class="font-bold text-2xl pt-10 pb-4">Statistics</h1>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div class="md:col-span-2">
                        <OverallChart/>
                    </div>
                    <div class="md:col-span-2">
                        <BarChart />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5 pt-6 pb-12">
                    <div class="md:col-span-2">
                        <overallData/>
                    </div>
                    <div class="md:col-span-2">
                        <studentsData/>
                    </div>
                </div>
            </div>
        </div>
    </Frontend>
</template>

<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue';
import {Head} from "@inertiajs/vue3";
import BarChart from "@/Components/BarChart.vue";
import OverallChart from "@/Components/overallChart.vue";
import studentsData from "@/Components/studentsData.vue";
import overallData from "@/Components/overallData.vue";
defineProps({
    title: String
})
</script>
