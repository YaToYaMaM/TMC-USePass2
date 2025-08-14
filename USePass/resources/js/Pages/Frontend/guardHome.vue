<template>
    <Head title="USePass" />

    <!-- Desktop Navbar (hidden on mobile) -->
    <nav class="hidden md:flex fixed items-center justify-between bg-black bg-opacity-70 text-white px-6 py-3 shadow-md w-full z-50">
        <!-- Left Buttons -->
        <div class="flex items-center space-x-4">
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                FOR CHECKING
            </button>
            <button @click="showIncidentModal = true" class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Incident Report
            </button>
            <button @click="showSpotModal = true" class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Spot Report
            </button>
            <button @click="showStudentAttendanceModal = true" class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Students
            </button>
            <button @click="showFacultyAndStaffAttendanceModal = true" class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Faculty & Staff
            </button>
            <button class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Visitors
            </button>
            <button @click="showLogsModal = true" class="hover:bg-gray-600 text-white px-4 py-1 rounded font-semibold shadow transition duration-300 ease-in-out">
                Logs
            </button>
        </div>

        <!-- Center Search -->
        <div class="flex items-center space-x-6 justify-end">
            <!-- Enhanced Search Section in the Desktop Navbar -->
            <div class="flex items-center space-x-2 border-r-2 relative">
                <div class="relative">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="onSearchInput"
                        @focus="searchQuery && performSearch()"
                        @blur="hideSearchDropdown"
                        placeholder="Search Students, Faculty & Staff..."
                        class="bg-black bg-opacity-40 text-white px-3 py-1 rounded-[10px] border border-white border-opacity-30 placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-1 focus:ring-white w-64"
                    />

                    <!-- Loading indicator -->
                    <div v-if="isSearching" class="absolute right-2 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                    </div>

                    <!-- Clear button -->
                    <button
                        v-if="searchQuery && !isSearching"
                        @click="clearSearch"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Enhanced Search Results Dropdown -->
                    <transition name="dropdown">
                        <div
                            v-if="showSearchDropdown && (searchResults.students.length > 0 || searchResults.faculty.length > 0)"
                            class="absolute top-full left-0 right-0 mt-1 bg-black/65 rounded-lg shadow-lg z-[100] max-h-80 overflow-y-auto min-w-80"
                        >
                            <!-- Students Section -->
                            <div v-if="searchResults.students.length > 0" class="py-2 bg-transparent">
                                <div class="px-4 py-2 bg-blue-600/20 text-white text-xs font-semibold uppercase tracking-wide border-b border-gray-600">
                                    Students ({{ searchResults.students.length }})
                                </div>
                                <div
                                    v-for="student in searchResults.students.slice(0, 5)"
                                    :key="`student-${student.id}`"
                                    @click="selectPerson(student, 'student')"
                                    class="px-4 py-3 hover:bg-gray-700 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Student Avatar -->
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                                <img
                                                    v-if="student.profile && student.profile.trim()"
                                                    :src="student.profile"
                                                    :alt="student.full_name"
                                                    class="w-full h-full object-cover rounded-full"
                                                    @error="handleImageError($event)"
                                                />
                                                <span v-else>{{ getInitials(student.full_name) }}</span>
                                            </div>
                                        </div>

                                        <!-- Student Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center text-sm font-bold text-white truncate">
                                                {{ student.full_name }}
                                                <div class="ml-2 mt-1 w-2 h-2 bg-blue-400 rounded-full" title="Student"></div>
                                            </div>
                                            <div class="text-xs text-blue-300 font-medium truncate">
                                                ID: {{ student.id_number }}
                                            </div>
                                            <div class="text-xs text-white truncate">
                                                {{ student.program }}
                                            </div>
                                            <div v-if="student.major" class="text-xs text-white truncate">
                                                Major: {{ student.major }}
                                            </div>
                                        </div>

                                        <!-- Arrow indicator -->
                                        <div class="flex-shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Faculty & Staff Section -->
                            <div v-if="searchResults.faculty.length > 0" class="py-2 bg-transparent">
                                <div class="px-4 py-2 bg-green-600/20 text-white text-xs font-semibold uppercase tracking-wide border-b border-gray-600">
                                    Faculty & Staff ({{ searchResults.faculty.length }})
                                </div>
                                <div
                                    v-for="faculty in searchResults.faculty.slice(0, 5)"
                                    :key="`faculty-${faculty.id}`"
                                    @click="selectPerson(faculty, 'faculty')"
                                    class="px-4 py-3 hover:bg-gray-700 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors duration-150"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Faculty Avatar -->
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-sm">
                                                <img
                                                    v-if="faculty.profile && faculty.profile.trim()"
                                                    :src="faculty.profile"
                                                    :alt="faculty.name"
                                                    class="w-full h-full object-cover rounded-full"
                                                    @error="handleImageError($event)"
                                                />
                                                <span v-else>{{ getInitials(faculty.name) }}</span>
                                            </div>
                                        </div>

                                        <!-- Faculty Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center text-sm font-bold text-white truncate">
                                                {{ faculty.name }}
                                                <div class="ml-2 mt-1 w-2 h-2 bg-green-400 rounded-full" title="Faculty/Staff"></div>
                                            </div>
                                            <div class="text-xs text-green-300 font-medium truncate">
                                                ID: {{ faculty.employee_id }}
                                            </div>
                                            <div class="text-xs text-white truncate">
                                                {{ faculty.department || 'N/A' }}
                                            </div>
                                            <div v-if="faculty.unit" class="text-xs text-white truncate">
                                                Unit: {{ faculty.unit }}
                                            </div>
                                        </div>

                                        <!-- Arrow indicator -->
                                        <div class="flex-shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search summary -->
                            <div class="px-4 py-2 bg-transparent border-t text-xs text-white text-center">
                                {{ getTotalResults() }} result(s) found
                                <span v-if="getTotalResults() >= 10">• Showing first 10 results</span>
                            </div>

                            <!-- No results message -->
                            <div v-if="searchQuery && !isSearching && getTotalResults() === 0" class="px-4 py-6 text-sm text-gray-500 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                No results found for "{{ searchQuery }}"
                                <div class="text-xs text-gray-400 mt-1">Try searching by name, ID, department, or program</div>
                            </div>
                        </div>
                    </transition>
                </div>

                <button
                    @click="performSearch"
                    :disabled="!searchQuery.trim() || isSearching"
                    class="text-white pr-8 hover:text-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed transition-colors duration-150"
                >
                    <svg v-if="isSearching" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-else>Search</span>
                </button>
            </div>
            <!-- Right Profile -->
            <div class="flex items-center space-x-3">
                <div class="text-sm text-right">
                    <div class="font-semibold uppercase">{{ user.first_name }} {{ user.last_name}}</div>
                    <div class="text-xs text-gray-300">{{ user.role === 'guard' ? 'Security Guard' : 'Unknown'}}</div>
                </div>
                <img
                    :src="user.profile_image || '/guard_profiles/user.png'"
                    @error="handleImageError"
                    alt="Profile"
                    class="h-10 w-10 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white"
                />
                <button @click="menuOpen = !menuOpen" class="focus:outline-none" aria-label="Toggle menu">
                    <svg class="h-5 w-5 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <transition name="fade">
                    <div v-if="menuOpen" class="absolute right-0 top-full mt-2 w-40 sm:w-48 bg-black rounded-lg shadow-lg border border-[#ffffff] z-[99999]">
                        <div class="flex items-center space-x-2 sm:space-x-3 px-3 py-2 border-b border-[#ffffff]">
                            <img
                                :src="user.profile_image || '/guard_profiles/user.png'"
                                @error="handleImageError"
                                alt="Profile"
                                class="h-10 w-10 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white"
                            />
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

    <!-- Mobile Header -->
    <div class="md:hidden bg-black bg-opacity-90 text-white px-4 py-3 flex items-center justify-between relative">
        <div class="flex items-center space-x-3">
            <img
                :src="user.profile_image || '/guard_profiles/user.png'"
                @error="handleImageError"
                alt="Profile"
                class="h-10 w-10 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white"
            />
            <div>
                <div class="font-semibold text-sm">{{ user.first_name }} {{ user.last_name}}</div>
                <div class="text-xs text-gray-300">{{ user.role === 'guard' ? 'Security Guard' : 'Unknown'}}</div>
            </div>
        </div>
        <button @click="menuOpen = !menuOpen" class="focus:outline-none">
            <svg class="h-5 w-5 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
            </svg>
        </button>

        <!-- Mobile Dropdown Menu -->
        <transition name="fade">
            <div v-if="menuOpen" class="absolute right-0 top-full mt-2 w-40 sm:w-48 bg-black rounded-lg shadow-lg border border-[#ffffff] z-[99999]">
                <div class="flex items-center space-x-2 sm:space-x-3 px-3 py-2 border-b border-[#ffffff]">
                    <img
                        :src="user.profile_image || '/guard_profiles/user.png'"
                        @error="handleImageError"
                        alt="Profile"
                        class="h-10 w-10 sm:h-10 sm:w-10 rounded-full object-cover border-2 border-white"
                    />
                    <div>
                        <div class="font-semibold uppercase text-xs">{{ user.first_name }} {{ user.last_name}}</div>
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

    <!-- Main Content Container -->
    <div class="relative min-h-screen bg-cover bg-center flex flex-col" :style="{ backgroundImage: 'url(/images/bg_tmc.jpg)' }">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>

        <!-- Desktop Layout -->
        <div class="hidden md:flex relative z-10 flex-col items-center justify-center px-4 pt-20 min-h-screen">
            <div class="p-4 rounded-lg text-center max-w-lg w-full">
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
                        <input type="text" v-model="userIdInput" @keyup.enter="checkStudent(false)"
                               class="max-w-100 px-4 py-2 text-center rounded-[10px] bg-black bg-opacity-40 border border-white border-opacity-40 text-white placeholder-white placeholder-opacity-60 focus:outline-none"
                               placeholder="Enter Student ID" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Layout -->
        <div class="md:hidden relative z-10 flex flex-col min-h-screen">
            <!-- Logo Section -->
            <div class="flex-shrink-0 text-center py-8 px-4">
                <img src="/images/logo4.png" alt="USePass Logo" class="mx-auto w-48 h-auto mb-4" />
                <p class="text-white italic text-sm">
                    "Track Student. Ensure Safety. USePass."
                </p>
            </div>

            <!-- Add this section after the logo section and before the mobile button grid -->
            <!-- Mobile Search Section -->
            <div class="flex-shrink-0 px-6 pb-6">
                <div class="relative">
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="onSearchInput"
                        @focus="searchQuery && performSearch()"
                        @blur="hideSearchDropdown"
                        placeholder="Search Students, Faculty & Staff..."
                        class="w-full bg-black bg-opacity-40 text-white px-4 py-3 rounded-xl border border-white border-opacity-30 placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    />

                    <!-- Loading indicator -->
                    <div v-if="isSearching" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    </div>

                    <!-- Clear button -->
                    <button
                        v-if="searchQuery && !isSearching"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Mobile Search Results Dropdown -->
                    <transition name="dropdown">
                        <div
                            v-if="showSearchDropdown && (searchResults.students.length > 0 || searchResults.faculty.length > 0)"
                            class="absolute top-full left-0 right-0 mt-2 bg-black/80 rounded-xl shadow-lg z-[100] max-h-80 overflow-y-auto"
                        >
                            <!-- Students Section -->
                            <div v-if="searchResults.students.length > 0" class="py-2">
                                <div class="px-4 py-2 bg-blue-600/30 text-white text-xs font-semibold uppercase tracking-wide border-b border-gray-600">
                                    Students ({{ searchResults.students.length }})
                                </div>
                                <div
                                    v-for="student in searchResults.students.slice(0, 3)"
                                    :key="`mobile-student-${student.id}`"
                                    @click="selectPerson(student, 'student')"
                                    class="px-4 py-3 hover:bg-gray-700 cursor-pointer border-b border-gray-600 last:border-b-0 transition-colors duration-150"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Student Avatar -->
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xs">
                                                <img
                                                    v-if="student.profile && student.profile.trim()"
                                                    :src="student.profile"
                                                    :alt="student.full_name"
                                                    class="w-full h-full object-cover rounded-full"
                                                    @error="handleImageError($event)"
                                                />
                                                <span v-else>{{ getInitials(student.full_name) }}</span>
                                            </div>
                                        </div>

                                        <!-- Student Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center text-sm font-bold text-white truncate">
                                                {{ student.full_name }}
                                                <div class="ml-2 mt-0.5 w-1.5 h-1.5 bg-blue-400 rounded-full" title="Student"></div>
                                            </div>
                                            <div class="text-xs text-blue-300 font-medium truncate">
                                                ID: {{ student.id_number }}
                                            </div>
                                            <div class="text-xs text-gray-300 truncate">
                                                {{ student.program }}
                                            </div>
                                        </div>

                                        <!-- Arrow indicator -->
                                        <div class="flex-shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Faculty & Staff Section -->
                            <div v-if="searchResults.faculty.length > 0" class="py-2">
                                <div class="px-4 py-2 bg-green-600/30 text-white text-xs font-semibold uppercase tracking-wide border-b border-gray-600">
                                    Faculty & Staff ({{ searchResults.faculty.length }})
                                </div>
                                <div
                                    v-for="faculty in searchResults.faculty.slice(0, 3)"
                                    :key="`mobile-faculty-${faculty.id}`"
                                    @click="selectPerson(faculty, 'faculty')"
                                    class="px-4 py-3 hover:bg-gray-700 cursor-pointer border-b border-gray-600 last:border-b-0 transition-colors duration-150"
                                >
                                    <div class="flex items-center space-x-3">
                                        <!-- Faculty Avatar -->
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-xs">
                                                <img
                                                    v-if="faculty.profile && faculty.profile.trim()"
                                                    :src="faculty.profile"
                                                    :alt="faculty.name"
                                                    class="w-full h-full object-cover rounded-full"
                                                    @error="handleImageError($event)"
                                                />
                                                <span v-else>{{ getInitials(faculty.name) }}</span>
                                            </div>
                                        </div>

                                        <!-- Faculty Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center text-sm font-bold text-white truncate">
                                                {{ faculty.name }}
                                                <div class="ml-2 mt-0.5 w-1.5 h-1.5 bg-green-400 rounded-full" title="Faculty/Staff"></div>
                                            </div>
                                            <div class="text-xs text-green-300 font-medium truncate">
                                                ID: {{ faculty.employee_id }}
                                            </div>
                                            <div class="text-xs text-gray-300 truncate">
                                                {{ faculty.department || 'N/A' }}
                                            </div>
                                        </div>

                                        <!-- Arrow indicator -->
                                        <div class="flex-shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search summary -->
                            <div class="px-4 py-2 bg-transparent border-t border-gray-600 text-xs text-white text-center">
                                {{ getTotalResults() }} result(s) found
                                <span v-if="getTotalResults() >= 6">• Showing first 6 results</span>
                            </div>

                            <!-- No results message -->
                            <div v-if="searchQuery && !isSearching && getTotalResults() === 0" class="px-4 py-6 text-sm text-gray-300 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                No results found for "{{ searchQuery }}"
                                <div class="text-xs text-gray-400 mt-1">Try searching by name, ID, department, or program</div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Mobile Button Grid -->
            <div class="flex-grow flex items-center justify-center px-6 pb-20">
                <div class="w-full max-w-sm">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Scan QR Button -->
                        <button
                            @click="openScanModal"
                            class="bg-blue-500 hover:bg-blue-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3"
                        >
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zM13 3v8h8V3h-8zm6 6h-4V5h4v4zM3 21h8v-8H3v8zm2-6h4v4H5v-4z"/>
                                    <path d="M13 13h1.5v1.5H13zM14.5 14.5H16V16h-1.5zM16 13h1.5v1.5H16zM13 16h1.5v1.5H13zM14.5 17.5H16V19h-1.5zM16 16h1.5v1.5H16zM17.5 14.5H19V16h-1.5zM17.5 17.5H19V19h-1.5zM22 17.5h-1.5V19H22zM22 13h-1.5v1.5H22zM20.5 19H22v1.5h-1.5z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Scan QR</span>
                        </button>

                        <!-- Report Incident Button -->
                        <button @click="showIncidentModal = true"
                                class="mobile-btn bg-red-500 hover:bg-red-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L1 21h22L12 2zm0 3.5L19.5 19h-15L12 5.5zm-1 5.5h2v4h-2v-4zm0 5h2v2h-2v-2z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Incident Report</span>
                        </button>

                        <!-- Spot Report Button -->
                        <button @click="showSpotModal = true"
                                class="mobile-btn bg-orange-500 hover:bg-orange-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Spot Report</span>
                        </button>

                        <!-- Student Attendance Button -->
                        <button @click="showStudentAttendanceModal = true"
                                class="mobile-btn bg-green-500 hover:bg-green-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Student Attendance</span>
                        </button>

                        <!-- Faculty and Staff Attendance Button -->
                        <button @click="showFacultyAndStaffAttendanceModal = true"
                                class="mobile-btn bg-green-500 hover:bg-green-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Faculty and Staff Attendance</span>
                        </button>

                        <!-- Visitor Button -->
                        <button
                            class="mobile-btn bg-gray-500 hover:bg-blue-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Visitors</span>
                        </button>

                        <!-- Logs Button -->
                        <button @click="showLogsModal = true"
                                class="mobile-btn bg-yellow-500 hover:bg-blue-600 text-white p-6 rounded-2xl shadow-lg flex flex-col items-center space-y-3">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-sm">Logs</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Scan Modal -->
    <transition name="fade">
        <div v-if="showScanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 px-4">
            <div class="relative w-full max-w-sm bg-white rounded-xl p-6">
                <!-- Close Button -->
                <button @click="closeScanModal" class="absolute top-4 right-4 z-10 text-gray-600 hover:text-red-500 text-xl bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <div class="text-center">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Scan</h3>

                    <!-- QR Scanner Container -->
                    <div class="qr-scanner mb-4" :class="{ 'camera-error': cameraError, 'scanning-success': scanSuccess }">
                        <!-- Video element for camera feed -->
                        <video ref="videoElement" class="w-full h-full object-cover"></video>

                        <!-- Scanner overlay -->
                        <div class="scanner-overlay">
                            <div class="scanner-frame">
                                <div class="scanner-corners">
                                    <div class="scanner-corner top-left"></div>
                                    <div class="scanner-corner top-right"></div>
                                    <div class="scanner-corner bottom-left"></div>
                                    <div class="scanner-corner bottom-right"></div>
                                </div>
                                <div v-if="isScanning" class="scanning-line"></div>

                            </div>
                        </div>


                        <!-- Error overlay -->
                        <div v-if="cameraError" class="absolute inset-0 bg-red-50 flex items-center justify-center">
                            <div class="text-red-600 text-center p-4">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <p class="text-sm font-medium">{{ errorMessage }}</p>
                                <button @click="retryCamera" class="mt-2 px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600">
                                    Retry
                                </button>
                            </div>
                        </div>

                        <!-- Success overlay -->
                        <div v-if="scanSuccess" class="absolute inset-0 bg-green-50 flex items-center justify-center">
                            <div class="text-green-600 text-center p-4">
                                <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                                <p class="text-sm font-medium">QR Code Scanned!</p>
                                <p class="text-xs text-gray-600 mt-1">{{ scannedResult }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-2 mt-5">
                        <button
                            @click="toggleScanner"
                            :disabled="cameraError"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isScanning ? 'Stop Scanner' : 'Start Scanner' }}
                        </button>
                        <button
                            @click="switchCamera"
                            v-if="hasMultipleCameras"
                            class="p-2 bg-gray-200 rounded hover:bg-gray-300"
                        >
                            <i class="fas fa-camera-rotate"></i>
                        </button>

                    </div>

                    <!-- Scanner Status -->
                    <div v-if="isScanning" class="mt-4 text-sm text-gray-600">
                        <p>Point your camera at a QR code</p>
                        <p class="text-xs text-gray-400 mt-1">Scanner will automatically detect and read QR codes</p>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Student Profile Modal -->
    <transition name="fade">
        <div v-if="showProfileModal"
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black bg-opacity-70 px-4"
             style="z-index: 999999 !important;">

            <div class="relative z-10 flex flex-col items-center px-4 sm:px-8 md:px-12 lg:px-20 py-6 md:py-10 w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl 2xl:max-w-2xl space-y-6 md:space-y-8 bg-white rounded-xl shadow-lg text-center">
                <!-- Close Button -->
                <button @click="closeProfileModal"
                        class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <img src="/images/Logo2.png" alt="USePass Logo" class="mb-2 w-48 sm:w-56 md:w-64 lg:w-80 h-auto" />

                <!-- Modal Content - USING KEY TO FORCE RE-RENDER -->
                <div class="p-6" :key="`modal-${modalKey}`">

                    <!-- Debug Info -->
                    <div class="mb-4 p-2 bg-yellow-100 rounded text-xs">
                        State: {{ studentFound }} | Key: {{ modalKey }} | Time: {{ currentTime }}
                    </div>

                    <!-- LOADING STATE -->
                    <div v-show="isLoading" class="text-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading student data...</p>
                        <p class="text-xs text-gray-400 mt-2">Please wait...</p>
                    </div>

                    <!-- STUDENT FOUND STATE -->
                    <div v-show="isStudentFound" class="flex flex-col items-center space-y-4">
                        <div class="mb-4">
                            <div class="rounded-full overflow-hidden shadow border-4 border-white w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64">
                                <img
                                    :src="studentImageUrl"
                                    @error="handleImageError"
                                    class="object-cover rounded-lg w-full h-full"
                                    alt="Student Profile"
                                />
                            </div>
                        </div>
                        <div class="text-gray-800 text-center">
                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold tracking-wide">{{ studentName }}</h3>
                        <p class="text-sm md:text-base lg:text-lg font-medium italic">{{ studentProgram }}</p>
                        <p class="text-base md:text-lg mt-2 font-mono tracking-widest">ID: {{ studentId }}</p>

                        <div class="mt-4 p-3">
                            <div v-if="lastScanType" class="mt-2 p-2 text-lg font-bold">
                                <div class="text-green-600" v-if="lastScanType === 'time in'">Time In</div>
                                <div class="text-red-600" v-else-if="lastScanType === 'time out'">Time Out</div>
                                <div v-else>❓ Unknown Status</div>
                            </div>
                            <div v-if="userType" class="mt-2 p-2 text-blue-800 text-2xl font-bold">
                                <div v-if="userType === 'Student'">Student</div>
                                <div v-else-if="userType === 'Faculty'">Faculty</div>
                                <div v-else>❓ Unknown Status</div>
                            </div>
                        </div>



                    </div>
                    </div>

                    <!-- STUDENT NOT FOUND STATE -->
                    <div v-show="isStudentNotFound" class="text-center py-8">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-red-500 text-2xl">✗</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Student Not Found</h3>
                        <p class="text-gray-600">No student record found for ID: {{ userIdInput }}</p>
                    </div>

                </div>
            </div>
        </div>
    </transition>


    <!-- Incident Report Modal -->
    <transition name="fade">
        <div v-if="showIncidentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4">
            <div class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Close Button -->
                <button @click="showIncidentModal = false" class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Incident Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <IncidentTable :reports="incidentReports || []"/>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Spot Report Modal -->
    <transition name="fade">
        <div v-if="showSpotModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4">
            <div class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Close Button -->
                <button @click="showSpotModal = false" class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Spot Report</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <SpotTable :reports="spotReports || []"/>
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Student Attendance Modal -->
    <transition name="fade">
        <div v-if="showStudentAttendanceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4">
            <div class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Close Button -->
                <button @click="closeStudentAttendanceModal" class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">
                        Student Attendance Report
                        <span v-if="selectedStudent" class="hidden text-lg font-normal ml-2">
                        - {{ selectedStudent.full_name || selectedStudent.name }}
                    </span>
                    </h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <ghome :selected-student="selectedStudent" :key="selectedStudent ? selectedStudent.id : 'no-student'" />
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Faculty And Staff Attendance Modal -->
    <transition name="fade">
        <div v-if="showFacultyAndStaffAttendanceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4">
            <div class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Close Button -->
                <button @click="closeFacultyAttendanceModal" class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">
                        Faculty & Staff Attendance Report
                        <span v-if="selectedFaculty" class="hidden text-lg font-normal ml-2">
                        - {{ selectedFaculty.name }}
                    </span>
                    </h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <FacultyStaffAttendance
                                :selected-faculty="selectedFaculty"
                            :key="selectedFaculty ? selectedFaculty.id : 'no-faculty'"
                        />
                    </div>
                </div>
            </div>
        </div>
    </transition>

    <!-- Logs Modal -->
    <transition name="fade">
        <div v-if="showLogsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70 px-4">
            <div class="relative z-10 w-full max-w-6xl h-full max-h-[90vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                <!-- Close Button -->
                <button @click="showLogsModal = false" class="absolute top-4 right-4 z-20 text-gray-600 hover:text-red-500 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg">
                    ×
                </button>

                <!-- Modal Header -->
                <div class="bg-gray-600 text-white px-6 py-4">
                    <h2 class="text-2xl font-bold">Activity Logs</h2>
                </div>

                <!-- Modal Body -->
                <div class="h-full overflow-y-auto p-6">
                    <div class="space-y-4">
                        <glogs />
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
import SpotTable from "@/Components/SpotTable.vue";
import { ref, computed, onMounted, onUnmounted, nextTick, watch, getCurrentInstance } from 'vue';
import { route } from 'ziggy-js';
import StudentReportTable from "@/Components/StudentReportTable.vue";
import Ghome from "@/Pages/Frontend/Ghome.vue";
import QrScanner from "qr-scanner";
import Glogs from "@/Pages/Frontend/Glogs.vue";
import FacultyStaffAttendance from "@/Pages/Frontend/FacultyStaffAttendance.vue";


// Move reactive data outside of component for proper composition API usage
const menuOpen = ref(false);

const logout = () => {
    router.post(route('logout'))
}

export default {
    name: "guardHome",
    components: {
        FacultyStaffAttendance,
        Glogs,
        SpotTable,
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

        // QR Scanner reactive data
        const showScanModal = ref(false);
        const videoElement = ref(null);
        const userIdInput = ref('');

        const isScanning = ref(false);
        const cameraError = ref(false);
        const errorMessage = ref('');
        const scanSuccess = ref(false);
        const scannedResult = ref('');
        const lastScannedCode = ref('');
        const hasMultipleCameras = ref(false);
        const checking = ref(false);
        const showProfileModal = ref(false);

        const studentFound = ref(null);
        const lastScanType = ref('');
        const userType = ref('');


        const studentProfile = ref({
            fullName: '',
           program: '',
            idNumber: '',
            profileImage: ''
        });
        const modalKey = ref(0);
        const currentTime = ref('');
        const successTime = ref('');

        const updateTime = () => {
            currentTime.value = new Date().toLocaleTimeString();
        };
        setInterval(updateTime, 1000);

        const isLoading = computed(() => {
            console.log('🔄 isLoading computed:', studentFound.value === null);
            return studentFound.value === null;
        });
        const isStudentFound = computed(() => {
            console.log('✅ isStudentFound computed:', studentFound.value === true);
            return studentFound.value === true;
        });
        const isStudentNotFound = computed(() => {
            console.log('❌ isStudentNotFound computed:', studentFound.value === false);
            return studentFound.value === false;
        });

        const studentName = computed(() => studentProfile.value.fullName || '');
        const studentProgram = computed(() => studentProfile.value.program || '');
        const studentId = computed(() => studentProfile.value.idNumber || '');
        const studentImageUrl = computed(() => {
            if (!studentProfile.value.profileImage) return '/images/user.png';
            return `/${studentProfile.value.profileImage}`;
        });

        const getFullImagePath = (imagePath) => {
            if (!imagePath) return '/images/user.png';

            console.log('Original image path:', imagePath);

            // If it's already a full URL, return as is
            if (imagePath.startsWith('http')) {
                return imagePath;
            }

            // If it starts with /, return as is
            if (imagePath.startsWith('/')) {
                return imagePath;
            }

            // Try multiple possible paths
            const possiblePaths = [
                `/storage/${imagePath}`,
                `/images/profiles/${imagePath}`,
                `/public/storage/${imagePath}`,
                `/storage/app/public/${imagePath}`
            ];

            // For now, return the storage path
            const finalPath = `/storage/${imagePath}`;
            console.log('Final image path:', finalPath);
            return finalPath;
        };

        let qrScanner = null;
        let currentCameraIndex = 0;
        let availableCameras = [];


        const loadQrScannerLibrary = () => {
            return new Promise((resolve) => {
                if (window.QrScanner) return resolve();

                const script = document.createElement('script');
                script.src = 'https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js';
                script.onload = () => {
                    QrScanner.WORKER_PATH = 'https://unpkg.com/qr-scanner@1.4.2/qr-scanner-worker.min.js';
                    resolve();
                };
                document.head.appendChild(script);
            });
        };

        const fetchAvailableCameras = async () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                throw new Error("Camera API not supported in this browser.");
            }
            const devices = await navigator.mediaDevices.enumerateDevices();
            availableCameras = devices.filter(device => device.kind === 'videoinput');
            hasMultipleCameras.value = availableCameras.length > 1;
        };

        const startScanner = async () => {
            cameraError.value = false;
            scannedResult.value = '';

            try {
                await navigator.mediaDevices.getUserMedia({ video: true });

                await loadQrScannerLibrary();
                await fetchAvailableCameras();

                if (!videoElement.value) throw new Error("No video element found");

                const selectedCamera = availableCameras[currentCameraIndex]?.deviceId;

                qrScanner = new QrScanner(
                    videoElement.value,
                    (result) => {
                        console.log("✅ QR Code Result:", result.data);
                        onScanSuccess(result);
                        scannedResult.value = result.data;
                        stopScanner(); // auto-stop after scan
                    },
                    {
                        returnDetailedScanResult: true,
                        highlightScanRegion: true,
                        highlightCodeOutline: true,
                    }
                );

                if (selectedCamera) {
                    await qrScanner.setCamera(selectedCamera);
                }

                await qrScanner.start();
                isScanning.value = true;
            } catch (err) {
                console.error("Camera start failed:", err);
                errorMessage.value = err.message || 'Could not access camera.';
                cameraError.value = true;
            }
        };


        const testCameraAccess = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            stream.getTracks().forEach(track => track.stop());
        };

        const stopScanner = () => {
            if (qrScanner) {
                qrScanner.stop();
                qrScanner.destroy();
                qrScanner = null;
            }
            isScanning.value = false;
        };

        const toggleScanner = () => {
            if (isScanning.value) {
                stopScanner();
            } else {
                startScanner();
            }
        };

        const switchCamera = async () => {
            if (availableCameras.length <= 1) return;

            currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;

            if (qrScanner) {
                await qrScanner.setCamera(availableCameras[currentCameraIndex].id);
            }
        };


        const forceTemplateUpdate = () => {
            console.log('🔄 Forcing template update');
            const instance = getCurrentInstance();
            if (instance && instance.proxy) {
                instance.proxy.$forceUpdate();
            }
        };

        // UPDATED: checkStudent with forced updates
        const checkStudent = async () => {
            if (!userIdInput.value.trim()) {
                console.error('No ID provided');
                return;
            }

            try {
                const id = userIdInput.value.trim();
                console.log('🔍 Checking ID:', id);

                // Reset everything
                studentProfile.value = {
                    fullName: '',
                    program: '',
                    idNumber: '',
                    profileImage: ''
                };

                modalKey.value++;
                studentFound.value = null;
                showProfileModal.value = true;

                await nextTick();
                await new Promise(resolve => setTimeout(resolve, 100));


                let response = await axios.get(`/students/profile/${id}`);
                userType.value = 'Student'
                if (!response.data?.exists) {

                    response = await axios.get(`/faculty/${id}`);
                    userType.value = 'Faculty'
                }

                if (response.data && response.data.exists) {
                    const person = response.data.student || response.data.faculty;

                    studentProfile.value = {
                        fullName: person.fullName || 'Unknown Name',
                        idNumber: person.id || id,
                        program: person.program || 'N/A',
                        profileImage: person.profileImage || ''
                    };

                    console.log('📝 Profile updated:', studentProfile.value);

                    // 🔄 Log scan for student or faculty
                    if (response.data.student) {
                        await logStudentScan(person.id);
                    } else if (response.data.faculty) {
                        await logFacultyScan(person.id);
                    }

                    studentFound.value = true;
                    successTime.value = new Date().toLocaleTimeString();
                    modalKey.value++;

                    setTimeout(() => {
                        closeProfileModal();
                        openScanModal();
                    }, 2000);

                    console.log('✅ Person found:', modalKey.value);
                } else {
                    studentFound.value = false;
                    modalKey.value++;
                    console.log('❌ Person not found');
                    setTimeout(() => {
                        closeProfileModal();
                        openScanModal();
                    }, 2000);
                }

                await nextTick();
            } catch (error) {
                console.error('💥 Error checking ID:', error);
                studentFound.value = false;
                modalKey.value++;
                setTimeout(() => {
                    closeProfileModal();
                    openScanModal();
                }, 2000);
            }
        };

        const resetModal = () => {
            console.log('🔄 Resetting modal state');
            studentFound.value = null;
            studentProfile.value = {
                fullName: '',
                program: '',
                idNumber: '',
                profileImage: ''
            };
        };
        const forceUpdate = () => {
            const instance = getCurrentInstance();
            if (instance) {
                instance.proxy.$forceUpdate();
            }
        };
        const closeProfileModal = () => {
            console.log('🔄 NUCLEAR: Closing modal');
            showProfileModal.value = false;
            studentFound.value = null;
            userIdInput.value = '';
            modalKey.value++;
        };
        const resetAndScanAgain = () => {
            closeProfileModal();
            setTimeout(() => {
                openScanModal();
            }, 300);
        };
        const handleImageError = (event) => {
            console.log('🖼️ Image error, using fallback');
            event.target.src = '/images/user.png';
        };

        const logStudentScan = async (studentsId) => {
            try {
                const response = await axios.post('/students/log-scan', {
                    students_id: studentsId,
                });

                const result = response.data;

                // Only accept 'in' or 'out'
                if (result.status === 'time in' || result.status === 'time out') {
                    lastScanType.value = result.status;
                } else {
                    lastScanType.value = ''; // fallback
                }

            } catch (error) {
                console.error('💥 Failed to log student scan:', error);
                lastScanType.value = '';
            }
        };
        const logFacultyScan = async (facultyId) => {
            try {
                const response = await axios.post('/faculty-log', {
                    faculty_id: facultyId,
                });

                const result = response.data;

                // Accept only 'time in' or 'time out'
                if (result.status === 'time in' || result.status === 'time out') {
                    lastScanType.value = result.status;
                    console.log(`✅ Faculty ${result.status} at ${result.time}`);
                } else {
                    lastScanType.value = ''; // fallback
                    console.warn('⚠️ Unrecognized status:', result.status);
                }

            } catch (error) {
                console.error('💥 Failed to log faculty scan:', error);
                lastScanType.value = '';
            }
        };



// Add this watcher to catch unauthorized changes
        watch([showProfileModal, studentFound], ([show, found]) => {
            console.log('Modal:', show, '| State:', found)
            console.log('Template should show:',
                found === null ? 'Loading' :
                    found ? 'Student' : 'Not Found'
            )
        })








        const onScanSuccess = (result) => {
            console.log('QR Code scanned:', result.data);

            const scannedRaw = result.data.trim();
            const cleanedId = scannedRaw.replace(/^ID:\s*/, '');


            userIdInput.value = cleanedId;
            lastScannedCode.value = cleanedId;
            scannedResult.value = scannedRaw;

            scanSuccess.value = true;

            // Then check student after a brief delay
            setTimeout(() => {
                checkStudent();
            }, 500);
        };

        watch([showProfileModal, studentFound], ([modalVisible, foundState]) => {
            console.log(`Modal: ${modalVisible}, State: ${foundState}`)
        })

// Add this method to test state transitions
        const testStateFlow = async () => {
            studentFound.value = null
            showProfileModal.value = true
            await new Promise(resolve => setTimeout(resolve, 1000))

            studentProfile.value = {
                fullName: "TEST STUDENT",
                idNumber: "TEST-123",
                program: "TEST PROGRAM",
                profileImage: "/images/user.png"
            }
            studentFound.value = true

            await new Promise(resolve => setTimeout(resolve, 2000))
            studentFound.value = false
        }

        const retryCamera = () => {
            cameraError.value = false;
            errorMessage.value = '';
            startScanner();
        };

        const closeScanModal = () => {
            stopScanner();
            showScanModal.value = false;
            scanSuccess.value = false;
            scannedResult.value = '';
            userIdInput.value = '';
        };

        const openScanModal = () => {
            showScanModal.value = true;
            // Start scanner after DOM update
            nextTick(() => {
                setTimeout(() => {
                    startScanner();
                    scanSuccess.value = false;
                }, 100);
            });
        };


        onMounted(async () => {
            // Load the scanner library dynamically
            watch(studentFound, (newVal) => {
                console.log("studentFound changed to:", newVal)
            })
            await loadQrScannerLibrary();
        });

        onUnmounted(() => {
            stopScanner();
        });

        return {
            menuOpen,
            logout,
            user,
            // QR Scanner functions and data
            showScanModal,
            videoElement,
            userIdInput,
            isLoading,
            isScanning,
            cameraError,
            errorMessage,
            scanSuccess,
            scannedResult,
            lastScannedCode,
            hasMultipleCameras,
            startScanner,
            stopScanner,
            toggleScanner,
            switchCamera,
            retryCamera,
            closeScanModal,
            openScanModal,
            onScanSuccess,
            studentFound,
            checking,
            showProfileModal,
            studentProfile,

            closeProfileModal,
            resetAndScanAgain,
            getFullImagePath,
            handleImageError,
            checkStudent,
            resetModal,
            forceUpdate,
            modalKey,
            currentTime,
            successTime,

            isStudentFound,
            isStudentNotFound,
            studentName,
            studentProgram,
            studentId,
            studentImageUrl,

            lastScanType,
            userType,

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
            studentFound: null,

            checking: false,
            showIncidentModal: false,
            showSpotModal: false,
            showStudentAttendanceModal: false,
            showFacultyAndStaffAttendanceModal: false,
            showLogsModal: false,
            triggeredByButton: false,
            currentUser: {
                id: 1,
                name: 'John Doe',
                email: 'john@example.com',
                role: 'guard' // or 'admin' or 'user'
            },
            incidentReports: [],
            spotReports: [],
            searchQuery: '',
            searchResults: {
                students: [],
                faculty: []
            },
            showSearchDropdown: false,
            isSearching: false,
            searchTimeout: null,
            selectedStudent: null,
            selectedFaculty: null,
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
        }
        ,
        // Method to load incident reports from backend
        async loadIncidentReports() {
            try {
                // If using Option 1, adjust the URL based on the current page
                const response = await axios.get('/incident', {  // or '/guard-home' for guards
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.incidentReports = response.data;
            } catch (error) {
                console.error("Error loading incident reports:", error);
            }
        },
        async loadSpotReports() {
            try {
                // Fixed URL to match the route
                const response = await axios.get('/spot-reports', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.spotReports = response.data;
            } catch (error) {
                console.error("Error loading spot reports:", error);
            }
        },

        async performSearch() {
            if (!this.searchQuery.trim() || this.searchQuery.length < 2) {
                this.searchResults = { students: [], faculty: [] };
                this.showSearchDropdown = false;
                return;
            }

            this.isSearching = true;

            try {
                // Search both students and faculty simultaneously
                const [studentsResponse, facultyResponse] = await Promise.all([
                    this.searchStudents(),
                    this.searchFaculty()
                ]);

                this.searchResults = {
                    students: studentsResponse || [],
                    faculty: facultyResponse || []
                };

                this.showSearchDropdown = this.getTotalResults() > 0;

            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = { students: [], faculty: [] };
                this.showSearchDropdown = false;
            } finally {
                this.isSearching = false;
            }
        },

        async searchStudents() {
            try {
                const response = await axios.get('/students/search-active', {
                    params: {
                        query: this.searchQuery.trim()
                    },
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                return response.data.students || [];
            } catch (error) {
                console.error('Student search error:', error);
                return [];
            }
        },

        async searchFaculty() {
            try {
                const response = await axios.get('/faculty/search-active', {
                    params: {
                        query: this.searchQuery.trim()
                    },
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                return response.data.faculty || [];
            } catch (error) {
                console.error('Faculty search error:', error);
                return [];
            }
        },

        onSearchInput() {
            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // If search query is empty, clear results immediately
            if (!this.searchQuery.trim()) {
                this.searchResults = { students: [], faculty: [] };
                this.showSearchDropdown = false;
                return;
            }

            // Debounce search - wait 300ms after user stops typing
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 300);
        },

        selectPerson(person, type) {
            if (type === 'student') {
                this.selectedStudent = {
                    id: person.id,
                    student_id: person.id,
                    name: person.full_name,
                    full_name: person.full_name,
                    program: person.program,
                    major: person.major,
                    unit: person.unit,
                    id_number: person.id_number,
                    profile: person.profile
                };

                // Show student attendance modal
                this.showStudentAttendanceModal = true;

            } else if (type === 'faculty') {
                this.selectedFaculty = {
                    id: person.id,
                    name: person.name,
                    department: person.department,
                    unit: person.unit,
                    employee_id: person.employee_id,
                    profile: person.profile
                };

                // Show faculty attendance modal
                this.showFacultyAndStaffAttendanceModal = true;
            }

            // Hide the search dropdown and clear search
            this.hideSearchDropdown();
            this.searchQuery = person.name || person.full_name;

            console.log(`Selected ${type}:`, type === 'student' ? this.selectedStudent : this.selectedFaculty);
        },

        getTotalResults() {
            return this.searchResults.students.length + this.searchResults.faculty.length;
        },

        getInitials(name) {
            if (!name) return '?';

            const names = name.trim().split(' ');
            if (names.length === 1) {
                return names[0].charAt(0).toUpperCase();
            }

            return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
        },

        hideSearchDropdown() {
            setTimeout(() => {
                this.showSearchDropdown = false;
            }, 200);
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = { students: [], faculty: [] };
            this.showSearchDropdown = false;
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
        },

        // Make sure to call fetchStudentRecords when component loads
        // so you have data to search through
        async loadInitialData() {
            await this.fetchStudentRecords();
            // Now allStudentRecords will be populated for searching
        },
        closeStudentAttendanceModal() {
            this.showStudentAttendanceModal = false;
            // Reset selected student after a short delay to allow for smooth transition
            setTimeout(() => {
                this.selectedStudent = null;
            }, 300);
        },
        closeFacultyAttendanceModal() {
            this.showFacultyAndStaffAttendanceModal = false;
            setTimeout(() => {
                this.selectedFaculty = null;
            }, 300);
        },
    },
    mounted() {
        this.startTimer();
        // Load incident reports when component mounts
        this.loadIncidentReports(); // Uncomment when you have the backend endpoint ready
        this.loadSpotReports();  // Uncomment when you have the backend endpoint ready

        // Load QR Scanner library dynamically if not already loaded
        if (typeof QrScanner === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qr-scanner/1.4.2/qr-scanner.umd.min.js';
            script.onload = () => {
                console.log('QR Scanner library loaded');
            };
            document.head.appendChild(script);
        }
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

/* Mobile button hover effects */
.mobile-btn {
    transition: all 0.3s ease;
}
.mobile-btn:active {
    transform: scale(0.95);
}

/* QR Scanner specific styles */
.qr-scanner {
    position: relative;
    width: 100%;
    max-width: 300px;
    height: 300px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
}

.qr-scanner video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scanner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.scanner-frame {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    transform: translate(-50%, -50%);
    border: 2px solid rgba(255, 255, 255, 0.8);
    border-radius: 12px;
}

.scanner-corners {
    position: absolute;
    width: 100%;
    height: 100%;
}

.scanner-corner {
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #00ff00;
}

.scanner-corner.top-left {
    top: -2px;
    left: -2px;
    border-right: none;
    border-bottom: none;
}

.scanner-corner.top-right {
    top: -2px;
    right: -2px;
    border-left: none;
    border-bottom: none;
}

.scanner-corner.bottom-left {
    bottom: -2px;
    left: -2px;
    border-right: none;
    border-top: none;
}

.scanner-corner.bottom-right {
    bottom: -2px;
    right: -2px;
    border-left: none;
    border-top: none;
}

.scanning-line {
    position: absolute;
    left: 10px;
    right: 10px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #00ff00, transparent);
    animation: scan-animation 2s linear infinite;
}

@keyframes scan-animation {
    0% { top: 10px; opacity: 1; }
    50% { opacity: 1; }
    100% { top: calc(100% - 12px); opacity: 0; }
}

.camera-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.scanning-success {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
.z-\[9999\] {
    z-index: 9999 !important;
}
.fixed.inset-0 {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.animate-spin {
    animation: spin 1s linear infinite;
}


/* Enhanced dropdown styles */
.dropdown-enter-active, .dropdown-leave-active {
    transition: all 0.2s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Custom scrollbar for dropdown */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
