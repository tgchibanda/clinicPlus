<!-- eslint-disable max-len -->
<template>
  <section>
    <b-modal
      id="modal-doctor-notes"
      ref="modal-doctor-notes"
      size="lg"
      title="Doctor's notes"
      hide-footer
    >
      <doctors-notes :consultation="userDetail"></doctors-notes>
    </b-modal>

    <h1 class="page-title">
      User - <span class="fw-semi-bold">Details</span>
    </h1>

    <b-row>
      <!-- USER CARD -->
      <b-col md="6" xl="4" sm="6" xs="12">
        <Widget class="h-100 mb-0" :fetchingData="loadingPersonalDetails">
          <div class="d-flex">
            <span class="avatar rounded-circle thumb-xl mr-3">
              <img src="../UserAccount/user.png" class="rounded-circle" width="120" />
            </span>

            <div>
              <h4>{{ userDetail.name }}</h4>

              <p class="mb-1">
                Status
                <b-badge
                  pill
                  :variant="
                    userDetail.status === 'active'
                      ? 'success'
                      : userDetail.status === 'suspended'
                      ? 'danger'
                      : 'info'
                  "
                >
                  {{ userDetail.status }}
                </b-badge>
              </p>

              <p class="text-secondary mb-1">{{ userDetail.email }}</p>

              <p class="text-muted">
                <a :href="`tel:${userDetail.mobile_no}`">
                  Call {{ userDetail.mobile_no }}
                </a>
              </p>
            </div>
          </div>
        </Widget>
      </b-col>

      <!-- ADDRESS -->
      <b-col md="6" xl="4" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Address">
          {{ userDetail.unit_number }}<br />
          {{ userDetail.street_name }}<br />
          {{ userDetail.suburb }}<br />
          {{ userDetail.city }}
        </Widget>
      </b-col>

      <!-- ACCOUNT ACTIONS -->
      <b-col md="6" xl="4" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Account Management">
          <hr />

          <b-button
            v-if="userDetail.status === 'active'"
            variant="danger"
            @click="suspend"
          >
            <i class="fa fa-times" /> Suspend Account
          </b-button>

          <b-button
            v-if="userDetail.status === 'suspended'"
            variant="success"
            @click="activate"
          >
            <i class="fa fa-check" /> Activate Account
          </b-button>
        </Widget>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "UserDetailsPage",
  data() {
    return {
      loadingPersonalDetails: false,
      userDetail: {},
      profile: {},
      files: {},
      user_role: userRole(),
      errorMessage: null,

      userId: this.$route.params.user,

      userData: {
        doctor_id: JSON.parse(localStorage.getItem("user")).user_id,
        id: this.$route.params.user,
      },
    };
  },

  methods: {
    loadUser(id) {
      this.loadingPersonalDetails = true;

      this.$axios
        .get(this.$base_url + "user_details/" + id, authHeader())
        .then(({ data }) => {
          this.userDetail = data.data.main_details[0];
          this.profile = data.data.profile?.[0] || {};
          this.files = data.data.files || [];
        })
        .finally(() => {
          this.loadingPersonalDetails = false;
        });
    },

    activate() {
      this.confirmAction(
        "Activate Account",
        "You are giving this user access to the system!",
        this.$base_url + "accept_user"
      );
    },

    suspend() {
      this.confirmAction(
        "Suspend Account",
        "You are suspending this user!",
        this.$base_url + "suspend_user"
      );
    },

    confirmAction(title, text, endpoint) {
      this.$swal({
        title,
        text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, continue",
      }).then((result) => {
        if (!result.value) return;

        this.$axios
          .post(endpoint, this.userData, authHeader())
          .then((res) => {
            this.$swal("Success!", res.data.message, "success");

            // ✅ reload user correctly
            this.loadUser(this.userId);
          })
          .catch((error) => {
            this.$swal(
              "Error",
              error.response?.data?.message || "Action failed",
              "error"
            );
          });
      });
    },
  },

  created() {
    this.loadUser(this.userId);
  },
};
</script>
