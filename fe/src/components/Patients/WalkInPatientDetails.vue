<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <b-form-group label="First Name *">
        <b-form-input
          type="text"
          v-model="user.first_name"
          placeholder="Enter First Name"
          :state="'first_name' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "first_name" in errors ? errors.first_name[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Last Name *">
        <b-form-input
          type="text"
          v-model="user.last_name"
          placeholder="Enter Last Name"
          :state="'last_name' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "last_name" in errors ? errors.last_name[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Email Address">
        <b-form-input
          type="email"
          v-model="user.email"
          placeholder="Enter email address"
          :state="'email' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "email" in errors ? errors.email[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Phone *">
        <b-form-input
          type="number"
          v-model="user.phone_number"
          placeholder="Enter phone number"
          :state="'phone_number' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "phone_number" in errors ? errors.phone_number[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Date of birth *">
        <b-form-datepicker
          id="example-datepicker"
          v-model="user.dob"
          class="mb-2"
          placeholder="Enter date of birth"
          :state="'dob' in errors ? false : null"
        ></b-form-datepicker>
        <b-form-invalid-feedback>
          {{ "dob" in errors ? errors.dob[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Gender *">
        <b-form-select
          v-model="user.gender"
          :state="'gender' in errors ? false : null"
        >
          <option value="" selected>Choose gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ "gender" in errors ? errors.gender[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Address *">
        <b-form-input
          v-model="user.address"
          type="text"
          placeholder="Enter Address"
          :state="'address' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "address" in errors ? errors.address[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Emergency contact number *">
        <b-form-input
          v-model="user.emergency_contact_number"
          type="text"
          placeholder="Enter emergency contact number"
          :state="'emergency_contact_number' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "emergency_contact_number" in errors ? errors.emergency_contact_number[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Payment method *">
        <b-form-select
          v-model="user.payment_method"
          :state="'payment_method' in errors ? false : null"
        >
          <option value="" selected>Choose payment method</option>
          <option value="Cash">Cash</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ "payment_method" in errors ? errors.payment_method[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-button block type="submit" variant="primary">Submit</b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "WalkInPatientDetails",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user: {
        first_name: "",
        last_name: "",
        email: "",
        phone_number: "",
        address: "",
        payment_method: "",
        emergency_contact_number: "",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "walk_in_patient_details", this.user, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          Fire.$emit("closeModalPatient");
          this.resetForm();
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
