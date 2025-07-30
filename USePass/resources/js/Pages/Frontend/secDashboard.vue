<template>
    <Head title="Dashboard" />
  <Frontend>
  <div class="px-3 pt-6 min-h-screen overflow-auto space-y-6 px-12">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Dashboard</h1>
        <div class="inline-flex px-1 py-1 justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit max-h-10">
            <button
                @click="selectUnit('Tagum')"
                :class="[
                    'px-3 py-1 rounded-[5px] font-semibold transition text-center',
                    selectedUnit === 'Tagum'
                      ? 'bg-white text-black shadow'
                      : 'bg-transparent text-black'
                  ]"
                >
              Tagum
            </button>
            <button
                @click="selectUnit('Mabini')"
                :class="[
                    'px-3 py-1 rounded-[5px] font-semibold transition text-center',
                    selectedUnit === 'Mabini'
                      ? 'bg-white text-black shadow'
                      : 'bg-transparent text-black'
                  ]"
                >
              Mabini
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <StatCard title="Student on the Campus" value="4,500" subtitle="Students on the campus" />
      <StatCard title="Visitors" value="0" subtitle="Visitors on the campus" />
      <StatCard title="Alumni" value="0" subtitle="Alumni on the campus" />
    </div>

    <div class="grid grid-cols-1">
      <div class="grid grid-cols-2 md:col-span-2 gap-5">
        <BarChart />
        <StudentsList />
      </div>
    </div>
  </div>
  </Frontend>
</template>


<script setup>
import StatCard from '../../Components/StatCard.vue'
import StudentsList from '../../Components/StudentList.vue'
import BarChart from '../../Components/BarChart.vue'
import Frontend from '@/Layouts/FrontendLayout.vue'
import {ref} from "vue";
import {Head} from "@inertiajs/vue3";
// const selectedLocation = ref('Tagum');
const selectedUnit = ref('Tagum')
function selectUnit(unit) {
  selectedUnit.value = unit
}
</script>
