<template>
  <div>
    <b-modal
      id="modal-payout"
      size="lg"
      title="Payout Batch Details"
      ref="modal-payout"
      hide-footer
    >
      <Widget
        bodyClass="widget-table-overflow"
        :fetchingData="this.loading"
      >
        <export-excel
          class="btn btn-primary"
          :data="csvData"
          worksheet="My Worksheet"
          name="Approved Payouts.xls"
        >
          Download Excel and Remit Funds
        </export-excel>
        <div class="table-responsive">
          <table class="table table-striped table-lg mb-0 requests-table">
            <thead>
              <tr class="text-muted">
                <th>Batch</th>
                <th>Name</th>
                <th>Email</th>
                <th>Gross</th>
                <th>Fees</th>
                <th>Net</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in exportPayOuts.data" :key="row.id">
                <td>{{ row.id }}</td>
                <td>{{ row.name }}</td>
                <td>{{ row.email }}</td>
                <td>{{ row.sum_amount_gross }}</td>
                <td>{{ row.sum_fees }}</td>
                <td>{{ row.sum_amount_net }}</td>

              </tr>
            </tbody>
          </table>
        </div>
      </Widget>
    </b-modal>
    <Widget
      bodyClass="widget-table-overflow"
      title="Approved Payouts"
      :fetchingData="this.loading"
    >
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Batch</th>
              <th>Gross</th>
              <th>Fees</th>
              <th>Net</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in payOuts.data" :key="row.id">
              <td>{{ row.id }}</td>
              <td>{{ row.amount_gross }}</td>
              <td>{{ row.fees }}</td>
              <td>{{ row.amount_net }}</td>

              <td>{{ row.created_at | moment("YYYY-MM-DD h:mm:ss a") }}</td>
              <td>
                <b-button variant="primary" @click="showPayoutModal(row)"
                  ><span class="fa fa-search-plus" />
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
import userRole from "../../services/user-role";
export default {
  name: "ProcessPayoutTable",

  data() {
    return {
      errorMessage: null,
      selected: [],
      selectAll: false,
      loading: false,
      disable: true,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      payOuts: {},
      exportPayOuts: [],
      csvData: [],
      user_role: userRole(),
      isLoading: false,
      json_fields: {},
    };
  },
  methods: {
    loadPayouts() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "getPayouts", authHeader())
        .then(({ data }) => {
          this.payOuts = data;
          this.loading = false;
          this.disable = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    getPayouts(id) {
      this.loading = true;
      this.$axios
        .get(`${this.$base_url}payouts/${id}`, authHeader())
        .then(({ data }) => {
          this.exportPayOuts = data;
          this.csvData = data.data;
          this.loading = false;
          this.disable = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    showPayoutModal(item) {
      this.$refs["modal-payout"].show();
      this.getPayouts(item.id);
    },
  },
  created() {
    this.loadPayouts();
    Fire.$on("approvePayout", () => {
      this.loadPayouts();
    });
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
.month-picker__container {
  background-color: #dddddd !important;
}
</style>
