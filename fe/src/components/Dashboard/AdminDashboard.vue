<template>
  <div class="doctor-dashboard-page">
    <div v-if="user_role.status == 'active'">
      <b-row>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0"
              title="System Users"
              :fetchingData="this.loading"
            >
              <div class="clearfix">
                <div class="row flex-nowrap">
                  <div xs="3" class="col">
                    <span class="widget-icon"
                      ><i class="fi flaticon-user text-primary"></i
                    ></span>
                  </div>
                  <div xs="9" class="col">
                    <h6 class="m-0">User Accounts</h6>
                    <p class="h2 m-0 fw-normal">{{ dashboard_data.users }}</p>
                  </div>
                </div>
                <div class="row flex-nowrap">
                  <div xs="6" class="col">
                    <h6 class="m-0">Doctor Accounts</h6>
                    <p class="value5">{{ dashboard_data.doctors }}</p>
                  </div>
                  <div xs="6" class="col">
                    <h6 class="m-0">Patients</h6>
                    <p class="value5">{{ dashboard_data.patients }}</p>
                  </div>
                </div>
              </div>
            </Widget>
          </div>
        </b-col>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0"
              title="Pending Users"
              :fetchingData="this.loading"
            >
              <div class="clearfix">
                <div class="row flex-nowrap">
                  <div xs="3" class="col">
                    <span class="widget-icon"
                      ><i class="fi flaticon-user text-danger"></i
                    ></span>
                  </div>
                  <div xs="9" class="col">
                    <h6 class="m-0">Doctor Accounts</h6>
                    <p class="h2 m-0 fw-normal">
                      {{ dashboard_data.pendingUsers | number }}
                    </p>
                  </div>
                </div>
              </div>
            </Widget>
          </div>
        </b-col>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0"
              title="Open Consultations"
              :fetchingData="this.loading"
            >
              <div class="clearfix">
                <div class="row flex-nowrap">
                  <div xs="3" class="col">
                    <span class="widget-icon"
                      ><i class="fi flaticon-magic-wand text-danger"></i
                    ></span>
                  </div>
                  <div xs="9" class="col">
                    <div class="overflow-hidden">
                      <div>
                        <h6 class="m-0">Currently</h6>
                        <p class="h2 m-0 fw-normal">
                          {{ dashboard_data.openConsultations | number }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </Widget>
          </div>
        </b-col>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0"
              title="Revenue Breakdown"
              :fetchingData="this.loading"
            >
              <div class="clearfix">
                <div class="row flex-nowrap">
                  <div xs="3" class="col">
                    <span class="widget-icon"
                      ><i class="fi flaticon-calculator text-success"></i
                    ></span>
                  </div>
                  <div xs="9" class="col">
                    <h6 class="m-0">Total Amount</h6>
                    <p class="h2 m-0 fw-normal">
                      {{ dashboard_data.amount_gross | currency }}
                    </p>
                  </div>
                </div>
                <div class="row flex-nowrap">
                  <div xs="6" class="col">
                    <h6 class="m-0">Fees</h6>
                    <p class="value5">
                      {{ dashboard_data.fees | currency }}
                    </p>
                  </div>
                  <div xs="6" class="col">
                    <h6 class="m-0">Payouts</h6>
                    <p class="value5">
                      {{ dashboard_data.amount_net | currency }}
                    </p>
                  </div>
                </div>
              </div>
            </Widget>
          </div>
        </b-col>
      </b-row>
      <b-row>
        <b-col xs="12">
          <users-table></users-table>
        </b-col>
      </b-row>

      <b-row>
        <b-col xs="12">
          <payments-table></payments-table>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";
import userRole from "../../services/user-role";
import authHeader from "../../services/auth-header";
import UsersTable from "../UserDetails/UsersTable.vue";
import PaymentsTable from "../Payments/PaymentsTable.vue";

export default {
  name: "AdminDashboard",
  components: {
    Widget,
    UsersTable,
    PaymentsTable,
  },
  data() {
    return {
      dashboard_data: {},
      user_role: userRole(),
      loading: false,
    };
  },
  methods: {
    loadDashboardData() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "dashboard/admin/admin", authHeader())
        .then(({ data }) => {
          this.loading = false;
          this.dashboard_data = data.data;
          console.log(data.data);
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
  },
  created() {
    this.loadDashboardData();
  },
};
</script>
