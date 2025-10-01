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
      title="<h5>My <span class='fw-semi-bold'>Insurance</span></h5>"
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
            placeholder="Search by owner name (first or last)…"
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
          <template v-if="filteredInsurance.length">
            Showing {{ startRow }}–{{ endRow }} of {{ filteredInsurance.length }} result(s)
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
              <th>Owner</th>
              <th>Contact</th>
              <th>Dependents</th>
              <th>Plan</th>
              <th>Monthly</th>
              <th>Status</th>
              <th>Joined</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedInsurance" :key="row.id">
              <td>{{ row.id }}</td>

              <td>
                <div class="font-weight-600">{{ ownerName(row) }}</div>
                <div class="small text-muted">{{ ownerAgeText(row.patient) }}</div>
              </td>

              <td>
                <div class="small text-muted" v-if="row.patient && row.patient.email">{{ row.patient.email }}</div>
                <div class="small text-muted" v-if="row.patient && row.patient.phone">{{ row.patient.phone }}</div>
                <div v-if="!row.patient" class="text-muted">—</div>
              </td>

              <td>
                <span class="badge bg-secondary">{{ dependentsCount(row) }}</span>
              </td>

              <td>
                <div>{{ (row.plan && row.plan.name) || '—' }}</div>
              </td>

              <td>
                <strong>{{ money(monthlyFromSubscription(row)) }}</strong>
              </td>

              <td>
                <span class="badge" :class="statusClass(row.status)">
                  {{ capFirst(row.status || '') || '—' }}
                </span>
              </td>

              <td>{{ formatDate(dateTime(row.created_at)) }}</td>

              <td class="text-right">
                <b-button size="sm" variant="primary" class="mr-2" @click="viewInsurance(row)">
                  <span class="fa fa-eye" /> View
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedInsurance.length && !loading">
              <td colspan="9" class="text-center text-muted">No insurance subscriptions found.</td>
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
import Widget from "@/components/Widget/Widget";

export default {
  name: "InsuranceTable",
  components: { Widget },
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,

      // raw list pulled from API (we'll paginate client-side)
      insurance: [],

      // search + pagination (client-side)
      searchInput: "",
      currentPage: 1,
      pageSize: 5,
      pageSizeOptions: [5, 10, 15, 20],
    };
  },
  computed: {
    // filter by owner first/last name (row.patient)
    filteredInsurance() {
      const q = (this.searchInput || "").trim().toLowerCase();
      if (!q) return this.insurance;

      return this.insurance.filter((row) => {
        const p = row && row.patient ? row.patient : {};
        const full = ((p.first_name || "") + " " + (p.last_name || "")).trim().toLowerCase();
        return full.includes(q);
      });
    },
    totalPages() {
      const total = this.filteredInsurance.length;
      return Math.max(1, Math.ceil(total / this.pageSize));
    },
    pagedInsurance() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredInsurance.slice(start, start + this.pageSize);
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
      if (!this.filteredInsurance.length) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },
    endRow() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.filteredInsurance.length);
    },
  },
  methods: {
    loadInsurance() {
      this.loading = true;
      // endpoint for subscriptions (user earlier had /api/subscriptions)
      const url = (this.$base_url ? this.$base_url : "") + "subscriptions";
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          // handle multiple possible shapes
          // priority: data.subscriptions.data (paginated) -> data.data -> data
          let list = [];
          if (data && data.subscriptions && Array.isArray(data.subscriptions.data)) {
            list = data.subscriptions.data;
          } else if (data && Array.isArray(data.data)) {
            list = data.data;
          } else if (Array.isArray(data)) {
            list = data;
          } else if (data && data.subscriptions && Array.isArray(data.subscriptions)) {
            list = data.subscriptions;
          } else if (data && data.data && data.data.data && Array.isArray(data.data.data)) {
            // double-nested paginator
            list = data.data.data;
          } else {
            // last resort: attempt to find array inside response
            const maybe = Object.values(data).find((v) => Array.isArray(v));
            if (Array.isArray(maybe)) list = maybe;
          }

          this.insurance = list;
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
formatDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      if (isNaN(d.getTime())) return val; // fallback if invalid
      return d.toLocaleDateString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric"
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
    viewInsurance(item) {
      // route to details page (change route name if different)
      this.$router.push({ name: "insurancepage", params: { insurance: item.id } });
    },
    makePayment(item) {
      this.$router.push({ name: "sales", params: { insurance: item.id } });
    },

    // display helpers
    fullName(p) {
      if (!p) return "—";
      const f = p.first_name || "";
      const l = p.last_name || "";
      const n = (f + " " + l).trim();
      return n || "—";
    },
    ownerName(row) {
      // owner is row.patient
      return this.fullName(row.patient);
    },
    ownerAgeText(p) {
      if (!p || !p.date_of_birth) return "";
      const d = new Date(p.date_of_birth);
      if (isNaN(d.getTime())) return "";
      const age = new Date().getFullYear() - d.getFullYear();
      return `DOB: ${d.toLocaleDateString()} • Age: ${age}`;
    },
    dependentsCount(row) {
      if (!row) return 0;
      if (Array.isArray(row.dependents)) return row.dependents.length;
      if (typeof row.dependents_count !== "undefined") return row.dependents_count;
      return 0;
    },
    // compute monthly total from owner + dependents using plan prices
    monthlyFromSubscription(row) {
      try {
        let total = 0;
        // owner price (use row.plan for owner's selected plan)
        if (row.plan && row.patient && row.patient.date_of_birth) {
          const ownerAge = this.ageFromDate(row.patient.date_of_birth);
          const ownerPrice = ownerAge >= 18 ? Number(row.plan.price_adult || 0) : Number(row.plan.price_child || 0);
          total += ownerPrice;
        } else if (row.plan) {
          // fallback: assume adult
          total += Number(row.plan.price_adult || 0);
        }

        // dependents: each dependent has plan (or plan_id)
        if (Array.isArray(row.dependents)) {
          row.dependents.forEach((d) => {
            // dependent's plan might be nested in d.plan
            const p = d.plan || (d.plan_id ? row.plan /* fallback: same plan */ : null);
            if (p) {
              const age = d.date_of_birth ? this.ageFromDate(d.date_of_birth) : 18;
              total += age >= 18 ? Number(p.price_adult || 0) : Number(p.price_child || 0);
            }
          });
        }

        return Number(total || 0);
      } catch (e) {
        return Number(row.monthly_total || 0);
      }
    },
    ageFromDate(dob) {
      if (!dob) return 0;
      const b = new Date(dob);
      const now = new Date();
      let age = now.getFullYear() - b.getFullYear();
      const m = now.getMonth() - b.getMonth();
      if (m < 0 || (m === 0 && now.getDate() < b.getDate())) age--;
      return age;
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
    money(v) {
      const n = Number(v || 0);
      return isNaN(n) ? "$0.00" : `$${n.toFixed(2)}`;
    },
    statusClass(status) {
      const s = (status || "").toString().toLowerCase();
      if (s === "active" || s === "paid" || s === "completed") return "bg-success";
      if (s === "pending" || s === "due") return "bg-warning";
      if (s === "lapsed" || s === "closed" || s === "cancelled") return "bg-danger";
      return "bg-secondary";
    },
  },
  created() {
    this.loadInsurance();
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
.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}
</style>
