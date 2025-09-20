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
      title="<h5>My <span class='fw-semi-bold'>Prescriptions</span></h5>"
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
          <template v-if="filteredPrescriptions.length">
            Showing {{ startRow }}–{{ endRow }} of {{ filteredPrescriptions.length }} result(s)
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
              <th>#</th>
              <th>Patient</th>
              <th>Doctor</th>
              <th>Notes</th>
              <th>Items</th>
              <th>Status</th>
              <th>Created</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedPrescriptions" :key="row.id">
              <td>{{ row.id }}</td>

              <td>
                {{ fullName(row.patient) }}
                <div class="small text-muted">
                  <span v-if="row.patient && row.patient.email">{{ row.patient.email }}</span>
                  <span v-if="row.patient && row.patient.phone">
                    <span v-if="row.patient && row.patient.email"> • </span>{{ row.patient.phone }}
                  </span>
                </div>
              </td>

              <td>{{ row.doctor ? row.doctor.name : '—' }}</td>

              <td>
                <span :title="row.notes">{{ truncate(row.notes, 50) }}</span>
              </td>

              <td>
                <span class="badge bg-secondary">{{ (row.items && row.items.length) || 0 }}</span>
              </td>

              <td>
                <span class="badge" :class="statusClass(row.status)">
                  {{ capFirst(row.status || '') || '—' }}
                </span>
              </td>

              <td>{{ dateTime(row.created_at) }}</td>

              <td class="text-right">
                <b-button size="sm" variant="primary" class="mr-2" @click="viewPrescription(row)">
                  <span class="fa fa-eye" /> View
                </b-button>

                <b-button size="sm" variant="success" @click="makePayment(row)">
                  <span class="fa fa-credit-card" /> Pay
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedPrescriptions.length && !loading">
              <td colspan="8" class="text-center text-muted">No prescriptions found.</td>
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
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "PrescriptionsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,

      // raw list pulled from API (we'll paginate client-side)
      prescriptions: [],

      // search + pagination (client-side)
      searchInput: "",
      currentPage: 1,
      pageSize: 5,
      pageSizeOptions: [5, 10, 15, 20],
    };
  },
  computed: {
    // filter by patient first/last name
    filteredPrescriptions() {
      const q = (this.searchInput || "").trim().toLowerCase();
      if (!q) return this.prescriptions;

      return this.prescriptions.filter((row) => {
        const p = row && row.patient ? row.patient : {};
        const full = ((p.first_name || "") + " " + (p.last_name || "")).trim().toLowerCase();
        return full.includes(q);
      });
    },
    totalPages() {
      const total = this.filteredPrescriptions.length;
      return Math.max(1, Math.ceil(total / this.pageSize));
    },
    pagedPrescriptions() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredPrescriptions.slice(start, start + this.pageSize);
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
      if (!this.filteredPrescriptions.length) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },
    endRow() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.filteredPrescriptions.length);
    },
  },
  methods: {
    loadPrescriptions() {
      this.loading = true;
      const url = this.$base_url + "prescriptions";
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          const payload = (data && data.data) || {};
          this.prescriptions = payload.data || [];
          this.currentPage = 1; // reset to first page
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          const msg =
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            error.toString();
          this.$swal("Error", msg, "error");
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
      this.currentPage = 1; // reset page when searching
    },

    // rows-per-page
    onPageSizeChange() {
      this.currentPage = 1; // reset to page 1 when page size changes
    },

    // actions
    viewPrescription(item) {
      this.$router.push({ name: "prescriptionpage", params: { prescription: item.id } });
    },
    makePayment(item) {
      this.$router.push({ name: "sales", params: { prescription: item.id } });
    },

    // display helpers
    fullName(p) {
      if (!p) return "—";
      const f = p.first_name || "";
      const l = p.last_name || "";
      const n = (f + " " + l).trim();
      return n || "—";
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    truncate(v, n) {
      if (!v) return "—";
      if (v.length <= n) return v;
      return v.substr(0, n) + "…";
    },
    dateTime(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleString("en-AU", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });
    },
    statusClass(status) {
      const s = (status || "").toLowerCase();
      if (s === "paid" || s === "completed") return "bg-success";
      if (s === "pending") return "bg-warning";
      if (s === "failed" || s === "cancelled" || s === "canceled") return "bg-danger";
      return "bg-secondary";
    },
  },
  created() {
    this.loadPrescriptions();
  },
};
</script>

<style scoped>
/* Toolbar padding + layout */
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
.table td, .table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}
</style>
