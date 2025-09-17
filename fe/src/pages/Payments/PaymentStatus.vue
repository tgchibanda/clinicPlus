<!-- eslint-disable max-len -->
<template>
  <section>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while we proccess your payment'"
    />
    <h1 class="page-title">
      Payment - <span class="fw-semi-bold">Status</span>
    </h1>

    <b-row>
      <b-col xs="12" v-if="isPaymentSuccessful">
        <Widget class="h-100 mb-0">
          <h2>Thank you so much for your payment</h2>
          <invoice :paymentData="this.paymentData"></invoice>
        </Widget>
      </b-col>
      <b-col xs="12" v-else class="text-center">
        <Widget class="h-100 mb-0">
          <h2>Payment Failed .....</h2>
          <img src="../../assets/oops.png" alt="..." />
        </Widget>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "PaymentStatus",
  data() {
    return {
      isPaymentSuccessful: false,
      paymentData: {
        paymentId: "",
        PayerID: "",
        token: "",
      },
      isLoading: false,
    };
  },
  methods: {
    checkQueryString() {
      var field = "paymentId";
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
    checkPayment() {
      this.isLoading = true;
      this.$axios
        .post(this.$base_url + "payment/status", this.paymentData, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          this.isLoading = false;
          this.isPaymentSuccessful = true;
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
          this.isLoading = false;
          this.$swal("Error!!!", this.errorMessage, "error");
        });
    },
  },
  created() {
    if (this.checkQueryString()) {
      this.paymentData.paymentId = this.getUrlParameter("paymentId");
      this.paymentData.payerID = this.getUrlParameter("PayerID");
      this.paymentData.token = this.getUrlParameter("token");
      this.checkPayment();
    }
  },
};
</script>


