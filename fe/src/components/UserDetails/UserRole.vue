<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <div>
        Select the user type for you. NB Doctor account wil be verified by the
        Admin
      </div>
      <hr />
      <b-form-group label="Account Type" v-slot="{ ariaDescribedby }">
        <b-form-radio-group
          id="btn-radios-2"
          v-model="user.user_account"
          :options="options"
          :aria-describedby="ariaDescribedby"
          button-variant="outline-warning"
          size="lg"
          name="radio-btn-outline"
          buttons
        ></b-form-radio-group>
      </b-form-group>

      <b-button block type="submit" variant="primary">Submit</b-button>
      <br />
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "UserRole",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user: {
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
      console.log(this.user);
      if (this.user.user_account == "user") {
        this.user.status = "active";
        this.user.role = "user";
      } else {
        this.user.role = "doctor";
        this.user.status = "pending";
      }
      this.$axios
        .post(this.$base_url + "user_role", this.user, authHeader())
        .then((response) => {
          var user = JSON.parse(localStorage.getItem("user"));

          if (this.user.user_account == "user") {
            user.status = "active";
            user.role = "user";
            localStorage.setItem("user", JSON.stringify(user));
            this.$swal("Success!", "Data saved successfully", "success");
            location.reload();
          } else {
            user.status = "pending";
            user.role = "doctor";
            localStorage.setItem("user", JSON.stringify(user));
            this.$swal(
              "Success!",
              "You need to add your professional data to get activated",
              "success"
            ).then((okay) => {
              if (okay) {
                window.location.href = location.protocol + '//' + location.host + "/#/app/account";
              }
            });
          }
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
        });
    },
  },
  created() {
    console.log(JSON.parse(localStorage.getItem("user")));
  },
};
</script>
