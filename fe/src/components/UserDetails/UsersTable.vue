<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Please wait while you are redirected'"
    />
    <b-modal id="modal-map" size="lg" title="Patient Location">
      <!-- <book-consultation></book-consultation> -->
      <!-- <patient-details></patient-details> -->
    </b-modal>
    <Widget
      title="<h5>System <span class='fw-semi-bold'>Users</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="this.loading"
    >
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>Fullname</th>
              <th>Email</th>
              <th>Role</th>
              <th>status</th>
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
                    row.status == 'pending'
                      ? 'danger'
                      : row.status == 'active'
                      ? 'success'
                      : 'info'
                  "
                  pill
                >
                  {{ row.status }}
                </b-badge>
              </td>
              <td>
                <b-button variant="primary" @click="viewDetails(row)"
                  ><span class="fa fa-search-plus" /> View Details</b-button
                >
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
      errorMessage: null,
      loading: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      UserDetails: {},
      timer: "",
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
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    viewDetails(item) {
      this.$router.push({ name: "userdetailspage", params: { user: item.a } });
    },

  },
  created() {
    this.loadUsers();
  }

};
</script>

