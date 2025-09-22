<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />

    <!-- Keep your existing modals -->
    <b-modal id="modal-map" size="lg" title="Drug Location" hide-footer />

    <b-modal
      id="modal-new-drug"
      ref="modal-new-drug"
      size="lg"
      title="New Drug"
      hide-footer
    >
      <drug-details @saved="closeDrugsModal" />
    </b-modal>

    <Widget
      title="<h5>My <span class='fw-semi-bold'>Drugs</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="loading"
    >
      <b-button
        v-b-modal.modal-new-drug
        variant="primary"
        class="modal-button"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        Add New Drug
      </b-button>

      <!-- Toolbar (matches your prescriptions page) -->
      <div class="toolbar d-flex flex-wrap align-items-center mb-3">
        <!-- Search by drug name -->
        <b-input-group class="mr-3 mb-2 search-box">
          <b-form-input
            v-model.trim="searchInput"
            placeholder="Search by drug name…"
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
          <template v-if="filteredDrugs.length">
            Showing {{ startRow }}–{{ endRow }} of {{ filteredDrugs.length }} result(s)
          </template>
          <template v-else>
            No results
          </template>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Name</th>
              <th>Batch#</th>
              <th>Description</th>
              <th width="8%">Expiry</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Unit</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedDrugs" :key="row.id">
              <td>{{ row.name }}</td>
              <td>{{ row.batch_number }}</td>
              <td>{{ row.description }}</td>
              <td>{{ formatDate(row.expiry_date) }}</td>
              <td>{{ row.category }}</td>
              <td>{{ formatMoney(row.selling_price) }}</td>

              <td>
                <span class="badge" :class="isLowStock(row) ? 'bg-danger' : 'bg-success'">
                  {{ row.stock_quantity }}
                </span>
              </td>

              <td>{{ row.unit }}</td>

              <td>
                <span v-if="isExpired(row)" class="badge bg-danger">Expired</span>
                <span v-else-if="isLowStock(row)" class="badge bg-warning">Low Stock</span>
                <span v-else class="badge bg-success">Available</span>
              </td>

              <td>
                <b-button variant="primary" size="sm" class="mr-2" @click="addStock(row)">
                  <span class="fa fa-search-plus" /> Add
                </b-button>
                </td>
                <td>
                <b-button variant="primary" size="sm" @click="editDetails(row)">
                  <span class="fa fa-edit" /> Edit
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedDrugs.length && !loading">
              <td colspan="7" class="text-center text-muted">No drugs found.</td>
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
  name: "DrugsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,

      // flat list for client-side pagination/search
      drugs: [],

      // search + pagination
      searchInput: "",
      currentPage: 1,
      pageSize: 5,
      pageSizeOptions: [5, 10, 15, 20],

      // you still reference these in modals/actions
      selectedDrug: null,
    };
  },
  computed: {
    filteredDrugs() {
      const q = (this.searchInput || "").toLowerCase().trim();
      if (!q) return this.drugs;
      return this.drugs.filter(d => (d.name || "").toLowerCase().includes(q));
    },
    totalPages() {
      const total = this.filteredDrugs.length;
      return Math.max(1, Math.ceil(total / this.pageSize));
    },
    pagedDrugs() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredDrugs.slice(start, start + this.pageSize);
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
      if (!this.filteredDrugs.length) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },
    endRow() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.filteredDrugs.length);
    },
  },
  methods: {
    loadDrugs(pageUrl = null) {
      this.loading = true;
      const url = pageUrl || (this.$base_url + "drugs");
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          // Normalize the payload into a simple array
          // Expected: data.data.drugs.data (array)
          const list = data?.data?.drugs?.data || [];
          this.drugs = list.map(d => ({
            id: d.id,
            name: d.name,
            category: d.category,
            batch_number: d.batch_number,
            selling_price: d.selling_price,
            description: d.description,
            stock_quantity: Number(d.stock_quantity || 0),
            minimum_stock_level: Number(d.minimum_stock_level || 0),
            unit: d.unit,
            expiry_date: d.expiry_date,
          }));
          this.currentPage = 1;
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          this.$swal("error!", "There was an error: " + (error?.message || error), "error");
        });
    },
    // status helpers
    isLowStock(row) {
      return Number(row.stock_quantity) <= Number(row.minimum_stock_level);
    },
    isExpired(row) {
      if (!row.expiry_date) return false;
      return new Date(row.expiry_date) < new Date();
    },
    formatMoney(value) {
      const n = Number(value);
      if (Number.isNaN(n)) return `$${value}`;
      return `$${n.toFixed(2)}`;
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
    // actions
    addStock(item) {
      this.$router.push({ name: "drugdetailspage", params: { drug: item.id } });
    },
    editDetails(item) {
      this.$router.push({ name: "drugeditpage", params: { drug: item.id } });
    },
    closeDrugsModal() {
      this.$refs["modal-new-drug"].hide();
      this.loadDrugs();
    },

    // toolbar events
    applySearch() {
      this.currentPage = 1;
    },
    onPageSizeChange() {
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
  },
  created() {
    this.loadDrugs();
  },
};
</script>

<style scoped>
.modal-button {
  position: absolute;
  z-index: 1;
  top: 4px;
  right: 4px;
  font-size: 0.875rem;
}

/* Match prescriptions page styling */
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

.pagination-bar {
  padding: 12px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}
</style>
