<template>
    <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-center items-start hover:shadow-md transition-shadow duration-200">
        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ title }}</p>
        <p class="text-3xl font-bold text-black mt-2">{{ formattedValue }}</p>
        <p class="text-sm text-gray-400">{{ subtitle }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        default: 0
    },
    subtitle: {
        type: String,
        default: ''
    },
})

// Format the value to handle numbers with commas for large values
const formattedValue = computed(() => {
    if (typeof props.value === 'number') {
        return props.value.toLocaleString()
    }
    return props.value
})
</script>
