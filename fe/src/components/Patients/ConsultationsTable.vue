<template>
  <div>
    <vue-element-loading :active="isLoading" :is-full-screen="true" :size="'80'" :color="'#FF6700'"
      :text="'Loading…'" />

    <Widget title="<h5>My <span class='fw-semi-bold'>Patients</span></h5>" bodyClass="widget-table-overflow"
      customHeader :fetchingData="loading">

      <!-- Enhanced Toolbar -->
      <div class="toolbar">
        <!-- First Row: Search and Status Filter -->
        <div class="d-flex flex-wrap align-items-center mb-3">
          <!-- Search -->
          <b-input-group class="mr-3 mb-2 search-box">
            <b-form-input v-model.trim="searchInput" placeholder="Search by patient name…"
              @keydown.enter.prevent="applyFilters" />
            <b-input-group-append>
              <b-button variant="primary" @click="applyFilters">
                <i class="fa fa-search"></i>
              </b-button>
            </b-input-group-append>
          </b-input-group>

          <!-- Status Filter -->
          <div class="mr-3 mb-2">
            <b-form-select v-model="statusFilter" :options="statusOptions" class="status-select"
              @change="applyFilters">
              <template #first>
                <b-form-select-option :value="null">All Statuses</b-form-select-option>
              </template>
            </b-form-select>
          </div>

          <!-- Doctor Filter -->
          <div class="mr-3 mb-2">
            <b-form-select v-model="doctorFilter" :options="doctorOptions" class="filter-select"
              @change="applyFilters">
              <template #first>
                <b-form-select-option :value="null">All Doctors</b-form-select-option>
              </template>
            </b-form-select>
          </div>

          <!-- Location Filter -->
          <div class="mr-3 mb-2">
            <b-form-select v-model="locationFilter" :options="locationOptions" class="filter-select"
              @change="applyFilters">
              <template #first>
                <b-form-select-option :value="null">All Locations</b-form-select-option>
              </template>
            </b-form-select>
          </div>
        </div>

        <!-- Second Row: Date Filters and Controls -->
        <div class="d-flex flex-wrap align-items-center justify-content-between">
          <div class="d-flex flex-wrap align-items-center">
            <!-- Date Range Toggle -->
            <b-form-checkbox v-model="useDateRange" switch class="mr-3 mb-2">
              Date Range
            </b-form-checkbox>

            <!-- Single Date Picker -->
            <div v-if="!useDateRange" class="mr-3 mb-2">
              <b-form-datepicker v-model="singleDate" @input="applyFilters" placeholder="Select date"
                class="date-picker" />
            </div>

            <!-- Date Range Pickers -->
            <div v-else class="d-flex align-items-center mr-3 mb-2">
              <b-form-datepicker v-model="startDate" @input="applyFilters" placeholder="Start date"
                class="date-picker mr-2" />
              <span class="mx-2">to</span>
              <b-form-datepicker v-model="endDate" @input="applyFilters" placeholder="End date"
                class="date-picker" />
            </div>

            <!-- Clear Filters Button -->
            <b-button variant="outline-secondary" class="mb-2 mr-3" @click="clearAllFilters">
              <i class="fa fa-times mr-1"></i> Clear Filters
            </b-button>
          </div>

          <!-- Right Side: Rows per page and Summary -->
          <div class="d-flex align-items-center">
            <label class="mb-0 mr-2 text-muted small">Show</label>
            <b-form-select v-model.number="pageSize" :options="pageSizeOptions" class="page-size-select mb-2"
              @change="onPageSizeChange" size="sm" />
            <span class="ml-2 mb-2 text-muted small">
              <template v-if="filteredPatients.length">
                {{ startRow }}–{{ endRow }} of {{ filteredPatients.length }}
              </template>
              <template v-else>
                No results
              </template>
            </span>
          </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="active-filters mt-2">
          <small class="text-muted">Active filters:</small>
          <b-badge v-if="statusFilter" variant="info" class="ml-2">
            Status: {{ getStatusLabel(statusFilter) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="statusFilter = null; applyFilters()"></i>
          </b-badge>
          <b-badge v-if="doctorFilter" variant="info" class="ml-2">
            Doctor: {{ getDoctorName(doctorFilter) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="doctorFilter = null; applyFilters()"></i>
          </b-badge>
          <b-badge v-if="locationFilter" variant="info" class="ml-2">
            Location: {{ getLocationName(locationFilter) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="locationFilter = null; applyFilters()"></i>
          </b-badge>
          <b-badge v-if="singleDate && !useDateRange" variant="info" class="ml-2">
            Date: {{ formatDateShort(singleDate) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="singleDate = null; applyFilters()"></i>
          </b-badge>
          <b-badge v-if="startDate && useDateRange" variant="info" class="ml-2">
            From: {{ formatDateShort(startDate) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="startDate = null; applyFilters()"></i>
          </b-badge>
          <b-badge v-if="endDate && useDateRange" variant="info" class="ml-2">
            To: {{ formatDateShort(endDate) }}
            <i class="fa fa-times ml-1 cursor-pointer" @click="endDate = null; applyFilters()"></i>
          </b-badge>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Phone</th>
              <th>Doctor</th>
              <th>Date</th>
              <th>Location</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedPatients" :key="row.id">
              <td>{{ row.patient.first_name + ' ' + row.patient.last_name }}</td>
              <td>{{ row.patient.phone }}</td>
              <td>{{ row.doctor ? row.doctor.name : '—' }}</td>
              <td>{{ formatDate(row.start_at) }}</td>
              <td>{{ row.location.name }}</td>
              <td>
                <span class="badge" :class="{
                  'bg-success': row.status === '4',
                  'bg-info': row.status === '0',
                  'bg-secondary': row.status !== '0' && row.status !== '4'
                }">
                  {{ getStatusLabel(row.status) }}
                </span>
              </td>
              <td>
                <b-button v-if="row.status === '0'" variant="primary" size="sm" @click="viewDetails(row)">
                  <i class="fa fa-book"></i> Prescribe
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedPatients.length && !loading">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                No consultations found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-bar d-flex flex-wrap justify-content-between align-items-center mt-3"
        v-if="totalPages > 1">
        <div class="btn-group mb-2">
          <b-button size="sm" variant="outline-secondary" :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)">
            <i class="fa fa-chevron-left"></i>
          </b-button>
          <b-button size="sm" variant="outline-secondary" :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)">
            <i class="fa fa-chevron-right"></i>
          </b-button>
        </div>

        <div class="mb-2">
          <b-button v-if="totalPages >= 1" size="sm" class="mx-1" :variant="pageBtnVariant(1)" @click="goToPage(1)">
            1
          </b-button>

          <span v-if="showLeftEllipsis" class="mx-1">…</span>

          <b-button v-for="p in middlePages" :key="'p' + p" size="sm" class="mx-1" :variant="pageBtnVariant(p)"
            @click="goToPage(p)">
            {{ p }}
          </b-button>

          <span v-if="showRightEllipsis" class="mx-1">…</span>

          <b-button v-if="totalPages > 1" size="sm" class="mx-1" :variant="pageBtnVariant(totalPages)"
            @click="goToPage(totalPages)">
            {{ totalPages }}
          </b-button>
        </div>
      </div>
    </Widget>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "ConsultationsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,

      // data
      consultations: [],

      // filters
      searchInput: "",
      statusFilter: '0', // Default to 'Booked'
      doctorFilter: null,
      locationFilter: null,
      useDateRange: false,
      singleDate: null,
      startDate: null,
      endDate: null,

      // pagination
      currentPage: 1,
      pageSize: 10,
      pageSizeOptions: [
        { value: 5, text: '5' },
        { value: 10, text: '10' },
        { value: 15, text: '15' },
        { value: 20, text: '20' },
        { value: 50, text: '50' }
      ],

      // status options
      statusOptions: [
        { value: '0', text: 'Booked' },
        { value: '4', text: 'Completed' },
        { value: 'cancelled', text: 'Cancelled' }
      ],

      selectedPatient: null,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
    };
  },
  computed: {
    filteredPatients() {
      const q = (this.searchInput || "").trim().toLowerCase();

      return this.consultations.filter((p) => {
        // Name filter
        const first = p.patient.first_name?.toLowerCase() || "";
        const last = p.patient.last_name?.toLowerCase() || "";
        const matchName = !q || first.includes(q) || last.includes(q);

        // Status filter
        const matchStatus = !this.statusFilter || p.status === this.statusFilter;

        // Doctor filter
        const matchDoctor = !this.doctorFilter || 
          (p.doctor && p.doctor.id === this.doctorFilter);

        // Location filter
        const matchLocation = !this.locationFilter || 
          (p.location && p.location.id === this.locationFilter);

        // Date filter
        let matchDate = true;
        if (p.start_at) {
          const rowDate = new Date(p.start_at);
          rowDate.setHours(0, 0, 0, 0);

          if (this.useDateRange) {
            // Date range filter
            if (this.startDate) {
              const start = new Date(this.startDate);
              start.setHours(0, 0, 0, 0);
              matchDate = matchDate && rowDate >= start;
            }
            if (this.endDate) {
              const end = new Date(this.endDate);
              end.setHours(23, 59, 59, 999);
              matchDate = matchDate && rowDate <= end;
            }
          } else if (this.singleDate) {
            // Single date filter
            const filterDate = new Date(this.singleDate);
            filterDate.setHours(0, 0, 0, 0);
            matchDate = rowDate.getTime() === filterDate.getTime();
          }
        }

        return matchName && matchStatus && matchDoctor && matchLocation && matchDate;
      });
    },
    totalPages() {
      const total = this.filteredPatients.length;
      return Math.max(1, Math.ceil(total / this.pageSize));
    },
    pagedPatients() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredPatients.slice(start, start + this.pageSize);
    },
    middlePages() {
      const cur = this.currentPage;
      const last = this.totalPages;
      const pages = [];
      for (let p = cur - 1; p <= cur + 1; p++) {
        if (p > 1 && p < last) pages.push(p);
      }
      return pages;
    },
    showLeftEllipsis() {
      return this.currentPage > 3;
    },
    showRightEllipsis() {
      return this.currentPage < this.totalPages - 2;
    },
    startRow() {
      if (!this.filteredPatients.length) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },
    endRow() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.filteredPatients.length);
    },
    hasActiveFilters() {
      return this.statusFilter || this.doctorFilter || this.locationFilter || 
             this.singleDate || this.startDate || this.endDate;
    },
    doctorOptions() {
      // Extract unique doctors from consultations
      const doctors = this.consultations
        .filter(c => c.doctor)
        .map(c => c.doctor)
        .filter((doctor, index, self) => 
          index === self.findIndex(d => d.id === doctor.id)
        )
        .sort((a, b) => a.name.localeCompare(b.name));

      return doctors.map(d => ({
        value: d.id,
        text: d.name
      }));
    },
    locationOptions() {
      // Extract unique locations from consultations
      const locations = this.consultations
        .filter(c => c.location)
        .map(c => c.location)
        .filter((location, index, self) => 
          index === self.findIndex(l => l.id === location.id)
        )
        .sort((a, b) => a.name.localeCompare(b.name));

      return locations.map(l => ({
        value: l.id,
        text: l.name
      }));
    }
  },
  methods: {
    loadWalkInPatients() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "consultations-report", authHeader())
        .then(({ data }) => {
          const payload = data && data.data ? data.data : data;
          const list = Array.isArray(payload) ? payload : (payload && payload.data) || [];

          this.consultations = list.map((c) => ({
            id: c.id,
            patient: c.patient || null,
            doctor: c.doctor || null,
            status: c.status || "pending",
            start_at: c.start_at,
            end_at: c.end_at,
            location_id: c.location_id,
            location: c.location,
          }));

          this.currentPage = 1;
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          const msg =
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            error.toString();
          this.$swal("error!", "There was an error: " + msg, "error");
        });
    },
    
    formatDate(dateStr) {
      if (!dateStr) return "—";
      const d = new Date(dateStr);
      return d.toLocaleString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    },

    formatDateShort(dateStr) {
      if (!dateStr) return "";
      const d = new Date(dateStr);
      return d.toLocaleDateString("en-US", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    },

    getStatusLabel(status) {
      const option = this.statusOptions.find(opt => opt.value === status);
      return option ? option.text : status;
    },

    getDoctorName(doctorId) {
      const doctor = this.consultations.find(c => c.doctor && c.doctor.id === doctorId)?.doctor;
      return doctor ? doctor.name : '';
    },

    getLocationName(locationId) {
      const location = this.consultations.find(c => c.location && c.location.id === locationId)?.location;
      return location ? location.name : '';
    },

    applyFilters() {
      this.currentPage = 1;
    },

    clearAllFilters() {
      this.searchInput = "";
      this.statusFilter = '0'; // Reset to default 'Booked'
      this.doctorFilter = null;
      this.locationFilter = null;
      this.singleDate = null;
      this.startDate = null;
      this.endDate = null;
      this.currentPage = 1;
    },

    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },

    pageBtnVariant(p) {
      return p === this.currentPage ? "primary" : "outline-secondary";
    },

    onPageSizeChange() {
      this.currentPage = 1;
    },

    viewDetails(item) {
      this.$router.push({ name: "walkinpatientpage", params: { patient: item.patient.id } });
    },
  },
  created() {
    // Set default date to today
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    this.singleDate = `${yyyy}-${mm}-${dd}`;
    
    this.loadWalkInPatients();
  },
};
</script>

<style scoped>
/* Toolbar */
.toolbar {
  padding: 18px;
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.search-box {
  min-width: 280px;
  max-width: 360px;
}

.status-select {
  min-width: 150px;
}

.filter-select {
  min-width: 180px;
}

.date-picker {
  min-width: 160px;
}

.page-size-select {
  min-width: 70px;
  max-width: 80px;
}

/* Active Filters */
.active-filters {
  padding-top: 8px;
  border-top: 1px solid #e0e0e0;
}

.active-filters .badge {
  padding: 6px 10px;
  font-weight: 500;
}

.cursor-pointer {
  cursor: pointer;
}

/* Pagination */
.pagination-bar {
  padding: 12px 16px;
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

/* Table */
.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.9rem;
  padding-bottom: 0.9rem;
}

.table-responsive {
  border-radius: 8px;
  overflow: hidden;
}

/* Status badges */
.badge {
  padding: 6px 12px;
  font-weight: 500;
  font-size: 0.85rem;
}

.bg-success {
  background-color: #28a745 !important;
}

.bg-info {
  background-color: #17a2b8 !important;
}

.bg-secondary {
  background-color: #6c757d !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .search-box {
    min-width: 100%;
    max-width: 100%;
  }
  
  .status-select,
  .filter-select,
  .date-picker {
    min-width: 100%;
  }
}
</style>