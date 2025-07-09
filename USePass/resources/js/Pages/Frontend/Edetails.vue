<script setup lang="ts">

</script>

<template>
    <div
        class="relative min-h-screen bg-cover bg-center px-4 py-8"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <!-- Red nav bar -->
        <!-- Red nav bar with centered logo and tagline -->
        <div class="absolute top-0 left-0 w-full h-20 bg-red-800 z-20 flex flex-col items-center justify-center text-white">
<!--            <img src="/images/Logo1.png" alt="Logo" class="h-10 mb-1" />-->
<!--            <span class="font-bold text-lg">USePass</span>-->
<!--            <span class="text-xs italic">Unified Secure Pass • Campus ID System</span>-->
        </div>
        <!-- Centered Logo + Tagline -->
        <div class="absolute top-14 left-1/2 transform -translate-x-1/2 z-10 flex flex-col items-center mt-2 text-white">
            <img src="/images/Logo1.png" alt="Logo" class="h-10 mb-1" />
            <span class="font-bold text-lg">USePass</span>
            <span class="text-sm italic">Unified Secure Pass • Campus ID System</span>
        </div>



        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60 z-0"></div>

        <div> <img src="/images/Logo1.png" alt="Logo" class="h-10 mr-2" />
            <span class="text-white font-bold text-lg">USePass</span>
        </div>


        <!-- Info Card -->
        <div class="relative z-10 mt-20 bg-white bg-opacity-90 rounded-md shadow-md p-6 max-w-4xl mx-auto space-y-6">
            <!-- User ID -->
            <h2 class="text-black font-semibold text-lg">User ID: 012345</h2>

            <!-- Personal Info -->
            <div>
                <h3 class="font-bold mb-2">Personal Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div><strong>First Name:</strong> Froilan</div>
                    <div><strong>Middle Name:</strong> Doms</div>
                    <div><strong>Surname:</strong> Canete</div>
                    <div><strong>Suffix:</strong> N/A</div>
                    <div><strong>Sex:</strong> Male</div>
                    <div><strong>Contact:</strong> 09123456789</div>
                    <div><strong>Unit:</strong> Tagum</div>
                    <div class="col-span-3"><strong>Email:</strong> fdcanete012345@usep.edu.ph</div>
                </div>
            </div>

            <!-- Guardian Info -->
            <div>
                <h3 class="font-bold mb-2">Parent/Guardian Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div><strong>First Name:</strong> Froilan</div>
                    <div><strong>Middle Name:</strong> Doms</div>
                    <div><strong>Surname:</strong> Canete</div>
                    <div><strong>Suffix:</strong> N/A</div>
                    <div class="md:col-span-2"><strong>Email:</strong>
                        <input
                            type="email"
                            v-model="guardianEmail"
                            placeholder="Enter email account"
                            class="mt-1 px-2 py-1 border border-gray-400 rounded w-full"
                        />
                    </div>
                    <div><strong>Contact:</strong> 09123456789</div>
                </div>

                <!-- Verify Button -->
                <div class="mt-4">
                    <button
                        @click="verify"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm"
                    >
                        Verify
                    </button>
                </div>
            </div>

            <!-- Barcode Section -->
            <div v-if="verified" class="pt-4">
                <h3 class="font-bold mb-2">Barcode</h3>
                <div class="bg-white p-6 rounded shadow-md text-center">
                    <svg id="barcode"></svg>
                    <button
                        @click="downloadBarcode"
                        class="mt-4 bg-red-700 hover:bg-red-800 text-white px-4 py-1 text-sm rounded"
                    >
                        Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import JsBarcode from "jsbarcode";

export default {
    name: "UserDetails",
    data() {
        return {
            guardianEmail: "",
            verified: false,
            userId: "012345",
        };
    },
    methods: {
        verify() {
            if (this.guardianEmail.trim() !== "") {
                this.verified = true;
                this.generateBarcode();
            } else {
                alert("Please enter guardian email.");
            }
        },
        generateBarcode() {
            JsBarcode("#barcode", this.userId, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 80,
                displayValue: true,
            });
        },
        downloadBarcode() {
            const svg = document.querySelector("#barcode");
            const serializer = new XMLSerializer();
            const source = serializer.serializeToString(svg);
            const blob = new Blob([source], { type: "image/svg+xml;charset=utf-8" });
            const url = URL.createObjectURL(blob);

            const link = document.createElement("a");
            link.href = url;
            link.download = "barcode.svg";
            link.click();
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

