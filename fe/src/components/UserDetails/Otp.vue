<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <div>Please enter OTP from your email inbox</div>
      <hr />
      <b-form-group label="OTP">
        <b-form-input
          v-model="user.otp"
          type="text"
          placeholder="Enter OTP"
          :state="'otp' in errors ? false : null"
        >
        </b-form-input>
        <b-form-invalid-feedback>
          {{ "otp" in errors ? errors.otp[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-button block type="submit" variant="primary">Submit</b-button>
      <br />
      <a href="" @click="logout"> <i class="la la-sign-out" /> Log Out </a>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "Otp",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user: {
        otp: "",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      if ((JSON.parse(localStorage.getItem("user")).otp == this.user.otp)) {
        window.localStorage.setItem("otpStatus", true);
        this.$swal("Success!", "OTP is correct", "success");
        location.reload();
      } else {
        window.localStorage.setItem("otpStatus", false);
        this.$swal("Failed!", "OTP is incorrect", "error");
      }
    },
    logout() {
      this.$store.dispatch("auth/logout").then(
        (res) => {
          window.localStorage.setItem("authenticated", false);
          window.localStorage.setItem("user", false);
          this.$router.push("/login");
        },
        (error) => {
          this.loading = false;
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
        }
      );
    },
  },
  created() {
    console.log(JSON.parse(localStorage.getItem("user")));
  },
};
</script>
