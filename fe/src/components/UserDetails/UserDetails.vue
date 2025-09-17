<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <!-- <b-form-group label="Account Type" v-slot="{ ariaDescribedby }">
        <b-form-radio-group
          id="btn-radios-2"
          v-model="user.user_account"
          :options="options"
          :aria-describedby="ariaDescribedby"
          button-variant="outline-primary"
          size="lg"
          name="radio-btn-outline"
          buttons
        >
        </b-form-radio-group>
      </b-form-group> -->
      <!-- <b-form-group label="Fullname *">
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
      </b-form-group> -->

      <!-- <b-form-group label="Email">
        <b-form-input
          type="email"
          v-model="user.email"
          placeholder="Enter email"
        >
        </b-form-input>
      </b-form-group> -->

      <b-form-group label="Profile Picture">
        <b-form-file
          v-model="pro_pic"
          id="pro_pic"
          name="pro_pic"
          drop-placeholder="Drop file here..."
          placeholder="Choose a file or drop it here..."
          :state="'pro_pic' in errors ? false : null"
          accept="image/jpeg, image/png, image/gif"
        >
        </b-form-file>
        <!-- <input type="file" class="form-control" v-on:change="onChange" /> -->
        <b-form-invalid-feedback>
          {{ "file" in errors ? errors.file[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Mobile *">
        <b-form-input
          type="text"
          v-model="user.mobile_no"
          placeholder="Enter mobile no"
          :state="'mobile_no' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "mobile_no" in errors ? errors.mobile_no[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Address Number *">
        <b-form-input
          v-model="user.unit_number"
          type="text"
          placeholder="Enter Address Number"
          :state="'unit_number' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "unit_number" in errors ? errors.unit_number[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Street Name *">
        <b-form-input
          v-model="user.street_name"
          type="text"
          placeholder="Enter Street Name"
          :state="'street_name' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "street_name" in errors ? errors.street_name[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Surburb">
        <b-form-input
          v-model="user.surburb"
          type="text"
          placeholder="Enter Street Name"
          :state="'surburb' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "surburb" in errors ? errors.surburb[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- <b-form-group label="GPS *">
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
          <option value="">Choose</option>
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
      <b-button block type="submit" variant="primary">Submit</b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header-upload";
export default {
  name: "UserDetails",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      pro_pic: [],
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user: {
        email: "",
        mobile_no: "",
        city: "",
        unit_number: "",
        street_name: "",
        surburb: "",
        gps: "",
        user_account: "user",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
      options: [
        { text: "User Account", value: "user" },
        { text: "Doctor Account", value: "doctor" },
      ],
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      // this.user.append("file", this.file);
      let formData = new FormData();
      formData.append("file", this.pro_pic);
      formData.append("email", this.user.email);
      formData.append("mobile_no", this.user.mobile_no);
      formData.append("city", this.user.city);
      formData.append("unit_number", this.user.unit_number);
      formData.append("street_name", this.user.street_name);
      formData.append("surburb", this.user.surburb);
      formData.append("gps", this.user.gps);
      formData.append("user_account", this.user.user_account);
      formData.append("user_id", this.user.user_id);

      this.$axios
        .post(this.$base_url + "contact", formData, authHeader())
        .then((response) => {
          this.loading = false;
          this.$swal("Success!", "Data saved successfully", "success");
          if (this.user.user_account == "user") {
            this.user.user_status = "active";

            var user = JSON.parse(localStorage.getItem("user"));
            user.status = "active";
            user.role = "user";
            localStorage.setItem("user", JSON.stringify(user));
          }
          this.loadCurrentUser();
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
          //location.reload();
          // this.$swal("error!", "There was an error" + error, "error");
        });
    },
    resetForm() {
      this.user.fullname = "";
      this.user.email = "";
      this.user.mobile_no = "";
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
          if (data.data != undefined || data.data != null) {
            this.user.fullname = data.data.name;
            this.user.email = data.data.email;
            this.user.mobile_no = data.data.mobile_no;
            this.user.city = data.data.city;
            this.user.unit_number = data.data.unit_number;
            this.user.street_name = data.data.street_name;
            this.user.surburb = data.data.suburb;
            this.user.gps = data.data.gps;
          }
        });
    },
  },
  created() {
    this.loadCurrentUser();
  },
};
</script>
<style src="./UserDetails.scss" lang="scss"></style>
