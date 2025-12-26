<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />

    <!-- Edit Drug Modal -->
    <b-modal
      id="modal-edit-drug"
      ref="modal-edit-drug"
      size="lg"
      title="Edit Drug"
      hide-footer
    >
      <b-form @submit.prevent="updateDrug">
        <b-row>
          <b-col md="6">
            <b-form-group label="Drug Name *" label-for="edit-name">
              <b-form-input
                id="edit-name"
                v-model="editForm.name"
                required
                placeholder="Enter drug name"
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Batch Number *" label-for="edit-batch">
              <b-form-input
                id="edit-batch"
                v-model="editForm.batch_number"
                required
                placeholder="Enter batch number"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col md="6">
            <b-form-group label="Category" label-for="edit-category">
              <b-form-input
                id="edit-category"
                v-model="editForm.category"
                placeholder="Enter category"
              />
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group label="Unit *" label-for="edit-unit">
              <b-form-input
                id="edit-unit"
                v-model="editForm.unit"
                required
                placeholder="e.g., tablets, ml, boxes"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <b-form-group label="Description" label-for="edit-description">
          <b-form-textarea
            id="edit-description"
            v-model="editForm.description"
            rows="3"
            placeholder="Enter drug description"
          />
        </b-form-group>

        <b-row>
          <b-col md="4">
            <b-form-group label="Selling Price *" label-for="edit-price">
              <b-form-input
                id="edit-price"
                v-model.number="editForm.selling_price"
                type="number"
                step="0.01"
                min="0"
                required
                placeholder="0.00"
              />
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group label="Stock Quantity *" label-for="edit-stock">
              <b-form-input
                id="edit-stock"
                v-model.number="editForm.stock_quantity"
                type="number"
                min="0"
                required
                placeholder="0"
              />
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group label="Min Stock Level *" label-for="edit-min-stock">
              <b-form-input
                id="edit-min-stock"
                v-model.number="editForm.minimum_stock_level"
                type="number"
                min="0"
                required
                placeholder="0"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <b-form-group label="Expiry Date" label-for="edit-expiry">
          <b-form-input
            id="edit-expiry"
            v-model="editForm.expiry_date"
            type="date"
          />
        </b-form-group>

        <div class="d-flex justify-content-end">
          <b-button variant="secondary" class="mr-2" @click="closeEditModal">
            Cancel
          </b-button>
          <b-button variant="primary" type="submit" :disabled="editLoading">
            <span v-if="editLoading">
              <i class="fa fa-spinner fa-spin mr-1" /> Updating...
            </span>
            <span v-else>
              <i class="fa fa-save mr-1" /> Update Drug
            </span>
          </b-button>
        </div>
      </b-form>
    </b-modal>

    <!-- Add Stock Modal -->
    <b-modal
      id="modal-add-stock"
      ref="modal-add-stock"
      size="md"
      title="Add Stock"
      hide-footer
    >
      <div v-if="selectedDrug" class="mb-3">
        <h6>{{ selectedDrug.name }}</h6>
        <p class="text-muted mb-1">
          Current Stock: <strong>{{ selectedDrug.stock_quantity }} {{ selectedDrug.unit }}</strong>
        </p>
      </div>

      <b-form @submit.prevent="submitAddStock">
        <b-form-group label="Quantity to Add *" label-for="add-quantity">
          <b-form-input
            id="add-quantity"
            v-model.number="stockForm.quantity"
            type="number"
            min="1"
            required
            placeholder="Enter quantity to add"
          />
        </b-form-group>

        <b-form-group label="Notes (Optional)" label-for="add-notes">
          <b-form-textarea
            id="add-notes"
            v-model="stockForm.notes"
            rows="3"
            placeholder="e.g., Supplier name, invoice number, etc."
          />
        </b-form-group>

        <div class="d-flex justify-content-end">
          <b-button variant="secondary" class="mr-2" @click="closeStockModal">
            Cancel
          </b-button>
          <b-button variant="success" type="submit" :disabled="stockLoading">
            <span v-if="stockLoading">
              <i class="fa fa-spinner fa-spin mr-1" /> Adding...
            </span>
            <span v-else>
              <i class="fa fa-plus mr-1" /> Add Stock
            </span>
          </b-button>
        </div>
      </b-form>
    </b-modal>

    <!-- New Drug Modal -->
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

      <!-- Toolbar -->
      <div class="toolbar d-flex flex-wrap align-items-center mb-3">
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

        <div class="d-flex align-items-center mb-2 page-size">
          <label class="mb-0 mr-2 text-muted small">Rows per page</label>
          <b-form-select
            v-model.number="pageSize"
            :options="pageSizeOptions"
            class="page-size-select"
            @change="onPageSizeChange"
          />
        </div>

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
                <b-button 
                  variant="success" 
                  size="sm" 
                  class="mr-2" 
                  @click="openAddStockModal(row)"
                >
                  <span class="fa fa-plus" /> Add More Stock
                </b-button>
                <b-button 
                  variant="primary" 
                  size="sm"
                  class="mr-2"
                  @click="openEditModal(row)"
                >
                  <span class="fa fa-edit" /> Edit
                </b-button>
                <b-button 
                  variant="danger" 
                  size="sm"
                  @click="confirmDelete(row)"
                >
                  <span class="fa fa-trash" /> Delete
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedDrugs.length && !loading">
              <td colspan="10" class="text-center text-muted">No drugs found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
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

      drugs: [],
      selectedDrug: null,

      // Edit form
      editForm: {
        name: "",
        batch_number: "",
        description: "",
        category: "",
        selling_price: 0,
        stock_quantity: 0,
        minimum_stock_level: 0,
        unit: "",
        expiry_date: "",
      },
      editLoading: false,

      // Stock form
      stockForm: {
        quantity: null,
        notes: "",
      },
      stockLoading: false,

      // Search + pagination
      searchInput: "",
      currentPage: 1,
      pageSize: 5,
      pageSizeOptions: [5, 10, 15, 20],
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
    loadDrugs() {
      this.loading = true;
      const url = this.$base_url + "drugs";
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
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

    // Edit Modal
    openEditModal(drug) {
      this.selectedDrug = drug;
      this.editForm = {
        name: drug.name,
        batch_number: drug.batch_number,
        description: drug.description || "",
        category: drug.category || "",
        selling_price: drug.selling_price,
        stock_quantity: drug.stock_quantity,
        minimum_stock_level: drug.minimum_stock_level,
        unit: drug.unit,
        expiry_date: drug.expiry_date || "",
      };
      this.$bvModal.show("modal-edit-drug");
    },

    updateDrug() {
      this.editLoading = true;
      this.$axios
        .put(
          this.$base_url + `drugs/${this.selectedDrug.id}`,
          this.editForm,
          authHeader()
        )
        .then(({ data }) => {
          this.editLoading = false;
          this.$swal("Success!", data.message, "success");
          this.closeEditModal();
          this.loadDrugs();
        })
        .catch((error) => {
          this.editLoading = false;
          this.$swal("Error!", error?.response?.data?.message || "Update failed", "error");
        });
    },

    closeEditModal() {
      this.$bvModal.hide("modal-edit-drug");
      this.selectedDrug = null;
      this.editForm = {
        name: "",
        batch_number: "",
        description: "",
        category: "",
        selling_price: 0,
        stock_quantity: 0,
        minimum_stock_level: 0,
        unit: "",
        expiry_date: "",
      };
    },

    // Add Stock Modal
    openAddStockModal(drug) {
      this.selectedDrug = drug;
      this.stockForm = {
        user_id: this.user_id,
        quantity: null,
        notes: "",
      };
      this.$bvModal.show("modal-add-stock");
    },

    submitAddStock() {
      this.stockLoading = true;
      this.$axios
        .post(
          this.$base_url + `drugs/${this.selectedDrug.id}/add-stock`,
          this.stockForm,
          authHeader()
        )
        .then(({ data }) => {
          this.stockLoading = false;
          this.$swal("Success!", data.message, "success");
          this.closeStockModal();
          this.loadDrugs();
        })
        .catch((error) => {
          this.stockLoading = false;
          this.$swal("Error!", error?.response?.data?.message || "Failed to add stock", "error");
        });
    },

    closeStockModal() {
      this.$bvModal.hide("modal-add-stock");
      this.selectedDrug = null;
      this.stockForm = {
        quantity: null,
        notes: "",
      };
    },

    closeDrugsModal() {
      this.$refs["modal-new-drug"].hide();
      this.loadDrugs();
    },

    // Status helpers
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
      if (isNaN(d.getTime())) return val;
      return d.toLocaleDateString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    },

    // Toolbar events
    applySearch() {
      this.currentPage = 1;
    },
    onPageSizeChange() {
      this.currentPage = 1;
    },

    // Pagination
    goToPage(page) {
      if (page < 1 || page > this.totalPages) return;
      this.currentPage = page;
    },
    pageBtnVariant(p) {
      return p === this.currentPage ? "primary" : "outline-secondary";
    },

    // Delete Drug
    confirmDelete(drug) {
      this.$swal({
        title: 'Are you sure?',
        html: `Do you want to delete <strong>${drug.name}</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteDrug(drug.id);
        }
      });
    },

    deleteDrug(drugId) {
      this.isLoading = true;
      this.$axios
        .delete(this.$base_url + `drugs/${drugId}`, authHeader())
        .then(({ data }) => {
          this.isLoading = false;
          this.$swal({
            title: 'Deleted!',
            text: data.message || 'Drug has been deleted successfully.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
          this.loadDrugs();
        })
        .catch((error) => {
          this.isLoading = false;
          const errorMessage = error?.response?.data?.message || 'Failed to delete drug';
          this.$swal({
            title: 'Cannot Delete',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: 'OK'
          });
        });
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