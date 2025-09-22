<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Loading…'"
    />

    <Widget
      title="<h5>My <span class='fw-semi-bold'>Patients</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="loading"
    >
      <!-- Toolbar -->
      <div class="toolbar d-flex flex-wrap align-items-center mb-3">
        <!-- Search -->
        <b-input-group class="mr-3 mb-2 search-box">
          <b-form-input
            v-model.trim="searchInput"
            placeholder="Search by patient name (first or last)…"
            @keydown.enter.prevent="applySearch"
          />
          <b-input-group-append>
            <b-button variant="primary" @click="applySearch">
              <i class="fa fa-search mr-1" /> Search
            </b-button>
          </b-input-group-append>
        </b-input-group>

        <!-- Page size -->
        <div class="d-flex align-items-center mb-2 page-size">
          <label class="mb-0 mr-2 text-muted small">Rows per page</label>
          <b-form-select
            v-model.number="pageSize"
            :options="pageSizeOptions"
            class="page-size-select"
            @change="onPageSizeChange"
          />
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
            <tr class="text-muted">
              <th>First Name</th>
              <th>Last Name</th>
              <th>Gender</th>
              <th>D.O.B</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Doctor</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedPatients" :key="row.id">
              <td>{{ row.first_name }}</td>
              <td>{{ row.last_name }}</td>
              <td>{{ capFirst(row.gender) }}</td>
              <td>{{ safeDate(row.date_of_birth) }}</td>
              <td>{{ row.email || '—' }}</td>
              <td>{{ row.phone || '—' }}</td>
              <td>{{ row.doctor ? row.doctor.name : '—' }}</td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'bg-success': row.status === 'completed',
                    'bg-info': row.status === 'booked',
                    'bg-warning': row.status !== 'completed' && row.status !== 'booked'
                  }"
                >
                  {{ capFirst(row.status || '') || '—' }}
                </span>
              </td>
              <td>
                <b-button
                  v-if="row.status === 'booked'"
                  variant="primary"
                  size="sm"
                  @click="viewDetails(row)"
                >
                  <i class="fa fa-book" aria-hidden="true"></i> Prescribe
                </b-button>

                <b-button
                  v-else
                  v-b-modal.modal-consultation
                  variant="primary"
                  size="sm"
                  @click="sendInfo(row)"
                >
                  <span class="fa fa-search-plus" /> Book Consultation
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedPatients.length && !loading">
              <td colspan="9" class="text-center text-muted">No patients found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Client-side Pagination -->
      <div
        class="pagination-bar d-flex flex-wrap justify-content-between align-items-center mt-3"
        v-if="totalPages > 1"
      >
        <div class="btn-group mb-2">
          <b-button
            size="sm"
            variant="outline-secondary"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            ← Previous
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            Next →
          </b-button>
        </div>

        <!-- Numbered pages (compact) -->
        <div class="mb-2">
          <b-button
            v-if="totalPages >= 1"
            size="sm"
            class="mx-1"
            :variant="pageBtnVariant(1)"
            @click="goToPage(1)"
          >
            1
          </b-button>

          <span v-if="showLeftEllipsis" class="mx-1">…</span>

          <b-button
            v-for="p in middlePages"
            :key="'p'+p"
            size="sm"
            class="mx-1"
            :variant="pageBtnVariant(p)"
            @click="goToPage(p)"
          >
            {{ p }}
          </b-button>

          <span v-if="showRightEllipsis" class="mx-1">…</span>

          <b-button
            v-if="totalPages > 1"
            size="sm"
            class="mx-1"
            :variant="pageBtnVariant(totalPages)"
            @click="goToPage(totalPages)"
          >
            {{ totalPages }}
          </b-button>
        </div>
      </div>
    </Widget>

    <!-- Keep your existing modals (outside the Widget to avoid layout issues) -->
    <b-modal
      id="modal-consultation"
      ref="modal-consultation"
      size="lg"
      title="Book Consultation"
      hide-footer
    >
      <book-consultation :selectedPatient="selectedPatient" />
    </b-modal>

    <b-modal
      id="modal-new-walk-in-patient"
      size="lg"
      ref="modal-new-walk-in-patient"
      title="New Patient"
      hide-footer
    >
      <walk-in-patient-details />
    </b-modal>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "WalkInPatientsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,

      // normalized array of patients
      patients: [],

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
      if (!q) return this.patients;

      return this.patients.filter((p) => {
        const first = (p.first_name || "").toLowerCase();
        const last = (p.last_name || "").toLowerCase();
        return first.includes(q) || last.includes(q);
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
        .get(this.$base_url + "walk_in_patient_details", authHeader())
        .then(({ data }) => {
          // Normalize typical shapes:
          // { data: [...] } OR { data: { data: [...] } }
          const payload = data && data.data ? data.data : data;
          const list = Array.isArray(payload) ? payload : (payload && payload.data) || [];

          this.patients = list.map((p) => ({
            id: p.id,
            first_name: p.first_name,
            last_name: p.last_name,
            gender: p.gender,
            date_of_birth: p.date_of_birth,
            email: p.email,
            phone: p.phone,
            doctor: p.doctor || null,
            status: p.status || "pending",
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

    // pagination
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    pageBtnVariant(p) {
      return p === this.currentPage ? "primary" : "outline-secondary";
    },

    // search
    applySearch() {
      this.currentPage = 1;
    },

    // rows-per-page
    onPageSizeChange() {
      this.currentPage = 1;
    },

    // actions
    viewDetails(item) {
      this.$router.push({ name: "walkinpatientpage", params: { patient: item.id } });
    },
    sendInfo(item) {
      this.selectedPatient = item;
      // Button already has v-b-modal to open the modal
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
