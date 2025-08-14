<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

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
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024; // lg breakpoint
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

const submit = () => {
    isLoading.value = true;

    form.post(route('login'), {
        onFinish: () => {
            isLoading.value = false;
            form.reset('password')
        }
    });
};
</script>

<template>
    <Head title="USePass" />

    <!-- Mobile Background Overlay - only show on mobile -->
    <div v-if="isMobile"
         class="fixed inset-0 z-0 lg:hidden"
         style="background-image: url('/images/bg.png');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;">
        <div class="absolute inset-0 bg-black/40"></div>
    </div>

    <GuestLayout>
        <div class="relative z-10 bg-white rounded-xl drop-shadow-[0px_4px_34px_rgba(118,0,0,0.06)]
                    w-full max-w-md mx-auto
                    px-4 sm:px-6 md:px-8
                    pt-20 sm:pt-24 md:pt-28
                    pb-6 sm:pb-8
                    min-h-[450px] sm:min-h-[500px]
                    lg:bg-white"
             :class="isMobile ? 'mobile-form' : ''">

            <!-- Logo container - responsive sizing -->
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2
                        bg-white rounded-full shadow-md
                        w-[160px] sm:w-[180px] md:w-[200px]
                        h-[56px] sm:h-[63px] md:h-[70px]
                        px-4 sm:px-6 md:px-8"
                 :class="isMobile ? 'mobile-logo' : ''">
                <img src="/images/Logo2.png" alt="Logo"
                     class="w-full h-auto mt-1 sm:mt-1.5 md:mt-2 object-contain" />
            </div>

            <div class="transition-all duration-300"
                 :class="{ 'opacity-30 scale-[0.98] pointer-events-none': isLoading, 'opacity-100 scale-100': !isLoading }">

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
                    <!-- Username field -->
                    <div>
                        <label class="block text-base sm:text-[17px] font-normal mb-1 sm:mb-2"
                               style="color: #760000;">
                            Username
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="Enter your username"
                                class="w-full border-0 border-b-2 border-red-900
                                       focus:outline-none focus:border-customRed rounded-sm
                                       py-2 sm:py-2.5 pr-10 sm:pr-12
                                       text-sm sm:text-base bg-transparent
                                       placeholder:text-sm sm:placeholder:text-md
                                       placeholder:italic"
                            />
                            <span class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-red-900">
                                <i class="fas fa-user text-sm sm:text-base" style="color: #760000;"></i>
                            </span>
                        </div>
                        <p v-if="form.errors.email" class="text-red-500 text-xs sm:text-sm mt-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password field -->
                    <div>
                        <label class="block text-base sm:text-[17px] font-normal mb-1 sm:mb-2"
                               style="color: #760000;">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Enter your password"
                                class="w-full border-0 border-b-2 border-red-900
                                       focus:outline-none focus:border-customRed rounded-sm
                                       py-2 sm:py-2.5 pr-10 sm:pr-12
                                       text-sm sm:text-base bg-transparent
                                       placeholder:text-sm sm:placeholder:text-md
                                       placeholder:italic"
                            />
                            <span class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2
                                         text-red-900 cursor-pointer touch-manipulation
                                         p-1 -m-1"
                                  @click="showPassword = !showPassword">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                                   class="text-sm sm:text-base"
                                   style="color: #760000;"></i>
                            </span>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs sm:text-sm mt-1">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Forgot password link -->
                    <div class="flex justify-center sm:justify-end items-center text-xs sm:text-sm pt-2">
                        <Link v-if="canResetPassword"
                              :href="route('password.request')"
                              class="text-red-900 font-semibold hover:underline touch-manipulation">
                            Forgot password?
                        </Link>
                    </div>

                    <!-- Login button -->
                    <div class="pt-2 sm:pt-4">
                        <button
                            type="submit"
                            class="block w-full sm:w-40 mx-auto
                                   bg-red-900 hover:bg-red-700 active:bg-red-800
                                   text-white font-semibold
                                   py-3 sm:py-2
                                   text-base sm:text-sm
                                   rounded shadow
                                   touch-manipulation
                                   transition-colors duration-200"
                            :disabled="isLoading">
                            <template v-if="!isLoading">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login
                            </template>
                            <template v-else>
                                <i class="fas fa-spinner fa-spin mr-2" style="color: white;"></i>
                                Logging in...
                            </template>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer - responsive text and spacing -->
            <div class="mt-6 sm:mt-8 text-center text-xs text-gray-500 leading-relaxed">
                © 2025. All Rights Reserved. <br class="sm:hidden" />
                <span class="hidden sm:inline"><br /></span>
                <Link href="#" class="underline mx-1 touch-manipulation">Terms of Use</Link> |
                <Link href="#" class="underline mx-1 touch-manipulation">Privacy Policy</Link>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Mobile-specific styles */
.mobile-form {
    background-color: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.mobile-logo {
    background-color: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Ensure mobile background shows */
@media (max-width: 1023px) {
    :deep(.bg-\[#F5F5F5\]) {
        background: transparent !important;
    }
}
</style>
