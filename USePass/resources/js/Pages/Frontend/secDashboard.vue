<template>
  <Frontend>
  <div class="pt-6  min-h-screen space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Dashboard</h1>
<!--      <div class="inline-flex justify-center gap-1 mt-6 bg-gray-300 p-1 rounded-md shadow max-w-fit mx-auto">-->
          <div class="inline-flex justify-center text-[0.775rem] bg-gray-100 p-0.5 rounded-md shadow max-w-fit ">
        <button
            @click="selectUnit('Tagum')"
            :class="[
        'px-4 py-2 rounded-md font-semibold transition text-center',
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
        'px-4 py-2 rounded-md font-semibold transition text-center',
        selectedUnit === 'Mabini'
          ? 'bg-white text-black shadow'
          : 'bg-transparent text-black'
      ]"
        >
          Mabini
        </button>
      </div>
<!--      <div class="inline-flex border border-gray-300 rounded overflow-hidden">-->
<!--        <button-->
<!--            @click="selectedLocation = 'Tagum'"-->
<!--            :class="[-->
<!--        'px-3 py-1 text-sm border-r border-gray-300',-->
<!--        selectedLocation === 'Tagum'-->
<!--          ? 'bg-gray-400 text-white font-semibold'-->
<!--          : 'bg-white text-gray-600'-->
<!--      ]"-->
<!--        >-->
<!--          Tagum-->
<!--        </button>-->
<!--        <button-->
<!--            @click="selectedLocation = 'Mabini'"-->
<!--            :class="[-->
<!--        'px-3 py-1 text-sm',-->
<!--        selectedLocation === 'Mabini'-->
<!--          ? 'bg-gray-400 text-white font-semibold'-->
<!--          : 'bg-white text-gray-600'-->
<!--      ]"-->
<!--        >-->
<!--          Mabini-->
<!--        </button>-->
<!--      </div>-->
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <StatCard title="Student on the Campus" value="4,500" subtitle="Students on the campus" />
      <StatCard title="Visitors" value="0" subtitle="Visitors on the campus" />
      <StatCard title="Alumni" value="0" subtitle="Alumni on the campus" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        <BarChart />
      </div>
      <StudentsList />
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
// const selectedLocation = ref('Tagum');
const selectedUnit = ref('Tagum')
function selectUnit(unit) {
  selectedUnit.value = unit
}
</script>
