<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">My - <span class="fw-semi-bold">Account </span></h1>
    <b-tabs>
      <b-tab title="Personal Details" active>
        <user-details></user-details>
      </b-tab>
      <b-tab>
        <template slot="title">
          My Practice Details
        </template>
        <h3 class="text-danger">Please make sure to upload all required documents to avoid any delays in the review process</h3>
        <my-practice></my-practice>
      </b-tab>
    </b-tabs>
  </section>
</template>

<script>
import userRole from "../../services/user-role";

export default {
  name: "UserAccount",
  data() {
    return {
      user_role: userRole(),
    };
  },

  created() {
    console.log("object");
  },

};
</script>

<script src="./UserAccount.scss" lang="scss" scoped />
