<template>
  <div class="dashboard-page">
    <b-modal
      id="modal-users"
      ref="modal-users"
      size="lg"
      title="User Role"
      hide-footer
      :no-close-on-backdrop="true"
      :hide-header-close="true"
    >
      <user-role></user-role>
    </b-modal>
    <b-modal
      id="modal-otp"
      ref="modal-otp"
      size="lg"
      title="One time pin"
      hide-footer
      :no-close-on-backdrop="true"
      :hide-header-close="true"
    >
      <otp></otp>
    </b-modal>
    <div v-if="opt_status == 'true'">
      <h1 class="page-title">Dashboard</h1>
      <div v-if="user_role.status == 'active' && user_role.role == 'doctor'">
        <doctor-dashboard></doctor-dashboard>
      </div>
      <div v-else-if="user_role.status == 'active' && user_role.role == 'user'">
        <user-dashboard></user-dashboard>
      </div>
      <div v-else-if="user_role.status == 'active' && user_role.role == 'admin'">
        <admin-dashboard></admin-dashboard>
      </div>
      <div v-else>
        <h3>
          Please finish setting up you account here.
          <router-link to="/app/account">Setup account</router-link>
        </h3>
      </div>
    </div>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";
import userRole from "../../services/user-role";
import authHeader from "../../services/auth-header";
import DoctorDashboard from "../../components/Dashboard/DoctorDashboard.vue";
import UserDashboard from "../../components/Dashboard/UserDashboard.vue";
import AdminDashboard from "../../components/Dashboard/AdminDashboard.vue";
import Otp from '../../components/UserDetails/Otp.vue';

export default {
  name: "Dashboard",
  components: {
    Widget,
    DoctorDashboard,
    UserDashboard,
    AdminDashboard,
    Otp,
  },
  data() {
    return {
      dashboard_condition: {},
      user_role: userRole(),
      opt_status: localStorage.getItem("otpStatus"),
      loading: false,
    };
  },
  methods: {},

  created() {},
  mounted() {
    if (window.localStorage.getItem("authenticated") === "false") {
      this.$router.push("/login");
    }

    if (this.user_role.role === undefined || this.user_role.role === null) {
      this.$refs["modal-users"].show();
    }
    if (localStorage.getItem("otpStatus") == 'falsee') {
      this.$refs["modal-otp"].show();
    }
  },
};
</script>

<style src="./Dashboard.scss" lang="scss" />
