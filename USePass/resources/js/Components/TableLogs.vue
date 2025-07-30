<template>
    <div class="log-container">
        <div class="header-section">
            <h2 class="log-title">Activity Logs</h2>
            <div class="date-picker">
                <label for="date" class="date-label">Select Date:</label>
                <input
                    class="date-input"
                    type="date"
                    id="date"
                    v-model="selectedDate"
                />
            </div>
        </div>

        <div class="table-wrapper">
            <table class="log-table">
                <thead class="table-header">
                <tr>
                    <th class="th-time">Time</th>
                    <th class="th-user">User</th>
                    <th class="th-action">Action</th>
                    <th class="th-description">Description</th>
                </tr>
                </thead>
                <tbody class="table-body">
                <tr v-for="(log, index) in paginatedLogs" :key="index" class="table-row">
                    <td class="td-time">
                        <span class="time-badge">{{ log.time }}</span>
                    </td>
                    <td class="td-user">
                        <span class="user-name">{{ log.user }}</span>
                    </td>
                    <td class="td-action">
                        <span class="action-text">{{ log.action }}</span>
                    </td>
                    <td class="td-description">{{ log.description }}</td>
                </tr>
                </tbody>
            </table>

            <div v-if="filteredLogs.length === 0" class="no-data">
                <div class="no-data-icon">📅</div>
                <p class="no-data-text">No logs found for the selected date</p>
            </div>
        </div>

        <!-- Pagination Section -->
        <div v-if="filteredLogs.length > 0" class="pagination-section">
            <div class="pagination-info">
                <span class="pagination-text">
                    Showing {{ startRecord }} to {{ endRecord }} of {{ totalRecords }} entries
                </span>
                <div class="items-per-page">
                    <label for="itemsPerPage" class="items-label">Show:</label>
                    <select
                        id="itemsPerPage"
                        v-model="itemsPerPage"
                        class="items-select"
                        @change="resetToFirstPage"
                    >
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="pagination-controls">
                <button
                    @click="goToPage(1)"
                    :disabled="currentPage === 1"
                    class="pagination-btn pagination-btn-first"
                    title="First page"
                >
                    ⏮
                </button>
                <button
                    @click="previousPage"
                    :disabled="currentPage === 1"
                    class="pagination-btn pagination-btn-prev"
                    title="Previous page"
                >
                    ◀
                </button>

                <div class="page-numbers">
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        @click="goToPage(page)"
                        :class="['page-btn', { 'page-btn-active': page === currentPage }]"
                    >
                        {{ page }}
                    </button>
                </div>

                <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="pagination-btn pagination-btn-next"
                    title="Next page"
                >
                    ▶
                </button>
                <button
                    @click="goToPage(totalPages)"
                    :disabled="currentPage === totalPages"
                    class="pagination-btn pagination-btn-last"
                    title="Last page"
                >
                    ⏭
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "LogTable",
    data() {
        return {
            selectedDate: "2025-06-27",
            currentPage: 1,
            itemsPerPage: 5,
            logs: [
                { time: "06/27/2025 | 11:35 AM", user: "John Doe Supranes", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe Vaughn Rhommer Theodore R. Gucor", action: "Submit Report", description: "John Doe Submit Report" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "Validate Report", description: "John Doe Validate Report" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "Submit Report", description: "John Doe Submit Report" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "Validate Report", description: "John Doe Validate Report" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/27/2025 | 11:35 AM", user: "John Doe", action: "QR Scan", description: "Froilan Canete Enter" },
                { time: "06/28/2025 | 02:15 PM", user: "Jane Smith", action: "Login", description: "User logged into system" },
                { time: "06/28/2025 | 02:20 PM", user: "Mike Johnson", action: "Export Data", description: "Downloaded monthly report" },
                // Adding more sample data to demonstrate pagination
                { time: "06/27/2025 | 09:15 AM", user: "Alice Williams", action: "QR Scan", description: "Employee check-in" },
                { time: "06/27/2025 | 09:30 AM", user: "Bob Brown", action: "Submit Report", description: "Weekly status report" },
                { time: "06/27/2025 | 10:00 AM", user: "Carol Davis", action: "Validate Report", description: "Approved timesheet" },
                { time: "06/27/2025 | 10:15 AM", user: "David Wilson", action: "QR Scan", description: "Meeting room access" },
                { time: "06/27/2025 | 10:30 AM", user: "Eva Miller", action: "Export Data", description: "Client report export" },
                { time: "06/27/2025 | 11:00 AM", user: "Frank Garcia", action: "Login", description: "System access" },
                { time: "06/27/2025 | 11:15 AM", user: "Grace Lee", action: "QR Scan", description: "Parking access" },
                { time: "06/27/2025 | 12:00 PM", user: "Henry Taylor", action: "Submit Report", description: "Expense report" },
            ],
        };
    },
    computed: {
        filteredLogs() {
            return this.logs.filter(log =>
                log.time.startsWith(this.formatDate(this.selectedDate))
            );
        },
        totalRecords() {
            return this.filteredLogs.length;
        },
        totalPages() {
            return Math.ceil(this.totalRecords / this.itemsPerPage);
        },
        startRecord() {
            return this.totalRecords === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1;
        },
        endRecord() {
            const end = this.currentPage * this.itemsPerPage;
            return end > this.totalRecords ? this.totalRecords : end;
        },
        paginatedLogs() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredLogs.slice(start, end);
        },
        visiblePages() {
            const pages = [];
            const maxVisible = 5;

            if (this.totalPages <= maxVisible) {
                for (let i = 1; i <= this.totalPages; i++) {
                    pages.push(i);
                }
            } else {
                let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
                let end = Math.min(this.totalPages, start + maxVisible - 1);

                if (end - start + 1 < maxVisible) {
                    start = Math.max(1, end - maxVisible + 1);
                }

                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
            }

            return pages;
        },
    },
    watch: {
        selectedDate() {
            this.resetToFirstPage();
        },
    },
    methods: {
        formatDate(dateStr) {
            const date = new Date(dateStr);
            const mm = String(date.getMonth() + 1).padStart(2, "0");
            const dd = String(date.getDate()).padStart(2, "0");
            const yyyy = date.getFullYear();
            return `${mm}/${dd}/${yyyy}`;
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        resetToFirstPage() {
            this.currentPage = 1;
        },
        getUserInitials(userName) {
            return userName
                .split(' ')
                .map(name => name.charAt(0))
                .join('')
                .substring(0, 2)
                .toUpperCase();
        },
        getActionBadgeClass(action) {
            const baseClass = 'action-badge';
            switch (action.toLowerCase()) {
                case 'qr scan':
                    return `${baseClass} action-qr`;
                case 'submit report':
                    return `${baseClass} action-submit`;
                case 'validate report':
                    return `${baseClass} action-validate`;
                case 'login':
                    return `${baseClass} action-login`;
                case 'export data':
                    return `${baseClass} action-export`;
                default:
                    return `${baseClass} action-default`;
            }
        },
    },
};
</script>

<style scoped>
.log-container {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    padding: 18px 20px 18px 20px;
    width: 100%;
    max-width: 100%;
    margin: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    /* Remove this line: height: 700px; */
    display: flex;
    flex-direction: column;
}

.table-wrapper {
    overflow: auto;
    background: white;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    position: relative;
    margin-bottom: 20px;
    max-height: 400px;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-shrink: 0;
}

.log-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.date-picker {
    display: flex;
    align-items: center;
    gap: 12px;
}

.date-label {
    font-weight: 500;
    color: #475569;
    font-size: 14px;
}

.date-input {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.2s ease;
}

.date-input:focus {
    outline: none;
    border-color: #760000;
    box-shadow: 0 0 0 3px rgba(118, 0, 0, 0.1);
}


.log-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.table-header {
    position: sticky;
    top: 0;
    background: #f8fafc;
    z-index: 10;
}

.table-header th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e5e7eb;
}

.th-time { width: 180px; }
.th-user { width: 250px; }
.th-action { width: 140px; }
.th-description { width: auto; }

.table-row {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.table-row:hover {
    background: #f8fafc;
}

.table-row td {
    padding: 16px;
    vertical-align: middle;
}

.time-badge {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.user-name {
    color: #374151;
    font-weight: 500;
    font-size: 14px;
    word-break: break-word;
}

.action-text {
    color: #374151;
    font-weight: 500;
    font-size: 14px;
}

.td-description {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.4;
}

.no-data {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.no-data-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.no-data-text {
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

/* Pagination Styles */
.pagination-section {
    flex-shrink: 0;
    background: white;
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.pagination-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.pagination-text {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.items-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
}

.items-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.items-select {
    padding: 6px 10px;
    border: 1px solid #760000;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
}

.items-select:focus {
    outline: none;
    border-color: #760000;
    box-shadow: 0 0 0 3px rgba(118, 0, 0, 0.1);
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn {
    padding: 8px 12px;
    border: 1px solid #760000;
    background: white;
    color: #760000;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-btn:hover:not(:disabled) {
    background: #760000;
    border-color: #760000;
    color: white;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f9fafb;
    border-color: #d1d5db;
    color: #9ca3af;
}

.page-numbers {
    display: flex;
    gap: 4px;
    margin: 0 8px;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #760000;
    background: white;
    color: #760000;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.page-btn:hover {
    background: #760000;
    border-color: #760000;
    color: white;
}

.page-btn-active {
    background: #760000;
    border-color: #760000;
    color: white;
}

.page-btn-active:hover {
    background: #5a0000;
    border-color: #5a0000;
    color: white;
}

/* Scrollbar styling */
.table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.table-wrapper::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive design */
@media (max-width: 768px) {
    .log-container {
        padding: 16px;
        height: 600px;
    }

    .header-section {
        flex-direction: column;
        gap: 16px;
        align-items: stretch;
    }

    .log-title {
        font-size: 20px;
        text-align: center;
    }

    .date-picker {
        justify-content: center;
    }

    .table-header th {
        padding: 12px 8px;
        font-size: 11px;
    }

    .table-row td {
        padding: 12px 8px;
    }

    .th-time { width: 140px; }
    .th-user { width: 180px; }
    .th-action { width: 120px; }

    .user-name {
        font-size: 13px;
    }

    .pagination-section {
        flex-direction: column;
        gap: 16px;
        padding: 16px;
    }

    .pagination-info {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }

    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
    }

    .page-numbers {
        order: -1;
        margin: 0;
    }
}
</style>
