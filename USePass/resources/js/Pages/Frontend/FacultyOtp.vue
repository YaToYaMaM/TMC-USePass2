    <script setup>
    import { ref, reactive, onMounted, computed, nextTick } from "vue";
    import { router } from '@inertiajs/vue3';
    import axios from 'axios';

    const isResending = ref(false);
    const errorMessage = ref('');

    const otpDigits = reactive(['', '', '', '', '', '']);
    const props = defineProps({
        otp_start_time: String,
        faculty_id: String,
        faculty_email: String,
    });

    const EXPIRE_SECONDS = 120;
    const timer = ref(EXPIRE_SECONDS);
    const expired = ref(false);
    let interval = null;

    const timerData = ref({
        startTime: null,
        expireSeconds: EXPIRE_SECONDS
    });

    const formattedTime = computed(() => {
        const min = Math.floor(timer.value / 60).toString().padStart(1, '0');
        const sec = (timer.value % 60).toString().padStart(2, '0');
        return `${min}:${sec}`;
    });
    const startTimer = (skipStorage = false) => {
        if (interval) {
            clearInterval(interval);
        }

        expired.value = false;

        // Only set storage if not skipping (i.e., for new OTP)
        if (!skipStorage) {
            timerData.value.startTime = new Date().toISOString();
            timerData.value.expireSeconds = EXPIRE_SECONDS;
        }

        interval = setInterval(() => {
            if (timer.value > 0) {
                timer.value--;
            } else {
                expired.value = true;
                clearInterval(interval);
                timerData.value.startTime = null;
                timerData.value.expireSeconds = EXPIRE_SECONDS;
            }
        }, 1000);
    };

    const resendOtp = async () => {
        isResending.value = true;
        try {
            const response = await axios.post('/faculty/resend-otp');

            if (response.data.success) {
                // Clear existing timer data
                clearInterval(interval);
                timerData.value.startTime = null;
                timerData.value.expireSeconds = EXPIRE_SECONDS;

                // Reset states
                timer.value = EXPIRE_SECONDS;
                expired.value = false;
                otpDigits.fill(''); // Clear OTP inputs
                errorMessage.value = ''; // Clear any errors

                // Start timer (this will set new storage)
                startTimer();

                nextTick(() => {
                    document.getElementById('otp-0')?.focus();
                });
            }
        } catch (error) {
            alert('Failed to resend OTP. Please try again.');

        } finally {
            isResending.value = false;
        }
    };

    const focusNext = (i, e) => {
        if (e.inputType === 'deleteContentBackward') return;
        if (otpDigits[i].length === 1 && i < 5) {
            document.getElementById(`otp-${i + 1}`).focus();
        }
    };

    const focusBack = (i, e) => {
        if (e.key === 'Backspace') {
            if (otpDigits[i] === '' && i > 0) {
                // Move focus back to the previous field
                document.getElementById(`otp-${i - 1}`).focus();
                otpDigits[i - 1] = ''; // Clear previous digit
            }
        }
    };

    const handlePaste = (event) => {
        const pasteData = event.clipboardData.getData('text');
        if (!/^\d{6}$/.test(pasteData)) return;

        event.preventDefault();

        for (let i = 0; i < 6; i++) {
            otpDigits[i] = pasteData[i];
        }

        setTimeout(() => {
            document.getElementById('otp-5')?.focus();
        }, 10);
    };

    const handleSubmit = async () => {
        const otp = otpDigits.join('');

        try {
            const response = await axios.post('/faculty/verify-otp', {
                otp: otp
            });

            if (response.data.success) {
                timerData.value.startTime = null;
                timerData.value.expireSeconds = EXPIRE_SECONDS;

                // Clear interval
                if (interval) {
                    clearInterval(interval);
                }

                // Redirect to success page with QR code generation
                router.visit('/faculty-staff/success', {
                    method: 'get',
                    data: {
                        facultyData: response.data.faculty_data
                    },
                    replace: true
                });
            }
        } catch (error) {
            if (error.response?.data?.error) {
                errorMessage.value = error.response.data.error;
            } else {
                errorMessage.value = 'Verification failed. Please try again.';
            }
        }
    };

    onMounted(() => {
        expired.value = false;

        // Clear any existing interval
        if (interval) {
            clearInterval(interval);
        }

        if (timerData.value.startTime && timerData.value.expireSeconds) {
            // Existing OTP session - calculate remaining time
            const startTime = new Date(timerData.value.startTime);
            const now = new Date();
            const elapsed = Math.floor((now - startTime) / 1000);
            const remaining = parseInt(timerData.value.expireSeconds) - elapsed;

            if (remaining > 0) {
                timer.value = remaining;
                startTimer(true);
            } else {
                timer.value = 0;
                expired.value = true;
            }
        } else if (props.otp_start_time) {
            // New OTP session from backend
            const backendStartTime = new Date(props.otp_start_time);
            const now = new Date();
            const elapsed = Math.floor((now - backendStartTime) / 1000);
            const remaining = EXPIRE_SECONDS - elapsed;

            if (remaining > 0) {
                timer.value = remaining;
                // Set memory data with backend time
                timerData.value.startTime = props.otp_start_time;
                timerData.value.expireSeconds = EXPIRE_SECONDS;
                startTimer(true);
            } else {
                timer.value = 0;
                expired.value = true;
            }
        } else {

            timer.value = EXPIRE_SECONDS;
            startTimer();
        }

        // Focus first input
        nextTick(() => {
            document.getElementById('otp-0')?.focus();
        });
    });
    </script>

    <template>
        <div
            class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
            :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
        >
            <!-- Overlay layer -->
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>

            <!-- Foreground content -->
            <div class="relative z-10 p-4 rounded-lg text-center max-w-lg w-full">
                <img
                    src="/images/Logo1.png"
                    alt="USePass Logo"
                    class="mx-auto mb-2 w-full max-w-[600px] h-100"
                />

                <h2 class="text-white font-extrabold text-base md:text-lg lg:text-4xl mb-6">
                    Faculty/Staff Registration Verification
                </h2>
                <p class="text-white italic text-base md:text-md lg:text-md mb-6">
                    Please Check Your Email for OTP.
                </p>
                <p class="text-white italic text-sm mb-6">
                   <strong> Faculty/Staff ID:</strong> {{ faculty_id }}
                </p>

                <!-- Wrapper for inputs and timer -->
                <div class="max-w-xs mx-auto relative">
                    <div class="flex justify-center space-x-2 mb-8">
                        <input
                            v-for="(digit, i) in otpDigits"
                            :key="i"
                            v-model="otpDigits[i]"
                            :id="`otp-${i}`"
                            maxlength="1"
                            @input="focusNext(i, $event)"
                            @keydown="focusBack(i, $event)"
                            @paste="handlePaste($event)"
                            type="tel"
                            class="w-12 h-12 text-center text-white text-xl rounded-md border border-white bg-white/10 backdrop-blur-md shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-white transition"
                            autocomplete="off"
                            placeholder="-"
                            :disabled="expired"
                        />
                    </div>

                    <!-- Timer positioned below inputs -->
                    <div class="text-center mb-4">
                        <div class="text-sm text-yellow-300 font-semibold select-none">
                            {{ formattedTime }}
                        </div>
                    </div>

                    <!-- Submit button -->
                    <button
                        @click="handleSubmit"
                        :disabled="expired || otpDigits.join('').length !== 6"
                        class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-500 text-white px-4 py-2 rounded-md font-semibold transition"
                    >
                        Verify OTP
                    </button>
                </div>
            </div>

            <div class="text-sm text-red-300 text-center mt-4 mb-4" v-if="errorMessage">
                {{ errorMessage }}
            </div>

            <!-- Timeout message overlay -->
            <div
                v-if="expired"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-80 z-50 px-4"
            >
                <div
                    class="bg-yellow-300/80 text-white rounded-xl p-8 max-w-sm w-full shadow-2xl text-center
                    animate-scaleIn border-1 border-[#a00000] ring ring-[#b22222] ring-opacity-60 relative"
                >
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
                            @click="resendOtp"
                            :disabled="isResending"
                            class="text-sm text-blue-300 underline mt-2"
                            :class="{ 'opacity-50 cursor-not-allowed': isResending }"
                        >
                            {{ isResending ? 'Sending...' : 'Resend OTP' }}
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

