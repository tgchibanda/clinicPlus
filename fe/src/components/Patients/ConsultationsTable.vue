<template>
  <div>
    <vue-element-loading :active="isLoading" :is-full-screen="true" :size="'80'" :color="'#FF6700'"
      :text="'Loading…'" />

    <Widget title="<h5>My <span class='fw-semi-bold'>Patients</span></h5>" bodyClass="widget-table-overflow"
      customHeader :fetchingData="loading">
      <!-- Overlaid top-right button (same place as before) -->

      <!-- Toolbar -->
      <div class="toolbar d-flex flex-wrap align-items-center mb-3">
        <!-- Search -->
        <b-input-group class="mr-3 mb-2 search-box">
          <b-form-input v-model.trim="searchInput" placeholder="Search by patient name…"
            @keydown.enter.prevent="applySearch" />
          <b-input-group-append>
            <b-button variant="primary" @click="applySearch">
              <i class="fa fa-search mr-1"></i> Search
            </b-button>
          </b-input-group-append>
        </b-input-group>


        <!-- Page Size -->
        <div class="d-flex align-items-center mb-2">
          <label class="mb-0 mr-2 text-muted small">Rows per page</label>
          <b-form-select v-model.number="pageSize" :options="pageSizeOptions" class="page-size-select"
            @change="onPageSizeChange" />
          Date
          <b-form-datepicker
  v-model="dateFilter"
  @input="onDateSelected"
  class="mr-3 mb-2"
  placeholder="Filter by date"
/>
          <b-button variant="secondary" class="mb-2" @click="clearDateFilter">
            Clear
          </b-button>
        </div>

        <!-- Summary -->
        <div class="text-muted small ml-auto mb-2">
          <template v-if="filteredPatients.length">
            Showing {{ startRow }}–{{ endRow }} of {{ filteredPatients.length }} result(s)
          </template>
          <template v-else>
            No results
          </template>
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
                  'bg-info': row.status === '0'
                }">
                  <span v-if="row.status === '0'">Booked</span>
                  <span v-else-if="row.status === '4'">Completed</span>
                  <span v-else="row.status === '0'">Cancelled</span>
                </span>
              </td>
              <td>
                <b-button v-if="row.status === '0'" variant="primary" size="sm" @click="viewDetails(row)">
                  <i class="fa fa-book" aria-hidden="true"></i> Prescribe
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedPatients.length && !loading">
              <td colspan="9" class="text-center text-muted">No consultations found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Client-side Pagination -->
      <div class="pagination-bar d-flex flex-wrap justify-content-between align-items-center mt-3"
        v-if="totalPages > 1">
        <div class="btn-group mb-2">
          <b-button size="sm" variant="outline-secondary" :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)">
            ← Previous
          </b-button>
          <b-button size="sm" variant="outline-secondary" :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)">
            Next →
          </b-button>
        </div>

        <!-- Numbered pages (compact) -->
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
      dateFilter: null,

      // normalized array of consultations
      consultations: [],

      // search + pagination (client-side)
      searchInput: "",
      currentPage: 1,
      pageSize: 5,
      pageSizeOptions: [5, 10, 15, 20],

      selectedPatient: null,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
    };
  },
  computed: {
    // filter by first/last name
    filteredPatients() {
      const q = (this.searchInput || "").trim().toLowerCase();
      if (!q) return this.consultations;

      return this.consultations.filter((p) => {
    // name filter
    const first = p.patient.first_name?.toLowerCase() || "";
    const last = p.patient.last_name?.toLowerCase() || "";
    const matchName = first.includes(q) || last.includes(q);

    // date filter
    let matchDate = true;
    if (this.dateFilter) {
      const rowDate = p.start_at ? p.start_at.slice(0, 10) : "";
      matchDate = rowDate === this.dateFilter;
    }

    return matchName && matchDate;
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
  },
  methods: {
    loadWalkInPatients() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "consultations-report", authHeader())
        .then(({ data }) => {
          // Normalize typical shapes:
          // { data: [...] } OR { data: { data: [...] } }
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
    filteredPatients() {
  const q = (this.searchInput || "").trim().toLowerCase();

  return this.consultations.filter((p) => {
    const first = p.patient.first_name?.toLowerCase() || "";
    const last = p.patient.last_name?.toLowerCase() || "";

    const matchesName = first.includes(q) || last.includes(q);

    // --- DATE FILTER ---
    let matchesDate = true;
    if (this.dateFilter) {
      const rowDate = p.start_at.slice(0, 10); // "2025-12-31"
      matchesDate = rowDate === this.dateFilter;
    }

    return matchesName && matchesDate;
  });
},
    // Convert API date → readable format (e.g. "31 Dec 2025, 11:30 AM")
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

  applySearch() {
    this.currentPage = 1;
  },

  // When selecting a date from datepicker
  onDateSelected(val) {
    if (!val) {
      this.dateFilter = null;
      this.currentPage = 1;
      return;
    }

    // Convert picker value to YYYY-MM-DD
    const d = new Date(val);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const dd = String(d.getDate()).padStart(2, "0");

    this.dateFilter = `${yyyy}-${mm}-${dd}`;
    this.currentPage = 1;
  },

  clearDateFilter() {
    this.dateFilter = null;
    this.currentPage = 1;
  },
    // pagination
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    pageBtnVariant(p) {
      return p === this.currentPage ? "primary" : "outline-secondary";
    },

    // rows-per-page
    onPageSizeChange() {
      this.currentPage = 1;
    },

    // actions
    viewDetails(item) {
      this.$router.push({ name: "walkinpatientpage", params: { patient: item.patient.id } });
    },

    // helpers
    capFirst(v) {
      if (!v) return "—";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    safeDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime()) ? val : d.toLocaleDateString();
    },
  },
  created() {
    this.loadWalkInPatients();
  },
};
</script>

<style scoped>
/* Toolbar padding + layout (kept to match your prescriptions page) */
.widget-wrap {
  position: relative;
}

.toolbar {
  padding: 14px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

.search-box {
  min-width: 280px;
  max-width: 420px;
}

.page-size-select {
  min-width: 100px;
}

/* Pagination bar padding */
.pagination-bar {
  padding: 12px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

/* Table spacing tweaks */
.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}
</style>
