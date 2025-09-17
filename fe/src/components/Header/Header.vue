<template>
  <b-navbar class="header d-print-none app-header">
    <b-nav>
      <b-nav-item>
        <a
          class="d-md-down-none px-2"
          href="#"
          @click="toggleSidebarMethod"
          id="barsTooltip"
        >
          <i class="la la-bars la-lg" />
        </a>
        <a class="fs-lg d-lg-none" href="#" @click="switchSidebarMethod">
          <i class="la la-bars la-lg" />
        </a>
      </b-nav-item>
      <!-- <b-nav-item class="d-md-down-none">
        <a href="#" class="px-2">
          <i class="la la-refresh la-lg" />
        </a>
      </b-nav-item> -->
      <!-- <b-nav-item class="d-md-down-none">
        <a href="#" class="px-2">
          <i class="la la-times la-lg" />
        </a>
      </b-nav-item> -->
    </b-nav>
    <b-nav>
      <b-form class="d-sm-down-none ml-5" inline>
        <b-form-group>
          <!-- <b-input-group class="input-group-no-border">
            <template v-slot:prepend>
              <b-input-group-text
                ><i class="la la-search"
              /></b-input-group-text>
            </template>
            <b-form-input id="search-input" placeholder="Search Dashboard" />
          </b-input-group> -->
        </b-form-group>
      </b-form>
    </b-nav>
    <!-- <a class="navbarBrand d-md-none">
      <i class="fa fa-circle text-danger" />
      &nbsp; clinicPlus &nbsp;
      <i class="fa fa-circle text-primary" />
    </a> -->
    <b-nav class="ml-auto">
      <b-nav-item-dropdown
        class="notificationsMenu mr-2"
        menu-class="notificationsWrapper py-0 animate__animated animate__animated-fast animate__fadeIn"
        right
      >
        <template slot="button-content">
          <span
            class="avatar rounded-circle thumb-sm float-left mr-2"
            v-if="avatar"
          >
            <img
              class="rounded-circle"
              :src="avatar"
              :alt="userDetails.fullname"
            />
          </span>
          <span class="avatar rounded-circle thumb-sm float-left mr-2" v-else>
            <img
              class="rounded-circle"
              src="../../assets/people/default.png"
              :alt="userDetails.fullname"
            />
          </span>
          <span class="small">{{ userDetails.fullname }}</span>
          <!-- <span class="ml-1 mr-3 circle bg-primary text-white fw-bold">13</span> -->
        </template>
        <!-- <Notifications /> -->

        <b-dropdown-item-button @click="redirect('account')">
          <i class="la la-user" /> My Account
        </b-dropdown-item-button>
        <b-dropdown-divider />
        <b-dropdown-item-button @click="redirect('feedback')">
          <i class="la la-mail-forward" /> Feedback
        </b-dropdown-item-button>
        <!-- <b-dropdown-item>
          Inbox &nbsp;&nbsp;<b-badge
            variant="danger"
            pill
            class="animate__animated animate__bounceIn"
            >9</b-badge
          >
        </b-dropdown-item> -->
        <b-dropdown-divider />
        <b-dropdown-item-button @click="logout">
          <i class="la la-sign-out" /> Log Out
        </b-dropdown-item-button>
      </b-nav-item-dropdown>
    </b-nav>
  </b-navbar>
</template>

<script>
import { mapState, mapActions } from "vuex";
import Notifications from "@/components/Notifications/Notifications";
import userRole from "../../services/user-role";

export default {
  name: "Header",
  components: { Notifications },
  computed: {
    ...mapState("layout", ["sidebarClose", "sidebarStatic"]),
  },
  data() {
    return {
      user_role: userRole(),
      errorMessage: null,
      loading: false,
      avatar: JSON.parse(localStorage.getItem("user")).avatar,
      userDetails: JSON.parse(localStorage.getItem("user")),
    };
  },
  methods: {
    ...mapActions("layout", [
      "toggleSidebar",
      "switchSidebar",
      "changeSidebarActive",
    ]),
    switchSidebarMethod() {
      if (!this.sidebarClose) {
        this.switchSidebar(true);
        this.changeSidebarActive(null);
      } else {
        this.switchSidebar(false);
        const paths = this.$route.fullPath.split("/");
        paths.pop();
        this.changeSidebarActive(paths.join("/"));
      }
    },
    toggleSidebarMethod() {
      if (this.sidebarStatic) {
        this.toggleSidebar();
        this.changeSidebarActive(null);
      } else {
        this.toggleSidebar();
        const paths = this.$route.fullPath.split("/");
        paths.pop();
        this.changeSidebarActive(paths.join("/"));
      }
    },
    redirect(page) {
      this.$router.push(`/app/${page}`);
    },
    logout() {
      this.$store.dispatch("auth/logout").then(
        (res) => {
          window.localStorage.setItem("authenticated", false);
          window.localStorage.setItem("otpStatus", false);
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
  created() {},
  mounted() {
    if (window.localStorage.getItem("authenticated") === "false") {
      this.$router.push("/login");
    }
    if (this.user_role.role === undefined || this.user_role.role === null) {
      this.$refs["modal-users"].show();
    }
  },
};
</script>

<style src="./Header.scss" lang="scss"></style>
