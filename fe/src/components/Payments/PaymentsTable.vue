<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />
    <b-modal id="modal-map" size="lg" title="Patient Location"> </b-modal>
    <Widget
      title="<h5>My <span class='fw-semi-bold'>Payments</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="this.loading"
    >
      <div class="m-2"></div>
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Fullname</th>
              <th>Email</th>
              <th>Cellnumber</th>
              <th>Order Number</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Date</th>
              <!-- <th>View</th> -->
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in consultationDetails.data" :key="row.payment_id">
              <td>{{ row.fullname }}</td>
              <td>{{ row.email }}</td>
              <td>{{ row.mobile_no }}</td>
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
                  {{ row.status }}
                </b-badge>
              </td>
              <td>{{ row.amount }}</td>
              <td>{{ row.created_at | moment("YYYY-MM-DD h:mm:ss a") }}</td>

              <!-- <td>
                <b-button variant="primary" @click="viewDetails(row)"
                  ><span class="fa fa-search-plus" /> View Details</b-button
                >
              </td> -->
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
import { MonthPickerInput } from "vue-month-picker";
export default {
  name: "PaymentsTable",
  components: {
    MonthPickerInput,
  },
  data() {
    return {
      errorMessage: null,
      loading: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      consultationDetails: {},
      timer: "",
      user_role: userRole(),
      payment: {
        return_url: "https://clinicPluszimbabwe.com/#/app/payment-status",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
      date: {
        from: null,
        to: null,
        month: null,
        year: null,
      },
      isLoading: false,
    };
  },
  methods: {
    loadPayments() {
      var queryString = `payment/${this.user_id}`;
      if (this.user_role.role == "user") {
        queryString = `payment/${this.user_id}`;
      } else if (this.user_role.role == "doctor") {
        queryString = `payments_doctor/${this.user_id}`;
      } else if (this.user_role.role == "admin") {
        queryString = `payment/admin`;
      }
      this.loading = true;
      this.$axios
        .get(`${this.$base_url}${queryString}`, authHeader())
        .then(({ data }) => {
          this.consultationDetails = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    showDate(date) {
      this.date = date;
      console.log(this.date);
    },
    viewDetails(item) {
      console.log(item);
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
