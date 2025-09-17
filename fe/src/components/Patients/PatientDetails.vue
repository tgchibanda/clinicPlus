<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <b-form-group label="Fullname *">
        <b-form-input
          type="text"
          v-model="user.fullname"
          placeholder="Enter Fullname"
          :state="'fullname' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "fullname" in errors ? errors.fullname[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Email">
        <b-form-input
          type="email"
          v-model="user.email"
          placeholder="Enter email"
          :state="'email' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "email" in errors ? errors.email[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Mobile *">
        <b-form-input
          type="number"
          v-model="user.mobile_no"
          placeholder="Enter mobile no"
          :state="'mobile_no' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "mobile_no" in errors ? errors.mobile_no[0] : true }}
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

      <b-form-group label="Address Line 1 *">
        <b-form-input
          v-model="user.address_line1"
          type="text"
          placeholder="Enter Address Line 1"
          :state="'address_line1' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "address_line1" in errors ? errors.address_line1[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Address Line 2 *">
        <b-form-input
          v-model="user.address_line2"
          type="text"
          placeholder="Enter Address Line 2"
          :state="'address_line2' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "address_line2" in errors ? errors.address_line2[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Address Line 3">
        <b-form-input
          v-model="user.address_line3"
          type="text"
          placeholder="Enter Street Name"
          :state="'address_line3' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "address_line3" in errors ? errors.surburb[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- <b-form-group label="GPS">
        <b-form-input
          v-model="user.gps"
          type="text"
          placeholder="Enter GPS -33.80672617589333, 18.48931609383872"
          :state="'gps' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "gps" in errors ? errors.gps[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group> -->
      <b-form-group label="City">
        <b-form-select
          v-model="user.city"
          :state="'city' in errors ? false : null"
        >
          <option value="">Choose City</option>
          <option value="Harare">Harare</option>
          <option value="Chitungwiza">Chitungwiza</option>
          <option value="Mutare">Mutare</option>
          <option value="Bulawayo">Bulawayo</option>
          <option value="Gweru">Gweru</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ "city" in errors ? errors.city[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Contact Person">
        <b-form-input
          v-model="user.contact_person"
          type="text"
          placeholder="Enter contact person"
          :state="'contact_person' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "contact_person" in errors ? errors.contact_person[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Contact Person mobile no">
        <b-form-input
          v-model="user.mobile_no_contact_person"
          type="number"
          placeholder="Enter contact person mobile no"
          :state="'mobile_no_contact_person' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{
            "mobile_no_contact_person" in errors
              ? errors.mobile_no_contact_person[0]
              : true
          }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-button block type="submit" variant="primary">Submit</b-button>
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
      user: {
        fullname: "",
        email: "",
        mobile_no: "",
        address_line1: "",
        address_line2: "",
        address_line3: "",
        gps: "111",
        city: "",
        dob: "",
        gender: "",
        mobile_no_contact_person: "",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "patient_details", this.user, authHeader())
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
