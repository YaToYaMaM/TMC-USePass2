<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ForgotPassLayout from "@/Layouts/ForgotPassLayout.vue";
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isLoading = ref(false);
const passwordError = ref('');
let passwordErrorTimeout = null;

const validatePassword = (password) => {
    const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&._-])[A-Za-z\d@$!%*#?&._-]{8,}$/;

    if(!pattern.test(password)){
        passwordError.value =  'Password must be at least 8 characters long, and include uppercase, lowercase, number, and special character.';

        if (passwordErrorTimeout) {
            clearTimeout(passwordErrorTimeout);
        }

        passwordErrorTimeout = setTimeout(() => {
            passwordError.value = '';
        }, 3000);

        return false;
    }

    if (form.password !== form.password_confirmation) {
        passwordError.value = 'Passwords do not match. Please re-enter the same password.';

        if (passwordErrorTimeout) {
            clearTimeout(passwordErrorTimeout);
        }

        passwordErrorTimeout = setTimeout(() => {
            passwordError.value = '';
        }, 3000);
        return false;
    }

    passwordError.value = '';
    return true;
};
const submit = () => {
    if(!validatePassword(form.password)){
        return;
    }
    isLoading.value = true;
    form.post(route('password.store'), {
        onFinish: () => {
            isLoading.value=false;
            form.reset('password', 'password_confirmation')
        }
    });
};
</script>

<template>
    <ForgotPassLayout>
        <div class="relative bg-white rounded-xl drop-shadow-[0px_4px_34px_rgba(118,0,0,0.06)] w-full max-w-md px-8 pt-28 pb-8 min-h-[500px] mt-16">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[120px] max-w-100 h-[120px]  rounded-full shadow-md px-8">
                <img src="/images/ForgotPassword.png" alt="Logo" class="w-50 mt-6 text-center " />
            </div>
        <Head title="Reset Password" />

            <div class="mb-8 text-md text-gray-600 justify-self-center">
                Enter new password
            </div>

            <div v-if="passwordError" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm text-center">
                {{ passwordError }}
            </div>
        <form @submit.prevent="submit">
            <div class="mt-4">
                <InputLabel for="password" value="" />
                <label class="block text-[17px] font-normal text-customRed mb-0">New Password</label>

                <div class="relative">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full border-0 border-b-2 border-red-900 focus:outline-none focus:border-customRed rounded-sm py-2 pr-10 placeholder:text-md placeholder:italic"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <span
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-900 cursor-pointer"
                        @click="showPassword = !showPassword"
                    >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" style="color: #760000;"></i>
                  </span>
                </div>

<!--                <InputError class="mt-2" :message="form.errors.password" />-->
            </div>

            <div class="mt-14">
                <InputLabel for="password_confirmation" value=""/>
                <label class="block text-[17px] font-normal text-customRed mb-1">Confirm Password</label>
                <div class="relative mt-4">
                    <TextInput
                        id="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full border-0 border-b-2 border-red-900 focus:outline-none focus:border-customRed rounded-sm py-2 pr-10 placeholder:text-md placeholder:italic"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <span
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-900 cursor-pointer"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                    <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" style="color: #760000;"></i>
                  </span>
                </div>

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-14 flex items-center justify-center">
                <PrimaryButton
                    :class="[
                        'block mx-auto bg-red-900 hover:bg-red-700 text-white font-semibold py-2 rounded shadow',
                        { 'opacity-50 cursor-not-allowed': form.processing }
                    ]"
                    :disabled="form.processing"
                >
                    <span v-if="!isLoading">Update Password</span>
                    <span v-else class="flex items-center justify-center gap-2">
                    <i class="fas fa-spinner fa-spin" style="color: white; font-size: 18px;"></i>
                    Updating...
                </span>
                </PrimaryButton>
            </div>
        </form>
    </div>
    </ForgotPassLayout>
</template>
