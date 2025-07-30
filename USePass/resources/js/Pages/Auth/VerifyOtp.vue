<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ForgotPassLayout from "@/Layouts/ForgotPassLayout.vue";
import {ref, reactive, onMounted, watch, computed} from 'vue';

const form = useForm({
    otp: '', // full 6-digit code
});

const otpDigits = reactive(['', '', '', '', '', '']);
const props = defineProps({
    otp_start_time: String, // ⏱️ passed from Laravel backend
});

const EXPIRE_SECONDS = 120;
const timer = ref(EXPIRE_SECONDS);
const expired = ref(false);
let interval = null;

const formattedTime = computed(() => {
    const min = Math.floor(timer.value / 60).toString().padStart(1, '0');
    const sec = (timer.value % 60).toString().padStart(2, '0');
    return `${min}:${sec}`;
});

const startTimer = () => {
    interval = setInterval(() => {
        if (timer.value > 0) {
            timer.value--;
        } else {
            expired.value = true;
            clearInterval(interval);
        }
    }, 1000);
};

const focusNext = (i, e) => {
    if (e.inputType === 'deleteContentBackward') return;
    if (otpDigits[i].length === 1 && i < 5) {
        document.getElementById(`otp-${i + 1}`).focus();
    }
};

const handlePaste = (event) => {
    const pasteData = event.clipboardData.getData('text');
    if (!/^\d{6}$/.test(pasteData)) return; // Only proceed if exactly 6 digits

    event.preventDefault(); // Prevent default paste

    for (let i = 0; i < 6; i++) {
        otpDigits[i] = pasteData[i];
    }

    // Optionally focus the last field
    setTimeout(() => {
        document.getElementById('otp-5')?.focus();
    }, 10);
};

const handleSubmit = () => {
    form.otp = otpDigits.join('');
    form.post(route('otp.verify'));
};
onMounted(() => {
    const startTime = new Date(props.otp_start_time);
    const now = new Date();
    const elapsed = Math.floor((now - startTime) / 1000);
    const remaining = EXPIRE_SECONDS - elapsed;

    if (remaining > 0) {
        timer.value = remaining;
        startTimer(); // only start if still valid
    } else {
        timer.value = 0;
        expired.value = true;
    }
});
</script>


<template>
    <ForgotPassLayout>
        <div class="relative bg-white rounded-xl drop-shadow-[0px_4px_34px_rgba(118,0,0,0.06)] w-full max-w-md px-8 pt-28 pb-8 min-h-[500px] mt-16">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[120px] max-w-100 h-[120px] rounded-full shadow-md px-8">
                <img src="/images/ForgotPassword.png" alt="Logo" class="w-50 mt-6 text-center" />
            </div>
            <Head title="Verify OTP" />

            <div class="absolute top-25 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full flex items-center justify-center">
                <h2 class="text-[36px] text-customRed font-extrabold mb-11">OTP Verification</h2>
            </div>
            <div class="w-full flex items-center justify-center">
                <img src="/images/otpICON.png" alt="Logo" class="w-24 mt-5 text-center " />
            </div>

            <form @submit.prevent="handleSubmit" class="p-4">
                <h2 class="text-sm font-light mb-4">Enter your One Time Password</h2>
                <div class="flex justify-center space-x-2 mb-4">
                    <input v-for="(digit, i) in otpDigits" :key="i" v-model="otpDigits[i]"
                           :id="`otp-${i}`"
                           maxlength="1"
                           @input="focusNext(i, $event)"
                           @paste="handlePaste($event)"
                           class="w-12 h-12 text-center border-2 border-red-700 rounded-xl focus:outline-none text-xl"
                           :disabled="expired"
                    />
                </div>

                <div class="text-sm text-red-500 text-center mb-4" v-if="form.errors.otp">{{ form.errors.otp }}</div>

                <p class="text-xs text-right text-red-700 font-medium mb-4">Expires: {{ formattedTime }}</p>

                <button
                    type="submit"
                    class="bg-red-900 text-white py-2 px-4 rounded hover:bg-red-700 w-full shadow"
                    :disabled="form.processing || expired"
                >
                    Reset Password
                </button>

                <p v-if="expired" class="text-xs text-center text-gray-500 mt-3">
                    OTP expired. <a href="/forgot-password" class="text-red-700 underline">Request again</a>.
                </p>
            </form>
        </div>
    </ForgotPassLayout>
</template>
