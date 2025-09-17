<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title" v-if="user_role.role == 'admin'">
      Process - <span class="fw-semi-bold">Payouts</span>
    </h1>
    <h1 class="page-title" v-if="user_role.role == 'doctor'">
      Your - <span class="fw-semi-bold">Payouts</span>
    </h1>

    <b-row>
      <b-col md="6" xl="4" sm="6" xs="12">
        <processed-payout-table></processed-payout-table>
      </b-col>
      <b-col md="6" xl="8" sm="6" xs="12">
        <payout-table></payout-table>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import PayoutTable from '../../components/Payments/PayoutTable.vue';
import ProcessedPayoutTable from '../../components/Payments/ProcessedPayoutTable.vue';
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  components: { PayoutTable, ProcessedPayoutTable },
  name: "Payouts",
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


