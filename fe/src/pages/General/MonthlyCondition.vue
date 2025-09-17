<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      Monthly - <span class="fw-semi-bold">Condition</span>
    </h1>
    <b-row v-if="user_role.role == 'admin'">
      <b-col md="6" xl="6" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Condition" close>
          <b-form @submit.prevent="handleSubmit">
            <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
              {{ errorMessage }}
            </b-alert>
            <b-form-group label="Title *">
              <b-form-input
                v-model="condition.title"
                type="text"
                placeholder="Enter Title"
                :state="'title' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "title" in errors ? errors.title[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group label="Description *">
              <b-form-textarea
                v-model="condition.description"
                id="textarea"
                placeholder="Enter description"
                rows="12"
                max-rows="12"
                :state="'description' in errors ? false : null"
              ></b-form-textarea>
              <b-form-invalid-feedback>
                {{ "description" in errors ? errors.description[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>
            <b-button block type="submit" variant="primary">Submit</b-button>
          </b-form>
        </Widget>
      </b-col>
      <b-col md="6" xl="6" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Manage" close>
          <div class="table-responsive" v-if="user_role.role == 'admin'">
            <table class="table table-striped table-lg mb-0 requests-table">
              <thead>
                <tr class="text-muted">
                  <th>Date</th>
                  <th>Title</th>
                  <th>Description</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in monthlyConditions.data" :key="row.id">
                  <td>{{ row.created_at }}</td>
                  <td>{{ row.title }}</td>
                  <td>{{ row.description }}</td>
                  <td>
                    <b-button variant="danger" @click="deleteRow(row.id)"
                      ><span class="fa fa-times" /> Delete</b-button
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Widget>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "MonthlyCondition",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      monthlyConditions: {},
      condition: {
        title: "",
        description: "",
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
      },
      user_role: userRole(),
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(
          this.$base_url + "monthly_condition",
          this.condition,
          authHeader()
        )
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          this.loadMonthlyConditions();
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
    deleteRow(id) {
      this.loading = true;
      this.$axios
        .delete(this.$base_url + "monthly_condition/" + id, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          this.loadMonthlyConditions();
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

    loadMonthlyConditions() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "monthly_condition", authHeader())
        .then(({ data }) => {
          this.monthlyConditions = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
  },
  created() {
    if (this.user_role.role == "admin") {
      this.loadMonthlyConditions();
    }
  },
};
</script>


