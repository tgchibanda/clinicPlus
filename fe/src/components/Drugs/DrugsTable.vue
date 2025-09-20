<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />

    <b-modal
      id="modal-consultation"
      ref="modal-consultation"
      size="lg"
      title="Book Consultation"
      hide-footer
    >
      <book-consultation :selectedPatient="selectedPatient"></book-consultation>
    </b-modal>

    <b-modal id="modal-map" size="lg" title="Patient Location" hide-footer />

    <b-modal
      id="modal-new-walk-in-patient"
      ref="modal-new-walk-in-patient"
      size="lg"
      title="New Walk In Patient"
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
        v-b-modal.modal-new-walk-in-patient
        variant="primary"
        class="modal-button"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        Add New Drug
      </b-button>

      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Unit</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in drugDetails" :key="row.id">
              <td>{{ row.name }}</td>
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
                <b-button variant="primary" @click="viewDetails(row)">
                  <span class="fa fa-search-plus" /> View Details
                </b-button>
                <b-button variant="primary" @click="editDetails(row)">
                  <span class="fa fa-edit" /> Edit Details
                </b-button>
              </td>
            </tr>
          </tbody>
        </table>
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
      drugDetails: [],       // array of drugs
      pagination: {},        // paginator object
      lowStockCount: 0,      // number
      selectedPatient: null, // used by consultation modal
    };
  },
  methods: {
    loadDrugs(pageUrl = null) {
      this.loading = true;
      const url = pageUrl || (this.$base_url + "drugs");
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          this.drugDetails   = data.data.drugs.data;     // array
          this.pagination    = data.data.drugs;           // paginator
          this.lowStockCount = data.data.low_stock_count; // number
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          this.$swal("error!", "There was an error: " + error, "error");
        });
    },
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
    viewDetails(item) {
      this.$router.push({ name: "drugdetailspage", params: { drug: item.id } });
    },
    editDetails(item) {
      // open your edit modal or route
      this.$router.push({ name: "drugeditpage", params: { drug: item.id } });
    },
    closeDrugsModal() {
      this.$refs["modal-new-walk-in-patient"].hide();
      this.loadDrugs();
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
</style>
