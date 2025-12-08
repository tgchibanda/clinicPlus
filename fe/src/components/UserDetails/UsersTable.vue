<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      size="80"
      color="#FF6700"
      text="Please wait while you are redirected"
    />

    <Widget
      title="System Users"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="loading"
    >
      <!-- Add User Button -->
      <div class="mb-3 text-right">
        <b-button variant="success" @click="goToAddUser">
          <i class="fa fa-plus"></i> Add User
        </b-button>
      </div>

      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Fullname</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>View</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in UserDetails.data" :key="row.a">
              <td>{{ row.name }}</td>
              <td>{{ row.email }}</td>
              <td>{{ row.role }}</td>
              <td>
                <b-badge
                  :variant="
                    row.status === 'pending'
                      ? 'danger'
                      : row.status === 'active'
                      ? 'success'
                      : 'info'
                  "
                  pill
                >
                  {{ row.status }}
                </b-badge>
              </td>
              <td>
                <b-button variant="primary" @click="viewDetails(row)">
                  <i class="fa fa-search-plus"></i> View Details
                </b-button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Widget>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "UsersTable",
  data() {
    return {
      loading: false,
      UserDetails: {},
      user_role: userRole(),
      isLoading: false,
    };
  },
  methods: {
    loadUsers() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "user_details", authHeader())
        .then(({ data }) => {
          this.UserDetails = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("Error!", "There was an error: " + error, "error");
        });
    },

    viewDetails(item) {
      this.$router.push({
        name: "userdetailspage",
        params: { user: item.a },
      });
    },

    goToAddUser() {
      this.$router.push({ name: "adduserpage" });
    },
  },
  created() {
    this.loadUsers();
  },
};
</script>
