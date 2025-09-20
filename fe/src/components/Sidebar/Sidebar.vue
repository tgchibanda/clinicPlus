<template>
  <div class="sidebar-wrapper">
    <nav
      :class="{ sidebar: true, sidebarStatic, sidebarOpened }"
      @mouseenter="sidebarMouseEnter"
      @mouseleave="sidebarMouseLeave"
    >
      <header class="logo">
        <router-link to="/app/dashboard"
          ><span class="primary-word">clinicPlus</span> App</router-link
        >
      </header>
      <ul
        class="nav"
        v-if="userRole.status == 'active' && userRole.role == 'user'"
      >
        <NavLink
          :activeItem="activeItem"
          header="Dashboard"
          link="/app/dashboard"
          iconName="flaticon-home"
          index="dashboard"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Consultation"
          link="/app/consultation"
          iconName="flaticon-list"
          index="consultation"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="My Patients"
          link="/app/patients"
          iconName="flaticon-user-6"
          index="patients"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Payments"
          link="/app/my-payments"
          iconName="flaticon-database-1"
          index="my-payments"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="My Account"
          link="/app/account"
          iconName="flaticon-user"
          index="account"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Directory"
          link="/app/directory"
          iconName="flaticon-compass"
          index="directory"
          isHeader
        />
      </ul>
      <ul
        class="nav"
        v-if="userRole.status == 'active' && userRole.role == 'doctor'"
      >
        <NavLink
          :activeItem="activeItem"
          header="Dashboard"
          link="/app/dashboard"
          iconName="flaticon-home"
          index="dashboard"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Consultation"
          link="/app/consultation"
          iconName="flaticon-list"
          index="consultation"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Payments"
          link="/app/my-payments"
          iconName="flaticon-database-1"
          index="my-payments"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="My Account"
          link="/app/account"
          iconName="flaticon-user"
          index="account"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Directory"
          link="/app/directory"
          iconName="flaticon-compass"
          index="directory"
          isHeader
        />
      </ul>
      <ul
        class="nav"
        v-if="userRole.status == 'active' && userRole.role == 'admin'"
      >
           

        <NavLink
            :activeItem="activeItem"
            header="Walk In Patients"
            link="/app/walkinpatients"
            iconName="flaticon-compass"
            index="walkinpatients"
            isHeader
          />

        <NavLink
            :activeItem="activeItem"
            header="Pharmacy"
            link="/app/drugs"
            iconName="flaticon-compass"
            index="drugs"
            isHeader
          />

        <NavLink
            :activeItem="activeItem"
            header="Prescriptions"
            link="/app/prescriptions"
            iconName="flaticon-compass"
            index="prescriptions"
            isHeader
          />

        <NavLink
            :activeItem="activeItem"
            header="Sales"
            link="/app/sales"
            iconName="flaticon-compass"
            index="sales"
            isHeader
          />

        <NavLink
            :activeItem="activeItem"
            header="Reports"
            link="/app/walkinpatients"
            iconName="flaticon-compass"
            index="walkinpatients"
            isHeader
          />


        <NavLink
          :activeItem="activeItem"
          header="Dashboard"
          link="/app/dashboard"
          iconName="flaticon-home"
          index="dashboard"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Users"
          link="/app/users"
          iconName="flaticon-user-4"
          index="users"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Payouts"
          link="/app/payouts"
          iconName="flaticon-database-1"
          index="payouts"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="My Account"
          link="/app/account"
          iconName="flaticon-user"
          index="account"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Reports"
          link="/app/reports"
          iconName="flaticon-network"
          index="reports"
          :childrenLinks="[
            { header: 'Payments', link: '/app/reports/PaymentReport' },
            { header: 'Consultation', link: '/app/reports/ConsultationReport' },
            { header: 'Payouts', link: '/app/reports/PayoutReport' },
          ]"
        />
        <NavLink
          :activeItem="activeItem"
          header="Feedback"
          link="/app/feedback"
          iconName="flaticon-compass"
          index="feedback"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Condition of the month"
          link="/app/condition"
          iconName="flaticon-eyeglasses"
          index="condition"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Directory"
          link="/app/AddDirectory"
          iconName="flaticon-compass"
          index="AddDirectory"
          isHeader
        />
      </ul>
      <ul
        class="nav"
        v-if="
          userRole.status == 'inactive' ||
          userRole.status == 'pending' ||
          userRole.status == null
        "
      >
        <NavLink
          :activeItem="activeItem"
          header="My Account"
          link="/app/account"
          iconName="flaticon-user"
          index="account"
          isHeader
        />
        <NavLink
          :activeItem="activeItem"
          header="Directory"
          link="/app/directory"
          iconName="flaticon-compass"
          index="directory"
          isHeader
        />
      </ul>
      <!-- <ul class="nav" v-if="isMobile">
        <li class="headerLink">
          <a @click="logout" class="sidebar-link"
            ><span class="icon"><i class="fi flaticon-compass"></i></span>
            Logout
          </a>
        </li>
      </ul> -->
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
      alerts: [
        {
          id: 0,
          title: "Sales Report",
          value: 15,
          footer: "Calculating x-axis bias... 65%",
          color: "danger",
        },
        {
          id: 1,
          title: "Personal Responsibility",
          value: 20,
          footer: "Provide required notes",
          color: "primary",
        },
      ],
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
      this.$store.dispatch("auth/logout").then(
        (res) => {
          window.localStorage.setItem("authenticated", false);
          window.localStorage.setItem("user", false);
          this.$router.push("/login");
        },
        (error) => {
          this.loading = false;
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
        }
      );
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

<!-- Sidebar styles should be scoped -->
<style src="./Sidebar.scss" lang="scss" scoped/>
