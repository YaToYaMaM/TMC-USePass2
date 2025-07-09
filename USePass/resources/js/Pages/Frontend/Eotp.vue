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
                src="/images/logo1.png"
                alt="USePass Logo"
                class="mx-auto mb-2 w-full max-w-[600px] h-100"
            />
            <p class="text-white italic text-base md:text-lg lg:text-xl mb-6">
                "Track Student. Ensure Safety. <br>
                USePass."
            </p>
            <h1 class="text-white font-extrabold text-base md:text-lg lg:text-4xl mb-6">
                OTP Verification
            </h1>
            <p class="text-white italic text-base md:text-md lg:text-md mb-6">
                Please Check Your Email.
            </p>

            <!-- Wrapper for inputs and timer -->
            <div class="max-w-xs mx-auto relative">
                <!-- Input boxes -->
                <div class="flex justify-center space-x-2">
                    <input
                        v-for="(char, index) in userId"
                        :key="index"
                        type="tel"
                        maxlength="1"
                        class="w-12 h-12 text-center text-white text-xl rounded-md border border-white bg-white/10 backdrop-blur-md shadow-sm
                   focus:outline-none focus:ring-2 focus:ring-white transition"
                        v-model="userId[index]"
                        @input="onInput(index)"
                        @keydown.backspace="onBackspace(index, $event)"
                        autocomplete="off"
                        placeholder="-"
                        :ref="'input' + index"
                    />
                </div>

                <!-- Timer positioned outside bottom-right -->
                <div
                    class="absolute right-0 -bottom-7 text-sm text-yellow-300 font-semibold select-none"
                    style="user-select: none;"
                >
                    {{ formattedTime }}
                </div>
            </div>
        </div>

        <!-- Timeout message overlay -->
        <div
            v-if="timeoutMessageVisible"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-80 z-50 px-4"
        >
            <div
                class="bg-yellow-300/80 text-white rounded-xl p-8 max-w-sm w-full shadow-2xl text-center
    animate-scaleIn border-1 border-[#a00000] ring ring-[#b22222] ring-opacity-60 relative"
            >

            <!-- Pulsating clock icon -->
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="mx-auto mb-6 h-14 w-14 text-[#760000] animate-pulse"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    role="img"
                    aria-label="Clock icon"
                >
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                </svg>

                <h2 class="text-3xl font-extrabold mb-3 drop-shadow-lg">OTP Expired!</h2>
                <p class="mb-2 text-white-200 font-semibold leading-relaxed">
                    Your session has expired.
                </p>
                <div class="flex justify-center space-x-5">
                    <button
                        @click.prevent="resendOtp"
                        :disabled="isResending"
                        class="bg-[#760000] text-[#FFFFFF] font-bold py-3 px-8 rounded-full shadow-lg
        hover:bg-yellow-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Resend
                    </button>

                    <div
                        class="self-center text-[#760000] font-semibold text-lg animate-pulse select-none"
                    >
                        {{ isResending ? "Resending OTP..." : "Waiting..." }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "UserIDView",
    data() {
        return {
            userId: ["", "", "", "", "", ""],
            timer: 120,
            timerInterval: null,
            timeoutMessageVisible: false,
            isResending: false, // track resend state
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

            // Simulate delay before reload, or reload after successful resend
            setTimeout(() => {
                window.location.reload();
            }, 3000);
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

/* Scale-in animation */
@keyframes scaleIn {
    0% {
        opacity: 0;
        transform: scale(0.85);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-scaleIn {
    animation: scaleIn 0.3s ease forwards;
}
</style>
