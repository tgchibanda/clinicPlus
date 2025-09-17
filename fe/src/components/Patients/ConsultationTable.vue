<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />
    <b-modal id="modal-map" size="lg" title="Patient Location">
      <!-- <book-consultation></book-consultation> -->
      <!-- <patient-details></patient-details> -->
    </b-modal>

    <Widget
      title="<h5><span class='fw-semi-bold'>Consultations</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="this.loading"
    >
      <!-- <location-picker></location-picker> -->

      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Date</th>
              <th>Fullname</th>
              <th>Email</th>
              <th>Cellnumber</th>
              <th>City</th>
              <th>status</th>
              <th>Booked By</th>
              <th>View</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in consultationDetails.data" :key="row.a">
              <td>{{ row.c_date | moment("YYYY-MM-DD h:mm:ss a") }}</td>
              <td>{{ row.fullname }}</td>
              <td>{{ row.email }}</td>
              <td>{{ row.mobile_no }}</td>
              <td>{{ row.city }}</td>

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
                  {{ row.status_text }}
                </b-badge>
              </td>
              <td>{{ row.name }}</td>
              <td>
                <b-button
                  v-if="row.status == 0 && user_role.role == 'user'"
                  variant="success"
                  @click="makePayment(row)"
                  ><i class="fa fa-dollar" /> Make payment</b-button
                >
                <b-button v-else variant="primary" @click="viewDetails(row)"
                  ><span class="fa fa-search-plus" /> View Details</b-button
                >
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
import LocationPicker from "../General/LocationPicker.vue";

export default {
  components: { LocationPicker },
  name: "ConsultationTable",
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
      isLoading: false,
    };
  },
  methods: {
    loadConsultations() {
      var userQueryString = "";
      if (this.user_role.role == "user") {
        userQueryString = "user/" + this.user_id;
      } else if (this.user_role.role == "doctor") {
        userQueryString = "doctor/" + this.user_id;
      } else if (this.user_role.role == "admin") {
        userQueryString = "admin/" + this.user_id;
      }

      this.loading = true;
      this.$axios
        .get(
          this.$base_url + "consultation_details/" + userQueryString,
          authHeader()
        )
        .then(({ data }) => {
          this.consultationDetails = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    cancelAutoUpdate() {
      clearInterval(this.timer);
    },
    viewDetails(item) {
      this.$router.push({
        name: "patientdetails",
        params: { patient: item.a },
      });
    },
    makePayment(item) {
      this.isLoading = true;
      this.payment.patient_id = item.id;
      this.$axios
        .post(this.$base_url + "paypal", this.payment, authHeader())
        .then((response) => {
          let url = response.data.url;
          this.isLoading = false;
          window.location.href = url;
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
          this.isLoading = false;
          this.$swal("Error!!!", this.errorMessage, "error");
        });
    },
  },
  created() {
    this.loadConsultations();
    // this.timer = setInterval(this.loadConsultations, 3000);
  },
  beforeDestroy() {
    clearInterval(this.timer);
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
