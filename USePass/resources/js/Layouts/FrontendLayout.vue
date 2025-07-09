<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import Navbar from "@/Components/Navbar.vue";
import Sidebar from "@/Components/Sidebar.vue";
</script>

<template>
 <div class="h-screen overflow-hidden flex flex-col">
     <Navbar/>
     <Sidebar>
         <slot />
     </Sidebar>
 </div>

</template>

