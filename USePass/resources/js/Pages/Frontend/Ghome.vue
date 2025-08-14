<template>
    <Head title="USePass" />
    <div class="min-h-screen bg-gray-50">
        <!-- Filter Section -->
        <div class="w-full px-3 sm:px-4 lg:px-8 py-4 sm:py-6">
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <!-- Mobile: Stack everything vertically -->
                        <!-- Desktop: Two-column layout -->
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0 lg:space-x-6">

                            <!-- LEFT SECTION: Date & Program -->
                            <div class="flex flex-col space-y-4 lg:flex-1">
                                <!-- Date Input -->
                                <div class="flex flex-col xs:flex-row xs:items-center space-y-2 xs:space-y-0 xs:space-x-3">
                                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Date:</label>
                                    <input
                                        type="date"
                                        v-model="selectedDate"
                                        class="w-full xs:w-auto border border-gray-300 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    />
                                </div>

                                <!-- Program Selector -->
                                <div class="flex flex-col xs:flex-row xs:items-center space-y-2 xs:space-y-0 xs:space-x-3">
                                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Program:</label>
                                    <div class="relative w-full xs:w-auto xs:min-w-[200px]">
                                        <select
                                            v-model="selectedProgram"
                                            class="w-full appearance-none border border-gray-300 rounded-md px-3 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-colors"
                                        >
                                            <option value="">All Programs</option>
                                            <option v-for="program in availablePrograms" :key="program" :value="program">
                                                {{ program }}
                                            </option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT SECTION: Search & Unit -->
                            <div class="flex flex-col space-y-4 lg:flex-1 lg:items-end">
                                <!-- Search Input -->
                                <div class="relative w-full lg:w-64">
                                    <input
                                        type="text"
                                        v-model="searchText"
                                        placeholder="Search students..."
                                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    />
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M15.5 10.5a5 5 0 11-10 0 5 5 0 0110 0z" />
                                    </svg>
                                </div>

                                <!-- Unit Selector -->
                                <div class="w-full lg:w-auto">
                                    <div class="inline-flex w-full lg:w-auto text-sm bg-gray-100 p-1 rounded-lg shadow-sm">
                                        <button
                                            @click="selectUnit('Tagum')"
                                            :class="buttonClass('Tagum')"
                                            class="flex-1 lg:flex-none px-4 py-2 rounded-md font-medium transition-all duration-200"
                                        >
                                            Tagum
                                        </button>
                                        <button
                                            @click="selectUnit('Mabini')"
                                            :class="buttonClass('Mabini')"
                                            class="flex-1 lg:flex-none px-4 py-2 rounded-md font-medium transition-all duration-200"
                                        >
                                            Mabini
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional: Mobile-specific action buttons -->
                        <div class="block sm:hidden pt-2 border-t border-gray-200">
                            <div class="flex space-x-3">
                                <button
                                    @click="clearFilters"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors"
                                >
                                    Clear All
                                </button>
                                <button
                                    @click="applyFilters"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                                >
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Loading State -->
            <div v-if="loading" class="bg-white shadow rounded-lg p-6 text-center">
                <div class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading students...
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="bg-white shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="text-red-600 text-sm font-medium">{{ error }}</div>
                    <button @click="fetchStudentRecords" class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Try Again
                    </button>
                </div>
            </div>

            <!-- Students Table -->
            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <div class="flex justify-between">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Students Inside Campus</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Showing {{ filteredStudents.length }} students for {{ selectedUnit }} unit
                            <span v-if="localSelectedStudent">
                                (Selected: {{ localSelectedStudent.name }})
                            </span>
                            <span v-else-if="props.selectedStudent && searchText === props.selectedStudent.name">
                                (Selected: {{ props.selectedStudent.name }})
                            </span>
                        </p>
                    </div>
                    <!-- Manual Attendance -->
                    <div class="flex px-6 py-6">
                        <button
                            @click="openManualAttendanceModal"
                            class="px-4 py-2 rounded-[5px] text-white bg-[#760000] hover:bg-[#5a0000] transition-colors duration-200"
                        >
                            Manual Attendance
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Major</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(student, index) in paginatedStudents"
                                :key="student.id"
                                @click="selectStudent(student)"
                                :class="[
                                'hover:bg-gray-50 transition-colors duration-150 cursor-pointer',
                                (localSelectedStudent?.id === student.id ||
                                 (props.selectedStudent?.id === student.id && searchText === props.selectedStudent.name)) &&
                                 getStatusText(student) === 'Present' ? 'bg-blue-50 border-l-4 border-[#760000]' : ''
                                ]"
                            >
                                <!-- Student Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                                            <div class="text-sm text-gray-500">{{ student.program }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Major Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ student.major || 'N/A' }}</div>
                                </td>

                                <!-- Unit Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ student.unit }}
                                        </span>
                                </td>

                                <!-- Time In Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ formatTime(student.time_in) }}</div>
                                    <div class="text-xs text-gray-400">{{ formatDate(student.date) }}</div>
                                </td>

                                <!-- Time Out Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ formatTime(student.time_out) }}</div>
                                    <div class="text-xs text-gray-400">{{ formatDate(student.date) }}</div>
                                </td>

                                <!-- Status Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(student)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getStatusText(student) }}
                                        </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="filteredStudents.length === 0" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                            <p class="mt-1 text-sm text-gray-500">No students are currently inside the campus matching your filters.</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="filteredStudents.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span>
                                to
                                <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredStudents.length) }}</span>
                                of
                                <span class="font-medium">{{ filteredStudents.length }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <button
                                    @click="prevPage"
                                    :disabled="currentPage === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                        Page {{ currentPage }} of {{ totalPages }}
                                    </span>
                                <button
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Attendance Modal -->
        <div v-if="showManualAttendanceModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeManualAttendanceModal"
                ></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <div class="text-center sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Manual Attendance</h3>

                                    <!-- Student Search/Select -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Search Student By: ( ID, Name, or Program )</label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                v-model="manualAttendanceSearch"
                                                placeholder="Search for a student..."
                                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M15.5 10.5a5 5 0 11-10 0 5 5 0 0110 0z" />
                                            </svg>
                                        </div>

                                        <!-- Search Results Dropdown -->
                                        <div v-if="manualAttendanceSearch && filteredManualStudents.length > 0"
                                             class="mt-1 max-h-60 overflow-y-auto bg-white border border-gray-300 rounded-md shadow-lg">
                                            <div
                                                v-for="student in filteredManualStudents.slice(0, 10)"
                                                :key="student.id"
                                                @click="selectManualStudent(student)"
                                                class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                            >
                                                <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                                                <div class="text-xs text-gray-500">{{ student.program }} - {{ student.unit }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Selected Student Info -->
                                    <div v-if="selectedManualStudent" class="mb-6 p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ selectedManualStudent.name }}</div>
                                                <div class="text-xs text-gray-500">{{ selectedManualStudent.program }} - {{ selectedManualStudent.unit }}</div>
                                            </div>
                                            <button
                                                @click="clearManualStudent"
                                                class="text-gray-400 hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Loading state for checking attendance status -->
                                        <div v-if="checkingAttendanceStatus" class="text-center py-2">
                                            <div class="inline-flex items-center text-sm text-gray-500">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Checking attendance status...
                                            </div>
                                        </div>

                                        <!-- Student Current Status Display -->
                                        <div v-if="!checkingAttendanceStatus && selectedManualStudent" class="mb-4">
                                            <div class="text-xs text-gray-600 mb-2">Current Status for {{ selectedAttendanceDate }}:</div>
                                            <div class="flex items-center space-x-2">
                                        <span :class="getCurrentStatusClass()" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getCurrentStatusText() }}
                                        </span>
                                                <div v-if="studentCurrentRecord" class="text-xs text-gray-500">
                                                    <span v-if="studentCurrentRecord.time_in">In: {{ formatTime(studentCurrentRecord.time_in) }}</span>
                                                    <span v-if="studentCurrentRecord.time_out" class="ml-2">Out: {{ formatTime(studentCurrentRecord.time_out) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons - Conditionally shown based on current status -->
                                        <div class="mt-4 flex space-x-3">
                                            <!-- Show Time In button only if student hasn't timed in yet or has already timed out -->
                                            <button
                                                v-if="canTimeIn()"
                                                @click="openTimeModal('time_in')"
                                                :disabled="!selectedManualStudent || checkingAttendanceStatus"
                                                class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Time In
                                            </button>

                                            <!-- Show Time Out button only if student has timed in but hasn't timed out yet -->
                                            <button
                                                v-if="canTimeOut()"
                                                @click="openTimeModal('time_out')"
                                                :disabled="!selectedManualStudent || checkingAttendanceStatus"
                                                class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Time Out
                                            </button>

                                            <!-- Show both buttons if no record exists for the selected date -->
                                            <template v-if="!studentCurrentRecord">
                                                <button
                                                    @click="openTimeModal('time_in')"
                                                    :disabled="!selectedManualStudent || checkingAttendanceStatus"
                                                    class="flex-1 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    Time In
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Status message -->
                                        <div v-if="!checkingAttendanceStatus && selectedManualStudent" class="mt-3 text-xs text-gray-500 text-center">
                                            <template v-if="!studentCurrentRecord">
                                                No attendance record found for this date.
                                            </template>
                                            <template v-else-if="canTimeOut()">
                                                Student is currently inside campus.
                                            </template>
                                            <template v-else-if="canTimeIn()">
                                                Student can time in again.
                                            </template>
                                            <template v-else>
                                                Student has completed attendance for this date.
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="closeManualAttendanceModal"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Selection Modal -->
        <div v-if="showTimeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeTimeModal"
                ></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Set {{ timeModalType === 'time_in' ? 'Time In' : 'Time Out' }}
                            </h3>

                            <!-- Student Info -->
                            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-900">{{ selectedManualStudent?.name }}</div>
                                <div class="text-xs text-gray-500">{{ selectedManualStudent?.program }}</div>
                            </div>

                            <!-- Time Input -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Time:</label>
                                <input
                                    type="time"
                                    v-model="selectedTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <!-- Date Input -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Date:</label>
                                <input
                                    type="date"
                                    v-model="selectedAttendanceDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="submitManualAttendance"
                            :disabled="!selectedTime || !selectedAttendanceDate"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Submit
                        </button>
                        <button
                            @click="closeTimeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Frontend from '@/Layouts/FrontendLayout.vue'
import {Head} from "@inertiajs/vue3";
import axios from 'axios';
import { ref, computed, watch, onMounted } from 'vue';
import Swal from "sweetalert2";

// Define props using defineProps (Composition API way)
const props = defineProps({
    selectedStudent: {
        type: Object,
        default: null
    }
});

// Reactive data
const selectedUnit = ref("Tagum");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const searchText = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]); // Today's date
const selectedProgram = ref('');
const allStudentRecords = ref([]); // Store all records
const loading = ref(false);
const error = ref('');
const localSelectedStudent = ref(null);

// Manual Attendance Modal States
const showManualAttendanceModal = ref(false);
const showTimeModal = ref(false);
const manualAttendanceSearch = ref('');
const selectedManualStudent = ref(null);
const timeModalType = ref(''); // 'time_in' or 'time_out'
const selectedTime = ref('');
const selectedAttendanceDate = ref(new Date().toISOString().split('T')[0]);

// Computed properties
const availablePrograms = computed(() => {
    // Filter programs based on selected unit first
    const unitFilteredRecords = allStudentRecords.value.filter(student =>
        student.unit === selectedUnit.value
    );
    const programs = [...new Set(unitFilteredRecords.map(student => student.program))];
    return programs.sort();
});

const filteredStudents = computed(() => {
    const search = searchText.value.toLowerCase();
    const program = selectedProgram.value;
    const unit = selectedUnit.value;

    return allStudentRecords.value.filter(student => {
        // First filter by unit - this is the main fix
        const matchesUnit = student.unit === unit;

        const matchesSearch =
            student.name.toLowerCase().includes(search) ||
            student.program.toLowerCase().includes(search) ||
            (student.major && student.major.toLowerCase().includes(search));

        const matchesProgram = !program || student.program === program;

        return matchesUnit && matchesSearch && matchesProgram;
    });
});

// Manual attendance search results (separate from filtered students)
// Add these to your existing reactive data
const manualStudentSearchResults = ref([]);
const isSearchingManualStudents = ref(false);
const checkingAttendanceStatus = ref(false);
const studentCurrentRecord = ref(null);

// Add this computed property
const filteredManualStudents = computed(() => {
    return manualStudentSearchResults.value;
});

// Add this search function
const searchManualStudents = async (query) => {
    if (!query || query.length < 2) {
        manualStudentSearchResults.value = [];
        return;
    }

    isSearchingManualStudents.value = true;

    try {
        const response = await axios.get('/search-students', {
            params: { query }
        });

        manualStudentSearchResults.value = response.data.students || [];
        console.log('Search results:', response.data.students); // Debug log
    } catch (error) {
        console.error('Error searching students:', error);
        manualStudentSearchResults.value = [];
    } finally {
        isSearchingManualStudents.value = false;
    }
};

// Add this to your checkStudentAttendanceStatus function for debugging
const checkStudentAttendanceStatus = async (studentId) => {
    if (!studentId || !selectedAttendanceDate.value) return;

    checkingAttendanceStatus.value = true;
    studentCurrentRecord.value = null;

    try {
        console.log('Checking attendance for:', studentId, 'on date:', selectedAttendanceDate.value);

        const response = await axios.get('/check-student-attendance', {
            params: {
                student_id: studentId,
                date: selectedAttendanceDate.value
            }
        });

        console.log('Response received:', response.data);

        studentCurrentRecord.value = response.data.record;

        // Refresh the main student records after checking attendance
        // This ensures the main table also shows the latest data
        fetchStudentRecords();

    } catch (error) {
        console.error('Error checking attendance status:', error);
        studentCurrentRecord.value = null;
    } finally {
        checkingAttendanceStatus.value = false;
    }
};


const totalPages = computed(() => {
    return Math.ceil(filteredStudents.value.length / itemsPerPage.value);
});

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredStudents.value.slice(start, start + itemsPerPage.value);
});

// Status helper functions
const getStatusText = (student) => {
    const hasTimeIn = student.time_in && student.time_in !== 'N/A' && student.time_in !== 'null' && student.time_in !== 'undefined';
    const hasTimeOut = student.time_out && student.time_out !== 'N/A' && student.time_out !== 'null' && student.time_out !== 'undefined';

    if (hasTimeIn && !hasTimeOut) {
        return 'Present';
    } else if (hasTimeIn && hasTimeOut) {
        return 'Already Out';
    } else {
        return 'Unknown';
    }
};

const getStatusClass = (student) => {
    const hasTimeIn = student.time_in && student.time_in !== 'N/A' && student.time_in !== 'null' && student.time_in !== 'undefined';
    const hasTimeOut = student.time_out && student.time_out !== 'N/A' && student.time_out !== 'null' && student.time_out !== 'undefined';

    if (hasTimeIn && !hasTimeOut) {
        return 'bg-green-100 text-green-800'; // Present - Green
    } else if (hasTimeIn && hasTimeOut) {
        return 'bg-red-100 text-red-800'; // Already Out - Red
    } else {
        return 'bg-gray-100 text-gray-800'; // Unknown - Gray
    }
};

const formatDate = (dateString) => {
    if (!dateString || dateString === 'null' || dateString === 'undefined') {
        return 'N/A';
    }

    try {
        let date;

        if (typeof dateString === 'string' && dateString.match(/^\d{4}-\d{2}-\d{2}/)) {
            date = new Date(dateString + 'T00:00:00');
        } else {
            date = new Date(dateString);
        }

        if (isNaN(date.getTime())) {
            console.warn('Invalid date string:', dateString);
            return 'Invalid Date';
        }

        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', dateString, error);
        return 'Invalid Date';
    }
};

const formatTime = (timeString) => {
    if (!timeString || timeString === 'null' || timeString === 'undefined') {
        return 'N/A';
    }

    try {
        let time;

        if (typeof timeString === 'string') {
            if (timeString.match(/^\d{1,2}:\d{2}(:\d{2})?$/)) {
                time = new Date(`1970-01-01T${timeString}${timeString.length === 5 ? ':00' : ''}`);
            } else {
                time = new Date(timeString);
            }
        } else {
            time = new Date(timeString);
        }

        if (isNaN(time.getTime())) {
            console.warn('Invalid time string:', timeString);
            return 'Invalid Time';
        }

        return time.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        console.error('Error formatting time:', timeString, error);
        return 'Invalid Time';
    }
};

// Manual Attendance Functions
const openManualAttendanceModal = () => {
    showManualAttendanceModal.value = true;
    manualAttendanceSearch.value = '';
    selectedManualStudent.value = null;
    studentCurrentRecord.value = null;
    checkingAttendanceStatus.value = false;
};

const closeManualAttendanceModal = () => {
    showManualAttendanceModal.value = false;
    manualAttendanceSearch.value = '';
    selectedManualStudent.value = null;
};

const selectManualStudent = async (student) => {
    selectedManualStudent.value = student;
    manualAttendanceSearch.value = student.name;
    manualStudentSearchResults.value = [];

    // Always check the database for the most recent record
    // Remove the local data check and always call the API
    await checkStudentAttendanceStatus(student.id);
};

const checkStudentRecordInCurrentData = (studentId) => {
    // This function should be removed or modified to call the API
    // instead of checking local data
    checkStudentAttendanceStatus(studentId);
};

const clearManualStudent = () => {
    selectedManualStudent.value = null;
    manualAttendanceSearch.value = '';
    studentCurrentRecord.value = null;
};

const openTimeModal = (type) => {
    timeModalType.value = type;
    showTimeModal.value = true;
    // Set current time as default
    const now = new Date();
    selectedTime.value = now.toTimeString().slice(0, 5);
    selectedAttendanceDate.value = selectedDate.value;
};

const closeTimeModal = () => {
    showTimeModal.value = false;
    selectedTime.value = '';
    timeModalType.value = '';
};

const submitManualAttendance = () => {
    const attendanceData = {
        student_id: selectedManualStudent.value.id,
        date: selectedAttendanceDate.value,
        time: selectedTime.value,
        type: timeModalType.value
    };

    axios.post('/manual-attendance', attendanceData)
        .then(response => {
            Swal.fire({
                title: 'Success!',
                text: response.data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
            fetchStudentRecords(); // Refresh data
            closeTimeModal();
            closeManualAttendanceModal();
        })
        .catch(error => {
            const errorMsg = error.response?.data?.error || 'Error recording attendance';
            Swal.fire({
                title: 'Error!',
                text: errorMsg,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
};

const fetchStudentRecords = () => {
    loading.value = true;
    error.value = '';

    // Fetch all records without unit filtering on the backend
    // We'll do the unit filtering on the frontend
    axios.get('/student-records', {
        params: {
            date: selectedDate.value,
            program: selectedProgram.value || undefined,
            // Remove unit parameter - we'll filter on frontend
        }
    })
        .then((response) => {
            allStudentRecords.value = response.data
                .map((record) => ({
                    id: record.student_id,
                    name: record.name,
                    program: record.students_program,
                    major: record.students_major,
                    unit: record.students_unit, // Make sure this field is correctly mapped
                    date: record.date,
                    time_in: record.record_in,
                    time_out: record.record_out,
                    raw_date: record.date,
                    raw_time_in: record.record_in
                }));

            // Debug: Log the records to verify unit field
            console.log('Total records received:', response.data.length);
            console.log('All student records:', allStudentRecords.value.length);
            console.log('Sample record with unit:', allStudentRecords.value[0]);

            // Log unique units to verify data
            const uniqueUnits = [...new Set(allStudentRecords.value.map(r => r.unit))];
            console.log('Available units in data:', uniqueUnits);
        })
        .catch((err) => {
            console.error('Error fetching student records:', err);
            error.value = 'Failed to load student records. Please try again.';
        })
        .finally(() => {
            loading.value = false;
        });
};

// New computed properties for button visibility
const canTimeIn = () => {
    if (checkingAttendanceStatus.value) return false;

    // Can time in if:
    // 1. No record exists for the date, OR
    // 2. Student has already timed out (completed a full cycle)
    if (!studentCurrentRecord.value) return true;

    const hasTimeIn = studentCurrentRecord.value.time_in &&
        studentCurrentRecord.value.time_in !== 'N/A' &&
        studentCurrentRecord.value.time_in !== null &&
        studentCurrentRecord.value.time_in !== 'null';

    const hasTimeOut = studentCurrentRecord.value.time_out &&
        studentCurrentRecord.value.time_out !== 'N/A' &&
        studentCurrentRecord.value.time_out !== null &&
        studentCurrentRecord.value.time_out !== 'null';

    // Can time in if student has completed a full cycle (both time in and out)
    return !hasTimeIn || (hasTimeIn && hasTimeOut);
};

const canTimeOut = () => {
    if (checkingAttendanceStatus.value) return false;

    // Can time out if:
    // 1. Student has timed in but not timed out yet
    if (!studentCurrentRecord.value) return false;

    const hasTimeIn = studentCurrentRecord.value.time_in &&
        studentCurrentRecord.value.time_in !== 'N/A' &&
        studentCurrentRecord.value.time_in !== null &&
        studentCurrentRecord.value.time_in !== 'null';

    const hasTimeOut = studentCurrentRecord.value.time_out &&
        studentCurrentRecord.value.time_out !== 'N/A' &&
        studentCurrentRecord.value.time_out !== null &&
        studentCurrentRecord.value.time_out !== 'null';

    return hasTimeIn && !hasTimeOut;
};


const getCurrentStatusText = () => {
    if (checkingAttendanceStatus.value) return 'Checking...';

    if (!studentCurrentRecord.value) {
        return 'No Record';
    }

    const hasTimeIn = studentCurrentRecord.value.time_in &&
        studentCurrentRecord.value.time_in !== 'N/A' &&
        studentCurrentRecord.value.time_in !== null &&
        studentCurrentRecord.value.time_in !== 'null';

    const hasTimeOut = studentCurrentRecord.value.time_out &&
        studentCurrentRecord.value.time_out !== 'N/A' &&
        studentCurrentRecord.value.time_out !== null &&
        studentCurrentRecord.value.time_out !== 'null';

    if (hasTimeIn && !hasTimeOut) {
        return 'Present (Inside Campus)';
    } else if (hasTimeIn && hasTimeOut) {
        return 'Completed';
    } else {
        return 'No Record';
    }
};

const getCurrentStatusClass = () => {
    if (checkingAttendanceStatus.value) return 'bg-gray-100 text-gray-600';

    if (!studentCurrentRecord.value) {
        return 'bg-gray-100 text-gray-800';
    }

    const hasTimeIn = studentCurrentRecord.value.time_in &&
        studentCurrentRecord.value.time_in !== 'N/A' &&
        studentCurrentRecord.value.time_in !== null &&
        studentCurrentRecord.value.time_in !== 'null';

    const hasTimeOut = studentCurrentRecord.value.time_out &&
        studentCurrentRecord.value.time_out !== 'N/A' &&
        studentCurrentRecord.value.time_out !== null &&
        studentCurrentRecord.value.time_out !== 'null';

    if (hasTimeIn && !hasTimeOut) {
        return 'bg-green-100 text-green-800'; // Present
    } else if (hasTimeIn && hasTimeOut) {
        return 'bg-blue-100 text-blue-800'; // Completed
    } else {
        return 'bg-gray-100 text-gray-800'; // No Record
    }
};

const selectUnit = (unit) => {
    selectedUnit.value = unit;
    // Reset program filter when unit changes since available programs will change
    selectedProgram.value = '';
    currentPage.value = 1;
};

const buttonClass = (unit) => {
    return [
        'px-3 py-1.5 rounded-lg font-medium transition-all duration-150',
        selectedUnit.value === unit
            ? 'bg-white text-black shadow-sm'
            : 'bg-transparent text-gray-600 hover:bg-gray-200',
    ];
};

const clearFilters = () => {
    searchText.value = '';
    selectedProgram.value = '';
    localSelectedStudent.value = null;
    currentPage.value = 1;
};

const applyFilters = () => {
    currentPage.value = 1;
    // Filters are applied reactively, so this just resets pagination
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const selectStudent = (student) => {
    localSelectedStudent.value = student;
    searchText.value = student.name;
    // You might want to emit this to parent component
    // emit('studentSelected', student);
};

// Watchers
watch(searchText, () => {
    currentPage.value = 1;
});

watch(selectedDate, () => {
    currentPage.value = 1;
    fetchStudentRecords();
});

watch(selectedProgram, () => {
    currentPage.value = 1;
    // Don't refetch - we already have all the data
});

// Watch for selectedStudent prop changes and update search input
watch(() => props.selectedStudent, (newStudent) => {
    if (newStudent && newStudent.name) {
        searchText.value = newStudent.name;
        localSelectedStudent.value = newStudent;
    }
}, { immediate: true });

// Watch for search text changes and clear selection when user types
watch(searchText, (newSearchText, oldSearchText) => {
    // Only clear selection if the user manually changed the search text
    // (not when it was programmatically set by selecting a student)
    if (localSelectedStudent.value &&
        newSearchText !== localSelectedStudent.value.name) {
        localSelectedStudent.value = null;
        // Emit an event to parent to clear selectedStudent if needed
        // emit('clearSelectedStudent');
    }
    // Reset pagination when searching
    currentPage.value = 1;
});

watch(manualAttendanceSearch, (newValue) => {
    if (newValue && newValue.length >= 2) {
        searchManualStudents(newValue);
    } else {
        manualStudentSearchResults.value = [];
    }
});

watch(selectedAttendanceDate, () => {
    if (selectedManualStudent.value) {
        checkStudentRecordInCurrentData(selectedManualStudent.value.id);
    }
});

// Lifecycle
onMounted(() => {
    fetchStudentRecords();
});
</script>
