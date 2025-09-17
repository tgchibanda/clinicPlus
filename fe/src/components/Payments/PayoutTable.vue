<template>
  <div>
    <Widget
      bodyClass="widget-table-overflow"
      title="Process Payout"
      :fetchingData="this.loading"
    >
      <b-button
        type="submit"
        :disabled="disable"
        variant="success"
        @click="approvePayout"
        ><i class="fa fa-cogs" aria-hidden="true"></i> Click To Approve Process
        Payout</b-button
      >
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <!-- <th>
                    <label class="form-checkbox">
                      <input type="checkbox" v-model="selectAll" @click="select" />
                      Select All
                    </label>
                  </th> -->
              <th>Fullname</th>
              <th>Email</th>
              <th>Order Number</th>
              <th>Status</th>
              <th>Gross amount</th>
              <!-- <th>Fees</th>
                  <th>Net amount</th> -->
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in payOutDetails.data" :key="row.payment_id">
              <!-- <td>
                    <label class="form-checkbox">
                      <input
                        type="checkbox"
                        :value="row.payment_id"
                        v-model="selected"
                      />
                      <i class="form-icon"></i>
                    </label>
                  </td> -->
              <td>{{ row.name }}</td>
              <td>{{ row.email }}</td>
              <td>{{ row.order_number }}</td>

              <td>
                <b-badge
                  :variant="
                    row.status == 1
                      ? 'danger'
                      : row.status == 2
                      ? 'success'
                      : 'info'
                  "
                  pill
                >
                  {{ row.status == "paid" ? "Pending Payout" : "Paid Out" }}
                </b-badge>
              </td>
              <td>{{ row.amount }}</td>
              <td>{{ row.created_at | moment("YYYY-MM-DD h:mm:ss a") }}</td>
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
  name: "PayoutTable",

  data() {
    return {
      errorMessage: null,
      selected: [],
      selectAll: false,
      loading: false,
      disable: true,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      payOutDetails: {},

      timer: "",
      user_role: userRole(),
      isLoading: false,
    };
  },
  methods: {
    loadPayments() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "payouts", authHeader())
        .then(({ data }) => {
          this.payOutDetails = data;
          this.loading = false;
          this.disable = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    approvePayout() {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, go for it!",
      }).then((result) => {
        if (result.value) {
          this.$axios
            .post(
              this.$base_url + "payouts",
              { payouts: this.payOutDetails.data },
              authHeader()
            )
            .then((response) => {
              this.$swal("Success!", response.message, "success");
              Fire.$emit("approvePayout");
              this.loadPayments();
              this.loadPayouts();
            })
            .catch((error) => {
              this.message =
                (error.response &&
                  error.response.data &&
                  error.response.data.message) ||
                error.message ||
                error.toString();
              this.errorMessage = this.message;
              this.errors = error.response.data.errors;
            });
        }
      });
    },
  },
  created() {
    this.loadPayments();
    // this.timer = setInterval(this.loadConsultations, 3000);
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
