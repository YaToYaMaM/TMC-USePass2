<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ForgotPassLayout from "@/Layouts/ForgotPassLayout.vue";
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const isLoading = ref(false);

const submit = () => {
    isLoading.value = true;

    form.post(route('otp.request'), {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <ForgotPassLayout>
        <div class="relative bg-white rounded-xl drop-shadow-[0px_4px_34px_rgba(118,0,0,0.06)] w-full max-w-md px-8 pt-28 pb-8 min-h-[500px] mt-16">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[120px] max-w-100 h-[120px]  rounded-full shadow-md px-8">
                <img src="/images/ForgotPassword.png" alt="Logo" class="w-50 mt-6 text-center " />
            </div>
        <Head title="Forgot Password" />

        <div class="mb-8 text-md text-gray-600 justify-self-center">
            Enter your email to reset password
        </div>

        <div
            v-if="status"
            class="mb-6 text-sm font-medium text-green-600 text-center"
        >
            {{ status }}
        </div>
            <div v-if="form.errors.email" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm text-center">
                {{ form.errors.email }}
            </div>

        <form @submit.prevent="submit">
            <div class="mb-8">
                <InputLabel for="email" value="" />
                   <label class="block text-[17px] font-normal text-customRed mb-5">Email Address: </label>

                <TextInput
                    id="email"
                    type="email"
                    class="w-full border-0 border-b-2 border-red-900 focus:outline-none focus:border-customRed rounded-sm py-2 pr-10
                            placeholder:text-md placeholder:italic"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

<!--                <InputError class="mt-2" :message="form.errors.email" />-->
            </div>

            <div class="mt-20 flex items-center justify-end">
                <button
                    type="submit"
                    :disabled="form.processing || isLoading"
                    class="block w-60 mx-auto bg-[#760000] hover:bg-red-700 text-white font-semibold py-2 rounded shadow flex justify-center items-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <template v-if="!isLoading">
                        <i class="fas fa-paper-plane"></i>
                        Email Password Reset Link
                    </template>
                    <template v-else>
                        <i class="fas fa-spinner fa-spin" style="color: white; font-size: 18px;"></i>
                        Sending...
                    </template>
                </button>

            </div>
          </form>
        </div>
    </ForgotPassLayout>
</template>
