<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-button block type="submit" variant="primary">Pay</b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "PatientDetails",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      payment: {
        amount: 20,
        return_url: "https://clinicPluszimbabwe.com/#/app/dashboard",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "paypal", this.payment, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          // Fire.$emit("closeModalPatient");
          // this.resetForm();
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
    resetForm() {
      this.user.fullname = "";
      this.user.email = "";
      this.user.mobile = "";
      this.user.city = "";
      this.user.unit_number = "";
      this.user.street_name = "";
      this.user.surburb = "";
      this.user.gps = "";
    },
    loadCurrentUser() {
      this.$axios
        .get(this.$base_url + "contact/" + this.user_id, authHeader())
        .then(({ data }) => {
          this.currentUser = JSON.parse(data.data);
          this.user.fullname = this.currentUser.fullname;
          this.user.email = "";
          this.user.mobile = "s";
          this.user.city = "";
          this.user.unit_number = "";
          this.user.street_name = "";
          this.user.surburb = "";
          this.user.gps = "";
        });
    },
    closeModal() {
      this.$refs["modal-consultation"].hide();
      this.loadPatients();
    },
  },
  created() {
    Fire.$on("closeModalPatient", () => {
      this.closeModal();
    });
    // this.loadCurrentUser();
  },
};
</script>
