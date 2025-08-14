<template>
    <Head title="USePass" />

    <!-- ✅ Navbar Start -->
    <nav class="flex fixed items-center justify-between bg-black bg-opacity-70 text-white px-6 py-3 shadow-md w-full z-50">
        <!-- Left Buttons -->
        <div class="flex items-center space-x-4">
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                FOR CHECKING
            </button>
            <button
                @click="showIncidentModal = true"
                class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out"
            >
                Incident Report
            </button>
            <button
                @click="showSpotModal = true"
                class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Spot Report
            </button>
            <button
                @click="showStudentAttendanceModal = true"
                class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Students
            </button>
            <button
                @click="showFacultyAndStaffAttendanceModal = true"
                class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Faculty & Staff
            </button>
            <button
                @click="showLogsModal = true"
                class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">Logs</button>
        </div>

        <!-- Center Search -->
        <div class="flex items-center space-x-6 justify-end">
            <div class="flex items-center space-x-2 border-r-2">
                <input
                    type="text"
                    placeholder="Search Attendance..."
                    class="bg-black bg-opacity-40 text-white px-3 py-1 rounded-[10px] border border-white border-opacity-30 placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-1 focus:ring-white"
                />
                <button class="text-white pr-8">
                    Search
                </button>
            </div>
            <!-- Right Profile -->
            <div class="flex items-center space-x-3">
                <div class="text-sm text-right">
                    <div class="font-semibold uppercase">{{ user.first_name }} {{ user.last_name}}</div>
                    <div class="text-xs text-gray-300">{{ user.role === 'guard' ? 'Security Guard' : 'Unknown'}}</div>
                </div>
                <img
                    src="/images/profile.png"
                    alt="Profile"
                    class="w-10 h-10 rounded-full border border-white object-cover"
                />
                <button @click="menuOpen = !menuOpen" class="focus:outline-none" aria-label="Toggle menu">
                    <svg class="h-5 w-5 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <transition name="fade">
                    <div v-if="menuOpen" class="absolute right-0 top-full mt-2 w-40 sm:w-48 bg-black rounded-lg shadow-lg border border-[#ffffff] z-[99999]">
                        <div class="flex items-center space-x-2 sm:space-x-3 px-3 py-2 border-b border-[#ffffff]">
                            <img src="/images/profile.png" alt="Profile" class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white" />
                            <div>
                                <div class="font-semibold uppercase">{{ user.first_name }} {{ user.last_name}}</div>
                                <div class="text-xs text-gray-300">{{ user.role === 'guard' ? 'Security Guard' : 'Unknown'}}</div>
                            </div>
                        </div>
                        <nav class="flex flex-col px-3 py-2 space-y-1 sm:space-y-2">
                            <a href="#" class="text-white font-bold text-xs sm:text-sm hover:underline">CHANGE PASSWORD</a>
                            <button @click="logout" class="text-white font-bold text-xs sm:text-sm hover:underline text-left">LOGOUT</button>
                        </nav>
                    </div>
                </transition>
            </div>
        </div>
    </nav>
    <!-- ✅ Navbar End -->

    <div
        class="relative min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4"
        :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }"
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <div class="relative z-10 p-4 rounded-lg text-center max-w-lg w-full">
            <!-- Logo + Motto -->
            <div class="relative inline-block mx-auto max-w-[600px] w-full">
                <img src="/images/logo4.png" alt="USePass Logo" class="mx-auto w-full h-auto" />
                <p class="text-white italic text-base md:text-lg lg:text-xl mb-6">
                    "Track Student. Ensure Safety. USePass."
                </p>
                <div class="corner-border relative">
                    <div class="absolute origin-center w-full h-full">
                        <div class="scanner-bar"></div>
                        <div class="scan-line"></div>
                    </div>
                </div>
            </div>

            <!-- User ID input -->
            <div class="flex items-center justify-center pt-5">
                <div class="relative w-full max-w-xs text-center">
                    <input
                        type="text"
                        v-model="userIdInput"
                        @keyup.enter="checkStudent(false)"
                        class="max-w-100 px-4 py-1 text-center rounded-[10px] bg-black bg-opacity-40 border border-white border-opacity-40 text-white placeholder-white placeholder-opacity-60 focus:outline-none"
                        placeholder="Enter Student ID"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Student Profile Modal -->
    <transition name="fade">
        <div
            v-if="showProfileModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 flex flex-col items-center px-4 sm:px-8 md:px-12 lg:px-20 py-6 md:py-10
                w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl 2xl:max-w-2xl space-y-6 md:space-y-8
                bg-white rounded-xl shadow-lg text-center"
            >
                <!-- Close Button -->
                <button
                    @click="showProfileModal = false"
                    class="absolute top-3 right-3 text-gray-600 hover:text-red-500 text-xl"
                >
                    ×
                </button>

                <!-- Logo -->
                <img src="/images/Logo2.png" alt="USePass Logo" class="mb-2 w-48 sm:w-56 md:w-64 lg:w-80 h-auto" />

                <!-- If student found -->
                <div v-if="studentFound" class="flex flex-col items-center space-y-4">
                    <div class="rounded-lg overflow-hidden shadow border-4 border-white w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64">
                        <img :src="getProfileImageUrl(studentProfile.profileImage)" alt="Profile" class="object-cover w-full h-full" />
                    </div>
                    <div class="text-gray-800 text-center">
                        <div class="text-lg sm:text-xl md:text-2xl font-bold tracking-wide">
                            {{ studentProfile.fullName }}
                        </div>
                        <div class="text-sm md:text-base lg:text-lg font-medium italic">
                            {{ studentProfile.course }}
                        </div>
                        <div class="text-base md:text-lg mt-2 font-mono tracking-widest">
                            {{ studentProfile.idNumber }}
                        </div>
                    </div>
                </div>

                <!-- If student not found -->
                <div v-else class="text-red-600 text-xl font-semibold">
                    No Student ID Found.
                </div>
            </div>
        </div>
    </transition>

    <!-- Incident Report Modal -->
    <transition name="fade">
        <div
            v-if="showIncidentModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="showIncidentModal = false"
                    class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg"
                >
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Incident Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <p>HELLOOO</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!--    SPOT REPORT MODAL-->
    <transition name="fade">
        <div
            v-if="showSpotModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="showSpotModal = false"
                    class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg"
                >
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Spot Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <p>HELLOOO</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!--   STUDENT ATTENDANCE MODAL-->
    <transition name="fade">
        <div
            v-if="showStudentAttendanceModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="showStudentAttendanceModal = false"
                    class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg"
                >
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Student Attendance Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <p>HELOOOO</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!--   FACULTY AND STAFF ATTENDANCE MODAL-->
    <transition name="fade">
        <div
            v-if="showFacultyAndStaffAttendanceModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="showFacultyAndStaffAttendanceModal = false"
                    class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg"
                >
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Faculty And Staff Attendance Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <p>HELLOOO</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!--   LOGS MODAL-->
    <transition name="fade">
        <div
            v-if="showLogsModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4"
        >
            <div
                class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden"
            >
                <!-- Close Button -->
                <button
                    @click="showLogsModal = false"
                    class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg"
                >
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Activity Logs</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <p>HELLOOO</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

</template>

<script>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { StreamBarcodeReader } from "vue-barcode-reader";
import axios from "axios";
import TextInput from "@/Components/TextInput.vue";
import IncidentTable from "@/Components/IncidentTable.vue";
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import StudentReportTable from "@/Components/StudentReportTable.vue";
import Ghome from "@/Pages/Frontend/Ghome.vue";

// Move reactive data outside of component for proper composition API usage
const menuOpen = ref(false);

const logout = () => {
    router.post(route('logout'))
}

export default {
    name: "UserIDView",
    components: {
        Ghome,
        StudentReportTable,
        IncidentTable,
        Head,
        Link,
        TextInput,
        StreamBarcodeReader
    },
    setup() {
        const page = usePage();
        const user = computed(() => page.props.auth.user);

        return {
            menuOpen,
            logout,
            user
        };
    },
    data() {
        return {
            userId: ["", "", "", "", "", ""],
            timer: 120,
            timerInterval: null,
            timeoutMessageVisible: false,
            isResending: false,
            scannedCode: null,
            userIdInput: "",
            studentFound: null,
            studentProfile: {
                fullName: '',
                course: '',
                idNumber: '',
                profileImage: ''
            },
            checking: false,
            showIncidentModal: false,
            showSpotModal: false,
            showStudentAttendanceModal: false,
            showFacultyAndStaffAttendanceModal: false,
            showLogsModal: false,
            showProfileModal: false,
            triggeredByButton: false,
            // Current user data - you should get this from your authentication system
            currentUser: {
                id: 1,
                name: 'John Doe',
                email: 'john@example.com',
                role: 'guard' // or 'admin' or 'user'
            },
            // Sample incident reports data - replace with actual data from your backend
            incidentReports: [
                {
                    id: 1,
                    guard_name: 'John Doe',
                    guardId: 1,
                    user_id: 1,
                    what: 'Unauthorized Entry',
                    description: 'Person without ID tried to enter building',
                    where: 'Main Gate',
                    when: '10:30 AM',
                    how: 'Attempted to follow authorized person',
                    why: 'Lost student ID',
                    recommendation: 'Verify identity before allowing entry',
                    type: 'Incident Report',
                    date: '2025-07-31',
                    created_at: '2025-07-31T10:30:00Z'
                },
                {
                    id: 2,
                    guard_name: 'Jane Smith',
                    guardId: 2,
                    user_id: 2,
                    what: 'Suspicious Activity',
                    description: 'Person loitering near parking area',
                    where: 'Parking Lot B',
                    when: '2:15 PM',
                    how: 'Walking around cars repeatedly',
                    why: 'Unknown motives',
                    recommendation: 'Increase patrol frequency in parking areas',
                    type: 'Incident Report',
                    date: '2025-07-31',
                    created_at: '2025-07-31T14:15:00Z'
                }
            ]
        };
    },
    computed: {
        formattedTime() {
            const minutes = Math.floor(this.timer / 60).toString().padStart(2, "0");
            const seconds = (this.timer % 60).toString().padStart(2, "0");
            return `${minutes}:${seconds}`;
        },
        getRoleDisplayName() {
            if (!this.currentUser) {
                return "User";
            }

            switch (this.currentUser.role) {
                case "admin":
                    return "Administrator";
                case "guard":
                    return "Security Guard";
                default:
                    return "User";
            }
        }
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
                }
            }, 1000);
        },
        resendOtp() {
            this.isResending = true;
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        },
        onDecode(result) {
            this.scannedCode = result;
            // (Optional: Auto-fill or trigger actions here)
        },
        onInit(promise) {
            promise.catch((error) => {
                console.error("Camera initialization failed:", error);
            });
        },
        getProfileImageUrl(imagePath) {
            // Add your logic here to return the full URL for the profile image
            return imagePath ? `/images/profiles/${imagePath}` : '/images/default-profile.jpg';
        },
        async checkStudent() {
            if (!this.userIdInput) {
                this.studentFound = false;
                return;
            }
            this.checking = true;
            this.studentFound = null;

            try {
                // Call your backend API route. If you put the Laravel route in 'web.php', use '/students/...'
                const response = await axios.get(
                    `/students/${this.userIdInput.trim()}`
                );
                this.studentFound = response.data.exists;
                if (this.studentFound) {
                    this.studentProfile = response.data.student;
                }
                this.showProfileModal = true;
            } catch (error) {
                console.error("Error checking student:", error);
                this.studentFound = false;
                this.showProfileModal = true;
            } finally {
                this.checking = false;
            }
        },
        // Method to load incident reports from backend
        async loadIncidentReports() {
            try {
                const response = await axios.get('/api/incident-reports');
                this.incidentReports = response.data;
            } catch (error) {
                console.error("Error loading incident reports:", error);
            }
        },
        // Method to handle new report creation from IncidentTable component
        handleNewReport(newReport) {
            this.incidentReports.unshift(newReport);
        }
    },
    mounted() {
        this.startTimer();
        // Load incident reports when component mounts
        // this.loadIncidentReports(); // Uncomment when you have the backend endpoint ready
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

/* Fade transition for modals */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.scanner-bar {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 60px;
    height: 60px;
    overflow: hidden;
    background-color: transparent;
    transform: translate(-50%, -50%);
}
.scanner-bar::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: repeating-linear-gradient(
        to right,
        white 0px,
        white 2px,
        transparent 2px,
        transparent 4px,
        white 4px,
        white 5px,
        transparent 5px,
        transparent 8px,
        white 8px,
        white 12px,
        transparent 12px,
        transparent 14px,
        white 14px,
        white 15px,
        transparent 15px,
        transparent 18px,
        white 18px,
        white 21px,
        transparent 21px,
        transparent 24px,
        white 24px,
        white 26px,
        transparent 26px,
        transparent 29px,
        white 29px,
        white 31px,
        transparent 31px,
        transparent 34px,
        white 34px,
        white 40px,
        transparent 40px
    );
    opacity: 1;
}
.corner-border {
    position: relative;
    width: 85px;
    height: 85px;
    margin: 0 auto;
}
.corner-border::before,
.corner-border::after,
.corner-border > div::before,
.corner-border > div::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid white;
    border-radius: 3px;
}
.corner-border::before {
    top: 0;
    left: 0;
    border-right: none;
    border-bottom: none;
}
.corner-border::after {
    top: 0;
    right: 0;
    border-left: none;
    border-bottom: none;
}
.corner-border > div::before {
    bottom: 0;
    left: 0;
    border-top: none;
    border-right: none;
}
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
    0% { top: 0%; }
    50% { top: 90%; }
    100% { top: 0%; }
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
