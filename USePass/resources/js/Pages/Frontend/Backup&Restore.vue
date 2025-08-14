<script setup lang="ts">
import Frontend from "@/Layouts/FrontendLayout.vue";
import axios from "axios";
import { ref } from 'vue';

const isBackingUp = ref(false);
const isRestoring = ref(false);

const backupDatabase = async () => {
    isBackingUp.value = true;

    try {
        const response = await axios.get("/backupData", { responseType: "blob" });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `database-backup-${new Date().toISOString().split('T')[0]}.sql`);
        document.body.appendChild(link);
        link.click();

        // Clean up
        window.URL.revokeObjectURL(url);
        document.body.removeChild(link);

        alert("Database backup downloaded successfully!");

    } catch (error: any) {
        console.error("Backup error:", error);
        const errorMsg = error.response?.data?.error || "Backup failed!";
        alert(errorMsg);
    } finally {
        isBackingUp.value = false;
    }
};

const restoreDatabase = async (event: Event) => {
    const fileInput = event.target as HTMLInputElement;
    if (!fileInput.files?.length) return;

    const file = fileInput.files[0];

    // Validate file type
    if (!file.name.toLowerCase().endsWith('.sql')) {
        alert("Please select a valid SQL file");
        fileInput.value = '';
        return;
    }

    // Confirm restoration
    if (!confirm("Are you sure you want to restore the database? This will overwrite all current data.")) {
        fileInput.value = '';
        return;
    }

    isRestoring.value = true;

    try {
        const formData = new FormData();
        formData.append("backup_file", file);

        await axios.post("/restoreData", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        alert("Database restored successfully!");

    } catch (error: any) {
        console.error("Restore error:", error);
        const errorMsg = error.response?.data?.error || "Restore failed!";
        alert(errorMsg);
    } finally {
        isRestoring.value = false;
        fileInput.value = '';
    }
};
</script>

<template>
    <Frontend>
        <div class="flex gap-4 justify-center mt-10">
            <button
                @click="backupDatabase"
                :disabled="isBackingUp || isRestoring"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105 active:scale-95 disabled:transform-none"
            >
                <span v-if="isBackingUp">⏳ Backing up...</span>
                <span v-else>📦 Back Up</span>
            </button>

            <label
                :class="{
                    'px-6 py-3 font-semibold rounded-xl shadow-lg transition transform cursor-pointer': true,
                    'bg-orange-600 hover:bg-orange-700 text-white hover:scale-105 active:scale-95': !isRestoring && !isBackingUp,
                    'bg-gray-400 text-white cursor-not-allowed': isRestoring || isBackingUp
                }"
            >
                <span v-if="isRestoring">⏳ Restoring...</span>
                <span v-else>♻️ Restore</span>
                <input
                    type="file"
                    class="hidden"
                    accept=".sql"
                    :disabled="isRestoring || isBackingUp"
                    @change="restoreDatabase"
                />
            </label>
        </div>

        <!-- Optional: Progress indicator -->
        <div v-if="isBackingUp || isRestoring" class="flex justify-center mt-4">
            <div class="text-gray-600">
                {{ isBackingUp ? 'Creating database backup...' : 'Restoring database...' }}
            </div>
        </div>
    </Frontend>
</template>
