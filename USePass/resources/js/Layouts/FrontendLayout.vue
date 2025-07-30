<!-- Layout.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from "@/Components/Sidebar.vue";
import Navbar from "@/Components/Navbar.vue";

// Define the expected shape of the user
interface User {
    id: number
    name: string
    email: string
    role: string
}

const page = usePage<{ auth: { user: User | null } }>()

const user = computed(() => page.props.auth.user)
const isAdmin = computed(() => user.value?.role === 'admin')
const isGuard = computed(() => user.value?.role === 'guard')
</script>

<template>
    <div class="h-screen overflow-hidden flex flex-col">
        <!-- Navbar for admin and guard -->
        <Navbar v-if="isAdmin || isGuard"/>
        <!-- Sidebar with content for admin and guard -->
        <Sidebar v-if="isAdmin || isGuard">
            <slot />
        </Sidebar>
    </div>
</template>
