<template>
  <div class="sidebar-wrapper">
    <nav :class="{ sidebar: true, sidebarStatic, sidebarOpened }" @mouseenter="sidebarMouseEnter"
      @mouseleave="sidebarMouseLeave">
      <header class="logo">

        <span class="primary-word">clinicPlus</span> App

      </header>

      <!-- PHARMACY OFFICER -->
      <ul class="nav" v-if="userRole.status == 'active' && userRole.role == 'user'">

        <NavLink :activeItem="activeItem" header="Prescription Payments" link="/app/prescriptions"
          iconName="flaticon-database-1" index="prescriptions" isHeader />

      </ul>
      <!-- FIELD OFFICER -->
      <ul class="nav" v-if="userRole.status == 'active' && userRole.role == 'field officer'">

        <NavLink :activeItem="activeItem" header="Patients" link="/app/walkinpatients" iconName="flaticon-user-6"
          index="walkinpatients" isHeader />

        <NavLink :activeItem="activeItem" header="Insurance" link="/public-signup" iconName="flaticon-compass"
          index="insurance" isHeader />

      </ul>


      <!-- DOCTOR -->
      <ul class="nav" v-if="userRole.status == 'active' && userRole.role == 'doctor'">
        <NavLink :activeItem="activeItem" header="Consultations" link="/app/consultations" iconName="flaticon-calendar"
                  index="consultations" isHeader />

        <NavLink :activeItem="activeItem" header="Patients" link="/app/walkinpatients" iconName="flaticon-user-6"
          index="walkinpatients" isHeader />
      </ul>

      <!-- ADMIN -->
      <ul class="nav" v-if="userRole.status == 'active' && userRole.role == 'admin'">

        <NavLink :activeItem="activeItem" header="Dashboard" link="/app/reports" iconName="flaticon-home"
          index="dashboard" isHeader />

        <NavLink :activeItem="activeItem" header="Consultations" link="/app/consultations" iconName="flaticon-calendar"
          index="consultations" isHeader />

        <NavLink :activeItem="activeItem" header="Patients" link="/app/walkinpatients" iconName="flaticon-user-6"
          index="walkinpatients" isHeader />

        <NavLink :activeItem="activeItem" header="Pharmacy" link="/app/drugs" iconName="flaticon-list" index="drugs"
          isHeader />

        <NavLink :activeItem="activeItem" header="Prescription Payments" link="/app/prescriptions"
          iconName="flaticon-database-1" index="prescriptions" isHeader />

        <NavLink :activeItem="activeItem" header="Insurance" link="/app/insurance" iconName="flaticon-compass"
          index="insurance" isHeader />

        <NavLink :activeItem="activeItem" header="Users" link="/app/users" iconName="flaticon-user-3" index="users"
          isHeader />

        <NavLink :activeItem="activeItem" header="Directory" link="/app/AddDirectory" iconName="flaticon-folder"
          index="AddDirectory" isHeader />
      </ul>

      <!-- INACTIVE / PENDING -->
      <ul class="nav" v-if="userRole.status == 'inactive' || userRole.status == 'pending' || userRole.status == null">
        <NavLink :activeItem="activeItem" header="My Account" link="/app/account" iconName="flaticon-user"
          index="account" isHeader />

        <NavLink :activeItem="activeItem" header="Directory" link="/app/directory" iconName="flaticon-compass"
          index="directory" isHeader />
      </ul>

    </nav>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import isScreen from "@/core/screenHelper";
import NavLink from "./NavLink/NavLink";
import userRole from "../../services/user-role";
import isMobile from "../../services/media-query";

export default {
  name: "Sidebar",
  components: { NavLink },
  data() {
    return {
      userRole: userRole(),
      isMobile: isMobile(),
    };
  },
  methods: {
    ...mapActions("layout", ["changeSidebarActive", "switchSidebar"]),
    setActiveByRoute() {
      const paths = this.$route.fullPath.split("/");
      paths.pop();
      this.changeSidebarActive(paths.join("/"));
    },
    sidebarMouseEnter() {
      if (!this.sidebarStatic && (isScreen("lg") || isScreen("xl"))) {
        this.switchSidebar(false);
        this.setActiveByRoute();
      }
    },
    sidebarMouseLeave() {
      if (!this.sidebarStatic && (isScreen("lg") || isScreen("xl"))) {
        this.switchSidebar(true);
        this.changeSidebarActive(null);
      }
    },
    logout() {
      this.$store.dispatch("auth/logout").then(() => {
        localStorage.setItem("authenticated", false);
        localStorage.setItem("user", false);
        this.$router.push("/login");
      });
    },
  },
  created() {
    this.setActiveByRoute();
  },
  computed: {
    ...mapState("layout", {
      sidebarStatic: (state) => state.sidebarStatic,
      sidebarOpened: (state) => !state.sidebarClose,
      activeItem: (state) => state.sidebarActiveElement,
    }),
  },
};
</script>

<style src="./Sidebar.scss" lang="scss" scoped />
