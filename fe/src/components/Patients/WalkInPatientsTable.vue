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
      <book-consultation
        :selectedPatient="this.selectedPatient"
      ></book-consultation>
    </b-modal>
    <b-modal id="modal-map" size="lg" title="Patient Location" hide-footer>
    </b-modal>
    <b-modal
      id="modal-new-walk-in-patient"
      size="lg"
      ref="modal-new-walk-in-patient"
      title="New Walk In Patient"
      hide-footer
    >
      <walk-in-patient-details></walk-in-patient-details>
    </b-modal>
    <Widget
      title="<h5>My <span class='fw-semi-bold'>Patients</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="this.loading"
    >
      <b-button
        v-b-modal.modal-new-walk-in-patient
        variant="primary"
        class="modal-button"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
        Add New Walk In Patient</b-button
      >
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>First Name</th>
              <th>Last Name</th>
              <th>Gender</th>
              <th>Date Of Birth</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Payment Method</th>
              <th>Book</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in patientDetails.data" :key="row.id">
              <td>{{ row.first_name }}</td>
              <td>{{ row.last_name }}</td>
              <td>{{ row.gender }}</td>
              <td>{{ row.date_of_birth }}</td>
              <td>{{ row.email }}</td>
              <td>{{ row.phone }}</td>
              <td>{{ row.payment_method }}</td>
              <td>
                <b-button
                  v-b-modal.modal-consultation
                  variant="primary"
                  @click="sendInfo(row)"
                >
                  <i class="fa fa-book" aria-hidden="true"></i>
                  Book Consultation</b-button
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
export default {
  name: "WalkInPatientsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,
      payment: {
        return_url: "https://clinicPluszimbabwe.com/#/app/payment-status",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      patientDetails: {},
      selectedPatient: "",
    };
  },
  methods: {
    loadWalkInPatients() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "walk_in_patient_details", authHeader())
        .then(({ data }) => {
          this.patientDetails = data.data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
    sendInfo(item) {
      this.selectedPatient = item;
    },
    gotoPayment(consultation_id) {
      this.isLoading = true;
      this.payment.patient_id = this.selectedPatient.id;
      this.payment.consultation_id = consultation_id;
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
          // this.$swal("error!", "There was an error" + error, "error");
        });
    },

    closeConsultationModal(consultation_id) {
      this.$refs["modal-consultation"].hide();
      this.gotoPayment(consultation_id);
    },
    closePatientsModal() {
      this.$refs["modal-new-walk-in-patient"].hide();
      this.loadWalkInPatients();
    },
  },
  created() {
    Fire.$on("closeModalConsultation", (data) => {
      this.closeConsultationModal(data.data.data.id);
    });
    Fire.$on("closeModalPatient", () => {
      this.closePatientsModal();
    });
    this.loadWalkInPatients();
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
