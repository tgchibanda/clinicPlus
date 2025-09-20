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
        title="<h3 class='mt-0'>Forgot Password</h3>"
        customHeader
      >
        <p class="widget-auth-info">Use your email to recover account.</p>
        <form class="mt" @submit.prevent="login">
          <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
            {{ errorMessage }}
          </b-alert>
          <div class="form-group">
            <input
              class="form-control no-border"
              ref="email"
              required
              type="email"
              name="email"
              placeholder="Email"
            />
          </div>
          <b-button
            type="submit"
            size="sm"
            class="auth-btn mb-3"
            variant="inverse"
            >Recover Account</b-button
          >
        </form>
        <router-link class="d-block text-center" to="login">Login</router-link>
      </Widget>
    </b-container>
    <footer class="auth-footer">
      2021 &copy; clinicPlus App Developed By
      <a
        rel="nofollow noopener noreferrer"
        target="_blank"
        >Takunda Chibanda</a
      >
    </footer>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";

export default {
  name: "ForgotPasswordPage",
  components: { Widget },
  data() {
    return {
      errorMessage: null,
      isLoading: false,
    };
  },
  methods: {
    login() {
      const email = this.$refs.email.value;
      const user = [email];
      this.isLoading = true;

      this.$store.dispatch("auth/forgot", user).then(
        () => {
          this.isLoading = false;
          this.$swal(
            "Success!",
            "Reset password link sent on your email id",
            "success"
          );
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
    if (window.localStorage.getItem("authenticated") === "true") {
      this.$router.push("/app/main/analytics");
    }
  },
};
</script>
