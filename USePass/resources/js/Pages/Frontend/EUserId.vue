<template>
    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >

        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <div class="min-h-screen flex items-center justify-center">
            <div class="relative z-10 p-2 rounded-lg text-center max-w-lg w-full mb-20">
                <img
                    src="/images/logo3.png"
                    alt="USePass Logo"
                    class="mx-auto mb-7 w-[1000px] min-w-64 h-auto"
                />

                <!-- Student ID input -->
                <input
                    v-model="userId"
                    type="text"
                    placeholder="User ID"
                    class="w-full max-w-xs mx-auto px-4 py-2 rounded-md text-center shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                    @keyup.enter="fetchStudentData"
                />

                <button
                    v-if="!isRedirecting && !hasClickedContinue"
                    @click="fetchStudentData"
                    :disabled="loading || !userId.trim()"
                    class="mt-4 bg-[#760000] hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-md font-semibold shadow w-full max-w-xs"
                >
                    {{ loading ? 'Loading...' : 'Continue' }}
                </button>

                <!-- Status message -->
                <div v-if="statusMessage" class="mt-4 p-3 rounded-md max-w-xs mx-auto" :class="statusMessageClass">
                    <div class="text-sm font-medium text-green-500">{{ statusMessage }}</div>
                    <div v-if="statusDetails" class="text-xs mt-1 opacity-90">{{ statusDetails }}</div>
                </div>

                <!-- Error message -->
                <div v-if="error" class="mt-4 text-red-300 text-sm">
                    {{ error }}
                </div>

                <!-- Proceed button (shown after status check) -->
                <button
                    v-if="canProceed && !isRedirecting"
                    @click="proceedToDetails"
                    class="mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold shadow w-full max-w-xs"
                >
                    {{ proceedButtonText }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "UserIDView",
    data() {
        return {
            userId: "",
            loading: false,
            error: null,
            hasClickedContinue: false,
            // Status tracking
            studentData: null,
            parentData: null,
            statusMessage: "",
            statusDetails: "",
            statusMessageClass: "",
            canProceed: false,
            isRedirecting: false,
            redirectStep: 1,

            // Status flags
            studentHasContact: false,
            parentHasContact: false
        };
    },
    computed: {
        proceedButtonText() {
            if (this.redirectStep === 3) {
                return "Generate Barcode";
            } else if (this.redirectStep === 2) {
                return "Update Parent Info";
            } else {
                return "Complete Registration";
            }
        }
    },
    methods: {
        async fetchStudentData() {
            if (!this.userId.trim()) {
                this.error = 'Please enter a User ID';
                return;
            }

            this.loading = true;
            this.error = null;
            this.statusMessage = "";
            this.canProceed = false;
            this.hasClickedContinue = true;

            try {
                const response = await axios.post('/student/get-data', {
                    students_id: this.userId.trim()
                });

                if (response.data.success) {
                    this.studentData = response.data.data.student;
                    this.parentData = response.data.data.parent;

                    // Get status info
                    const status = response.data.status;
                    this.studentHasContact = status.student_has_contact;
                    this.parentHasContact = status.parent_has_contact;
                    this.redirectStep = status.redirect_step;

                    this.statusMessage = status.message;

                    // Auto-redirect if no contact info
                    if (!this.studentHasContact && !this.parentHasContact) {
                        this.isRedirecting = true;
                        this.redirectStep = 1;
                        this.$inertia.visit(`/Details?step=${this.redirectStep}`, {
                            data: {
                                studentData: this.studentData,
                                parentData: this.parentData,
                                studentHasContact: this.studentHasContact,
                                parentHasContact: this.parentHasContact
                            },
                            method: 'get'
                        });
                    } else {
                        // Display status info for partial or complete data
                        this.setStatusDisplay();
                        this.canProceed = true;
                    }

                }
                else {
                    // Reset flag if request failed
                    this.hasClickedContinue = false;
                }
            } catch (error) {

                this.hasClickedContinue = false;
                if (error.response?.status === 404) {
                    this.error = 'Student not found';
                } else {
                    this.error = 'An error occurred. Please try again.';
                }
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },
        setStatusDisplay() {
            if (this.studentHasContact && this.parentHasContact) {
                // Both complete - green
                this.statusMessageClass = "bg-green-100 text-green-800 border border-green-200";
                this.statusDetails = "✓ Student contact: Complete  ✓ Parent contact: Complete";
            } else if (this.studentHasContact && !this.parentHasContact) {
                // Student complete, parent missing - yellow
                this.statusMessageClass = "bg-yellow-100 text-yellow-800 border border-yellow-200";
                this.statusDetails = "✓ Student contact: Complete  ⚠ Parent contact: Missing";
            } else {
                // Student missing - blue (needs registration)
                this.statusMessageClass = "bg-blue-100 text-blue-800 border border-blue-200";
                this.statusDetails = "⚠ Student contact: Missing  ⚠ Parent contact: Missing";
            }
        },
        proceedToDetails() {
            if (!this.canProceed) return;

            if (this.studentHasContact && !this.parentHasContact) {
                this.$inertia.visit(`/Details?step=1&mode=parent_update`, {
                    data: {
                        studentData: this.studentData,
                        parentData: this.parentData,
                        studentHasContact: this.studentHasContact,
                        parentHasContact: this.parentHasContact,
                        requiresOtp: true
                    },
                    method: 'get'
                });
                return;
            }

            this.$inertia.visit(`/Details?step=${this.redirectStep}`, {
                data: {
                    studentData: this.studentData,
                    parentData: this.parentData,
                    studentHasContact: this.studentHasContact,
                    parentHasContact: this.parentHasContact
                },
                method: 'get'
            });
        }
    }
};
</script>

<style scoped>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

</style>
