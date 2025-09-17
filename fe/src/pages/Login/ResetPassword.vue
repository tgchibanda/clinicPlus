<template>
  <div class="auth-page">
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while we Reset your password'"
    />
    <b-container>
      <h5 class="auth-logo">
        <i class="fa fa-circle text-primary"></i>
        clinicPlus App
        <i class="fa fa-circle text-danger"></i>
      </h5>
      <Widget
        class="widget-auth mx-auto"
        title="<h3 class='mt-0'>Reset Password</h3>"
        customHeader
      >
        <p class="widget-auth-info">Reset Password</p>
        <form class="mt" @submit.prevent="resetPassword">
          <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
            {{ errorMessage }}
          </b-alert>
          <div class="form-group">
            <input
              v-on:blur="validate"
              class="form-control no-border"
              ref="email"
              required
              type="email"
              name="email"
              placeholder="Email"
            />
          </div>
          <div class="form-group">
            <input
              v-on:blur="validate"
              class="form-control no-border"
              ref="password"
              required
              type="password"
              name="password"
              placeholder="Password"
            />
          </div>
          <div class="form-group">
            <input
              v-on:blur="validate"
              class="form-control no-border"
              ref="c_password"
              required
              type="password"
              name="c_password"
              placeholder="Confirm password"
            />
          </div>
          <b-button
            type="submit"
            size="sm"
            class="auth-btn mb-3"
            variant="inverse"
            >Reset Password</b-button
          >
        </form>
        <router-link class="d-block text-center" to="../login"
          >Login</router-link
        >
      </Widget>
    </b-container>
    <footer class="auth-footer">
      2021 &copy; clinicPlus App Developed By
      <a
        href="https://ernestmuroiwa.com"
        rel="nofollow noopener noreferrer"
        target="_blank"
        >GM58</a
      >
    </footer>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";

export default {
  name: "ResetPasswordPage",
  components: { Widget },
  data() {
    return {
      errorMessage: null,
      isLoading: false,
    };
  },
  methods: {
    validate() {
      if (this.$refs.c_password.value != this.$refs.password.value) {
        this.errorMessage = "Passwords don't match";
      } else {
        this.errorMessage = "";
      }
    },
    resetPassword() {
      const email = this.$refs.email.value;
      const token = this.$route.params.token;
      const password = this.$refs.password.value;
      const c_password = this.$refs.c_password.value;
      const user = [email, token, password, c_password];

      this.isLoading = true;
      this.$store.dispatch("auth/reset", user).then(
        () => {
          this.isLoading = false;
          this.$swal("Success!", "Reset password successfully", "success");
          this.$router.push("/login");
        },
        (error) => {
          this.isLoading = false;
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
  created() {},
};
</script>
