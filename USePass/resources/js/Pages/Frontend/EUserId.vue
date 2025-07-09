<template>

    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <div class="absolute top-0 left-0 w-full h-12 bg-[#760000] z-20"></div>
        <!-- Overlay layer -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Foreground content -->
        <div class="relative z-10 p-4 rounded-lg text-center max-w-lg w-full">
            <img
                src="/images/Logo1.png"
                alt="USePass Logo"
                class="mx-auto mb-2 w-full max-w-[600px] h-auto"
            />

            <p class="text-white italic text-base md:text-lg lg:text-xl mb-6">
                "Track Student. Ensure Safety. <br>
                USePass."
            </p>

            <input
                v-model="userId"
                type="text"
                placeholder="User ID"
                class="w-full max-w-xs mx-auto px-4 py-2 rounded-md text-center shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
            />

            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6 max-w-xs mx-auto">
                <button
                    @click="selectCampus('Tagum')"
                    class="bg-white hover:bg-gray-100 px-6 py-2 rounded-md font-semibold shadow w-full sm:w-auto"
                >
                    Tagum
                </button>
                <button
                    @click="selectCampus('Mabini')"
                    class="bg-white hover:bg-gray-100 px-6 py-2 rounded-md font-semibold shadow w-full sm:w-auto"
                >
                    Mabini
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "UserIDView",
    data() {
        return {
            userId: "",
        };
    },
    methods: {
        selectCampus(campus) {
            console.log(`User ID: ${this.userId}, Campus: ${campus}`);
            // Add your login navigation or API logic here
        },
    },
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>
