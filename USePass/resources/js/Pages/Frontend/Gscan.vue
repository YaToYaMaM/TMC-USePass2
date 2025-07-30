<template>
    <Head title="USePass" />
    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <!-- Overlay layer -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Foreground content -->
        <div class="relative z-10 p-4 rounded-lg text-center max-w-lg w-full">
            <div class="relative inline-block mx-auto max-w-[600px] w-full">
                <img
                    src="/images/logo4.png"
                    alt="USePass Logo"
                    class="mx-auto w-full h-auto"
                />
                <p class="text-white italic text-base md:text-lg lg:text-xl mb-6">
                    "Track Student. Ensure Safety. USePass."
                </p>                <!-- Scanner bar -->
                <div class="corner-border relative">
                    <div class="absolute origin-center w-full h-full">
                        <div class="scanner-bar"></div>
                        <div class="scan-line"></div>
                    </div>
                </div>

            </div>

            <!-- Barcode Scanner Camera -->
            <div
                v-show="false"
                class="mt-8 max-w-[600px] mx-auto rounded-lg overflow-hidden shadow-lg"
            >
                <StreamBarcodeReader
                    @decode="onDecode"
                    @init="onInit"
                    class="w-full h-64 rounded-lg object-cover"
                />
                <p class="mt-2 text-yellow-300 font-semibold select-text break-words">
                    Scanned Code: {{ scannedCode || 'No code detected yet' }}
                </p>
            </div>
            <!--input user id-->
            <div class="flex items-center justify-center pt-5">
                <div class="relative w-full max-w-xs text-center">
                    <input
                        type="text"
                        class="max-w-100 px-4 py-1 text-center rounded-[10px] bg-black bg-opacity-40 border border-white border-opacity-40 text-white placeholder-white placeholder-opacity-60 focus:outline-none"
                        placeholder=" "
                    />
                    <p class="text-white mt-2">User ID</p>
                </div>
            </div>
            <!--Checking button-->
            <div class="fixed bottom-10 left-20 z-50">
                <button class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow">
                    Check
                </button>
            </div>
            <!--Back button-->
            <div class="fixed bottom-10 right-20 z-50">
                <Link :href="route('guard.ghome')" class="text-white p-3 rounded-full hover:bg-white hover:bg-opacity-10 transition inline-block">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        class="w-8 h-8"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l-7 7 7 7M22 12H3" />
                    </svg>
                </Link>
            </div>


        </div>
    </div>
    <router-view />
</template>

<script>
import {Head, Link} from '@inertiajs/vue3'
import { StreamBarcodeReader } from "vue-barcode-reader";
import TextInput from "@/Components/TextInput.vue";

export default {
    name: "UserIDView",
    components: {Head, Link, TextInput, StreamBarcodeReader },
    data() {
        return {
            userId: ["", "", "", "", "", ""],
            timer: 120,
            timerInterval: null,
            timeoutMessageVisible: false,
            isResending: false,
            scannedCode: null,
        };
    },
    computed: {
        formattedTime() {
            const minutes = Math.floor(this.timer / 60)
                .toString()
                .padStart(2, "0");
            const seconds = (this.timer % 60).toString().padStart(2, "0");
            return `${minutes}:${seconds}`;
        },
    },
    methods: {
        onInput(index) {
            this.userId[index] = this.userId[index].replace(/\D/g, "");
            if (this.userId[index].length > 1) {
                this.userId[index] = this.userId[index].slice(0, 1);
            }
            if (this.userId[index] && index < this.userId.length - 1) {
                const nextInput = this.$refs["input" + (index + 1)];
                if (nextInput && nextInput[0]) {
                    nextInput[0].focus();
                }
            }
        },
        onBackspace(index, event) {
            if (this.userId[index] === "" && index > 0) {
                const prevInput = this.$refs["input" + (index - 1)];
                if (prevInput && prevInput[0]) {
                    prevInput[0].focus();
                    event.preventDefault();
                }
            }
        },
        getUserIdString() {
            return this.userId.join("");
        },
        startTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timer = 120;
            this.timeoutMessageVisible = false;
            this.isResending = false;
            this.timerInterval = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                } else {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                    this.timeoutMessageVisible = true;
                    // Do NOT reload automatically here
                }
            }, 1000);
        },
        resendOtp() {
            this.isResending = true;
            // TODO: Add your OTP resend logic here (e.g., API call)
            console.log("Resending OTP...");

            setTimeout(() => {
                window.location.reload();
            }, 3000);
        },
        onDecode(result) {
            this.scannedCode = result;
            console.log("Decoded barcode:", result);
            // Optional: You can auto-fill or trigger actions here
        },
        onInit(promise) {
            promise.catch((error) => {
                console.error("Camera initialization failed:", error);
            });
        },
    },
    mounted() {
        this.startTimer();
    },
    beforeUnmount() {
        if (this.timerInterval) clearInterval(this.timerInterval);
    },
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* Scanner bar animation */
.scanner-bar {
    position: absolute; /* position absolute to position it relative to .corner-border */
    top: 50%;
    left: 50%;
    width: 60px;
    height: 60px;
    overflow: hidden;
    background-color: transparent;
    transform: translate(-50%, -50%);
}


/* Barcode vertical lines inside the square */
.scanner-bar::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: repeating-linear-gradient(
        to right,
        white 0px, white 2px,
        transparent 2px, transparent 4px,
        white 4px, white 5px,
        transparent 5px, transparent 8px,
        white 8px, white 12px,
        transparent 12px, transparent 14px,
        white 14px, white 15px,
        transparent 15px, transparent 18px,
        white 18px, white 21px,
        transparent 21px, transparent 24px,
        white 24px, white 26px,
        transparent 26px, transparent 29px,
        white 29px, white 31px,
        transparent 31px, transparent 34px,
        white 34px, white 40px,
        transparent 40px
    );
    opacity: 1;
}

.corner-border {
    position: relative;
    width: 85px;
    height: 85px;
    margin: 0 auto; /* centers horizontally */
}


.corner-border::before,
.corner-border::after,
.corner-border > div::before,
.corner-border > div::after {
    content: "";
    position: absolute;
    width: 20px;   /* length of corner lines */
    height: 20px;
    border: 3px solid white; /* thickness and color of corner lines */
    border-radius: 3px;
}

/* Top-left corner */
.corner-border::before {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
}

/* Top-right corner */
.corner-border::after {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
}

/* Bottom-left corner */
.corner-border > div::before {
    bottom: 0;
    left: 0;
    border-top: none;
    border-right: none;
}

/* Bottom-right corner */
.corner-border > div::after {
    bottom: 0;
    right: 0;
    border-top: none;
    border-left: none;
}

@keyframes barcode-scan-move {
    0% {
        left: -300%;
    }
    100% {
        left: 0;
    }
}

.scan-line {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: white;
    animation: scan-vertical 2.5s ease-in-out infinite;
    z-index: 5;
    opacity: 0.8;
    border-radius: 1px;
}

@keyframes scan-vertical {
    0% {
        top: 0%;
    }
    50% {
        top: 90%;
    }
    100% {
        top: 0%;
    }
}


@keyframes scan {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
</style>
