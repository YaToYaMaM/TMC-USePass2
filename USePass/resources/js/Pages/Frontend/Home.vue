<template>
    <Head title="USePass" />
    <FrontendLayout>
        <div class="p-4">
            <!-- Add the target SVG elements -->
            <svg id="barcode-1"></svg>
            <svg id="barcode-2"></svg>
        </div>
    </FrontendLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import FrontendLayout from "@/Layouts/FrontendLayout.vue";
import JsBarcode from 'jsbarcode';
import {Head} from "@inertiajs/vue3";

// Sidebar toggle state
const isCollapsed = ref(false);

onMounted(() => {
    // Ensure the SVG elements exist before rendering
    JsBarcode("#barcode-1", "2022-00381", {
        format: "CODE128",
        lineColor: "#000",
        width: 1,
        height: 40,
        displayValue: false
    });

    JsBarcode("#barcode-2", "2022-00167", {
        format: "CODE128",
        lineColor: "#000",
        width: 1,
        height: 40,
        displayValue: false
    });
});
</script>
