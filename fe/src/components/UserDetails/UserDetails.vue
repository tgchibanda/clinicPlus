<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>

      <!-- Account Type -->
      <b-form-group label="Account Type" v-slot="{ ariaDescribedby }">
        <b-form-radio-group
          id="btn-radios-2"
          v-model="user.user_account"
          :options="options"
          :aria-describedby="ariaDescribedby"
          button-variant="outline-primary"
          size="lg"
          name="radio-btn-outline"
          buttons
        />
      </b-form-group>

      <!-- Location -->
      <b-form-group label="Location">
        <b-form-select
          v-model="user.location"
          :state="errors.location ? false : null"
        >
          <option value="">Choose</option>
          <option value="1">Chadcombe</option>
          <option value="2">Zengeza</option>
          <option value="3">Norton</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ errors.location ? errors.location[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Fullname -->
      <b-form-group label="Fullname *">
        <b-form-input
          type="text"
          v-model="user.fullname"
          placeholder="Enter Fullname"
          :state="errors.fullname ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.fullname ? errors.fullname[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Email -->
      <b-form-group label="Email">
        <b-form-input
          type="email"
          v-model="user.email"
          placeholder="Enter email"
          :state="errors.email ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.email ? errors.email[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Password -->
      <b-form-group label="Password *">
        <b-form-input
          type="password"
          v-model="user.password"
          placeholder="Enter password"
          :state="errors.password ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.password ? errors.password[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Confirm Password -->
      <b-form-group label="Confirm Password *">
        <b-form-input
          type="password"
          v-model="user.password_confirmation"
          placeholder="Confirm password"
          :state="errors.password_confirmation ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.password_confirmation ? errors.password_confirmation[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Mobile -->
      <b-form-group label="Mobile *">
        <b-form-input
          type="text"
          v-model="user.mobile_no"
          placeholder="Enter mobile no"
          :state="errors.mobile_no ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.mobile_no ? errors.mobile_no[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Address Number -->
      <b-form-group label="Address Number *">
        <b-form-input
          v-model="user.unit_number"
          type="text"
          placeholder="Enter Address Number"
          :state="errors.unit_number ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.unit_number ? errors.unit_number[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Street -->
      <b-form-group label="Street Name *">
        <b-form-input
          v-model="user.street_name"
          type="text"
          placeholder="Enter Street Name"
          :state="errors.street_name ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.street_name ? errors.street_name[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Suburb -->
      <b-form-group label="Surburb">
        <b-form-input
          v-model="user.surburb"
          type="text"
          placeholder="Enter Surburb"
          :state="errors.surburb ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.surburb ? errors.surburb[0] : '' }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- City -->
      <b-form-group label="City">
        <b-form-select
          v-model="user.city"
          :state="errors.city ? false : null"
        >
          <option value="">Choose</option>
          <option value="Harare">Harare</option>
          <option value="Chitungwiza">Chitungwiza</option>
          <option value="Mutare">Mutare</option>
          <option value="Bulawayo">Bulawayo</option>
          <option value="Gweru">Gweru</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ errors.city ? errors.city[0] : '' }}
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

      user: {
        fullname: "",
        email: "",
        password: "",
        password_confirmation: "",
        mobile_no: "",
        location: "",
        city: "",
        unit_number: "",
        street_name: "",
        surburb: "",
        user_account: "",
        user_id: "",
      },

      options: [
        { text: "Pharmacy Account", value: "user" },
        { text: "Doctor Account", value: "doctor" },
        { text: "Field Officer", value: "field officer" },
      ],
    };
  },

  methods: {
    handleSubmit() {
      this.loading = true;
      this.errors = {};

      let formData = new FormData();
      formData.append("fullname", this.user.fullname);
      formData.append("email", this.user.email);
      formData.append("password", this.user.password);
      formData.append(
        "password_confirmation",
        this.user.password_confirmation
      );
      formData.append("mobile_no", this.user.mobile_no);
      formData.append("city", this.user.city);
      formData.append("location", this.user.location);
      formData.append("unit_number", this.user.unit_number);
      formData.append("street_name", this.user.street_name);
      formData.append("surburb", this.user.surburb);
      formData.append("user_account", this.user.user_account);
      formData.append("user_id", this.user.user_id);

      this.$axios
        .post(this.$base_url + "contact", formData, authHeader())
        .then(() => {
          this.loading = false;
          this.$swal("Success!", "User created successfully", "success");
        })
        .catch((error) => {
          this.loading = false;

          this.errorMessage =
            error.response?.data?.message ||
            "An unexpected error occurred.";

          this.errors = error.response?.data?.errors || {};
        });
    },
  },
};
</script>

<style src="./UserDetails.scss" lang="scss"></style>
