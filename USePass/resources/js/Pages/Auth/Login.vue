<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isLoading = ref(false);
const showPassword = ref(false);

const submit = () => {
    isLoading.value=true;

    form.post(route('login'), {
        onFinish: () => {
            isLoading.value=false;
            form.reset('password')
        }
    });
};
</script>

<template>
    <Head title="USePass" />
    <GuestLayout>
        <div class="relative bg-white rounded-xl drop-shadow-[0px_4px_34px_rgba(118,0,0,0.06)] w-full max-w-md px-8 pt-28 pb-8 min-h-[500px]">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white w-[200px] max-w-100 h-[70px]  rounded-full shadow-md px-8">
                <img src="/images/Logo2.png" alt="Logo" class="w-50 mt-2 text-center " />
            </div>

        <div class="transition-all duration-300"
        :class = "{ 'opacity-30 scale-[0.98] pointer-events-none': isLoading,'opacity-100 scale-100': !isLoading}"
        >

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-[17px] font-normal text-customRed mb-1">Username</label>
                    <div class="relative">
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="Enter your username"
                            class="w-full border-0 border-b-2 border-red-900 focus:outline-none focus:border-customRed rounded-sm  py-2 pr-10
                            placeholder:text-md placeholder:italic"
                        />
                        <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-900">
             <i class="fas fa-user" style="color: #760000;"></i>
            </span>
                    </div>
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-[17px] font-normal text-customRed mb-1">Password</label>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            placeholder="Enter your password"
                            class="w-full border-0 border-b-2 border-red-900 focus:outline-none focus:border-customRed rounded-sm py-2 pr-10
                            placeholder:text-md placeholder:italic"
                        />
                        <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-red-900"
                              @click="showPassword = !showPassword"
                        >
                           <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" style="color: #760000;"></i>
                        </span>
                    </div>
                    <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="flex justify-between items-center text-sm">
                    <label class="flex items-center space-x-2"></label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-red-900 font-semibold hover:underline">
                        Forgot password?
                    </Link>
                </div>

                <button
                    type="submit"
                    class="block w-40 mx-auto bg-red-900 hover:bg-red-700 text-white font-semibold py-2 rounded shadow"
                    :disabled = "isLoading"
                >
                    <template v-if ="!isLoading">
                        <i class="fas fa-sign-in-alt"></i>
                         Login
                    </template>
                    <template v-else>
                        <i class="fas fa-spinner fa-spin" style="color: white; font-size: 20px;"></i>
                        Logging in...
                    </template>
                </button>
            </form>
        </div>
            <div class="mt-8 text-center text-xs text-gray-500">
                © 2025. All Rights Reserved. <br />
                <Link href="#" class="underline mx-1">Terms of Use</Link> |
                <Link href="#" class="underline mx-1">Privacy Policy</Link>
            </div>
        </div>
    </GuestLayout>
</template>
