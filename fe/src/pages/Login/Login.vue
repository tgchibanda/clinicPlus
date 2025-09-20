<template>
  <div class="auth-page">
    <b-container>
      <h5 class="auth-logo">
        <i class="fa fa-circle text-primary"></i>
        clinicPlus App
        <i class="fa fa-circle text-danger"></i>
      </h5>
      <Widget
        class="widget-auth mx-auto"
        title="<h3 class='mt-0'>Login to clinicPlus</h3>"
        customHeader
      >
        <p class="widget-auth-info">Use your email to sign in.</p>
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
          <div class="form-group">
            <input
              class="form-control no-border"
              ref="password"
              required
              type="password"
              name="password"
              placeholder="Password"
            />
          </div>
          <b-button
            type="submit"
            size="sm"
            class="auth-btn mb-3"
            variant="inverse"
            >Login</b-button
          >
          <!-- <social-login></social-login> -->
        </form>
        <p class="widget-auth-info">Don't have an account? Sign up now!</p>
        <router-link class="d-block text-center" to="register"
          >Create an Account</router-link
        >
        <router-link class="d-block text-center" to="forgot-password"
          >Forgot Password</router-link
        >
      </Widget>
    </b-container>
    <footer class="auth-footer">
      <cookie-law theme="dark-lime"></cookie-law>
      2021 &copy; clinicPlus App Developed By
      <a
        rel="nofollow noopener noreferrer"
        target="_blank"
        >Takunda Chibanda</a
      >
      <div>
        <router-link class="d-block text-center" to="privacy"
          >Privacy</router-link
        >
      </div>
    </footer>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";
import SocialLogin from "../../components/Login/SocialLogin.vue";
import CookieLaw from "vue-cookie-law";

export default {
  name: "LoginPage",
  components: { SocialLogin, Widget, CookieLaw },
  data() {
    return {
      errorMessage: null,
      loading: false,
    };
  },
  methods: {
    login() {
      const email = this.$refs.email.value;
      const password = this.$refs.password.value;
      const user = [email, password];

      this.loading = true;
      if (email && password) {
        this.$store.dispatch("auth/login", user).then(
          () => {
            window.localStorage.setItem("authenticated", true);
            window.localStorage.setItem("otpStatus", false);

            this.$router.push("/app/dashboard");
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
      }
      // });
    },
  },
  created() {
    if (window.localStorage.getItem("authenticated") === "true") {
      this.$router.push("/app/dashboard");
    }
  },
};
</script>
