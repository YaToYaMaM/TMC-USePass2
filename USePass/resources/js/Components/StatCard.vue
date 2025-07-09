<template>
    <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-center items-start">
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <p class="text-3xl font-bold text-black mt-2">{{ value }}</p>
        <p class="text-sm text-gray-400">{{ subtitle }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: String,
    subtitle: String,
})
</script>


