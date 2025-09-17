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
        title="<h3 class='mt-0'>Sign Up</h3>"
        customHeader
      >
        <p class="widget-auth-info">Use your email to sign up.</p>
        <form class="mt" @submit.prevent="register">
          <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
            {{ errorMessage }}
          </b-alert>
          <div class="form-group">
            <input
              class="form-control no-border"
              ref="name"
              required
              type="name"
              name="name"
              placeholder="Fullname"
            />
            <b-alert class="alert-sm" v-if="submitted && errors.has('name')"
              >{{ errors.first("name") }}
            </b-alert>
          </div>
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
          <div class="form-group">
            <input
              class="form-control no-border"
              ref="c_password"
              required
              type="password"
              name="c_password"
              placeholder="Confirm Password"
            />
          </div>
          <b-button
            type="submit"
            size="sm"
            class="auth-btn mb-3"
            variant="inverse"
            >Register</b-button
          >
          <p class="widget-auth-info">or sign up with</p>
          <!-- <social-login></social-login> -->
        </form>
        <router-link class="d-block text-center" to="login"
          >Already have an account</router-link
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
import SocialLogin from "../../components/Login/SocialLogin.vue";

export default {
  name: "RegisterPage",
  components: { Widget, SocialLogin },
  data() {
    return {
      errorMessage: null,
      submitted: false,
      successful: false,
      message: "",
    };
  },
  methods: {
    register() {
      const email = this.$refs.email.value;
      const password = this.$refs.password.value;
      const c_password = this.$refs.c_password.value;
      const name = this.$refs.name.value;
      const user = [name, email, password, c_password];

      this.$store.dispatch("auth/register", user).then(
        (data) => {
          this.successful = true;
          this.$swal("Success!", "Signup complete successfully", "success");
        },
        (error) => {
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
          this.successful = false;
        }
      );
    },
    // });
    //   },
  },
  created() {
    if (window.localStorage.getItem("authenticated") === "true") {
      this.$router.push("/app/main/analytics");
    }
  },
};
</script>
