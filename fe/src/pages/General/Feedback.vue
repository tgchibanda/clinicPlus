<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      <span class="fw-semi-bold">Feedback</span>
    </h1>

    <Widget class="h-100 mb-0" title="Feedback" close>
      <b-form @submit.prevent="handleSubmit" v-if="user_role.role != 'admin'">
        <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
          {{ errorMessage }}
        </b-alert>
        <b-form-group label="Feedback *">
          <b-form-textarea
            v-model="feedback.feedback"
            id="textarea"
            placeholder="Enter feedback"
            rows="3"
            max-rows="6"
            :state="'feedback' in errors ? false : null"
          ></b-form-textarea>
          <b-form-invalid-feedback>
            {{ "feedback" in errors ? errors.feedback[0] : true }}
          </b-form-invalid-feedback>
        </b-form-group>
        <b-button block type="submit" variant="primary">Submit</b-button>
      </b-form>
      <div class="table-responsive" v-if="user_role.role == 'admin'">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Date</th>
              <th>Fullname</th>
              <th>Feedback</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in feedbacks.data" :key="row.id">
              <td>{{ row.created_at }}</td>
              <td>{{ row.name }}</td>
              <td>{{ row.feedback }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Widget>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "Feedback",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      feedbacks: {},
      feedback: {
        feedback: "",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
      user_role: userRole(),
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "feedback", this.feedback, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
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
    loadFeedback() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "feedback", authHeader())
        .then(({ data }) => {
          this.feedbacks = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
  },
  created() {
    if (this.user_role.role == 'admin') {
      this.loadFeedback();
    }
  },
};
</script>


