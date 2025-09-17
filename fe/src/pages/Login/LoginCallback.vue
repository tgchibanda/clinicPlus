<template>
  <div class="auth-page">
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are autheticated'"
    />
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";

export default {
  name: "LoginCallback",
  components: { Widget },
  data() {
    return {
      errorMessage: null,
      isLoading: false,
      e: "",
      p: "",
    };
  },
  methods: {
    login() {
      const email = this.e;
      const password = this.p;
      const user = [email, password];

      this.isLoading = true;
      if (email && password) {
        this.$store.dispatch("auth/login", user).then(
          () => {
            window.localStorage.setItem("authenticated", true);
            this.$router.push("/app/dashboard");
          },
          (error) => {
            console.log(error);
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
      }
      // });
    },
    checkQueryString() {
      var field = "e";
      var url = window.location.href;
      if (url.indexOf("?" + field + "=") != -1) return true;
      else if (url.indexOf("&" + field + "=") != -1) return true;
      return false;
    },
    getUrlParameter(name) {
      name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
      var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
      var results = regex.exec(location.href);
      return results === null
        ? ""
        : decodeURIComponent(results[1].replace(/\+/g, " "));
    },
  },
  created() {
    console.log("object");
    if (this.checkQueryString()) {
      this.e = this.getUrlParameter("e");
      this.p = this.getUrlParameter("p");
      this.login();
    }
  },
};
</script>
