<template>
    <Head title="Logs" />
    <Frontend>
        <div class="px-12 pt-8 min-h-screen overflow-auto space-y-6">
            <div>
                <TableLogs/>
            </div>
        </div>

    </Frontend>
</template>



<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue';
import TableLogs from "@/Components/TableLogs.vue";
import {ref} from "vue";
import {Head} from "@inertiajs/vue3";
defineProps({
    title: String
})

// const selectedLocation = ref('Tagum');
const selectedUnit = ref('Tagum')
function selectUnit(unit) {
    selectedUnit.value = unit
}
</script>
