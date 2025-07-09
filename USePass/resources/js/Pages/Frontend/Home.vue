<template>
    <FrontendLayout>


    </FrontendLayout>


</template>

<script setup>
import { ref } from 'vue';
import FrontendLayout from "@/Layouts/FrontendLayout.vue";

import { Head } from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import JsBarcode from 'jsbarcode';
import { onMounted } from 'vue';


onMounted(() => {
    JsBarcode("#barcode-1", "2022-00381", {
        format: "CODE128",
        lineColor: "#000",
        width: 1,
        height: 10,
        displayValue: false
    });

    JsBarcode("#barcode-2", "2022-00167", {
        format: "CODE128",
        lineColor: "#000",
        width: 1,
        height: 10,
        displayValue: false
    });
});
// Sidebar toggle state
const isCollapsed = ref(false);
</script>
