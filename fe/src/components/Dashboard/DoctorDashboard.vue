<template>
  <div class="doctor-dashboard-page">
    <b-modal
      id="modal-monthly-condition"
      ref="modal-monthly-condition"
      size="lg"
      title="Monthly Condition"
      hide-footer
    >
      <h3>{{ dashboard_condition.title }}</h3>
      {{ dashboard_condition.description }}
    </b-modal>
    <div v-if="user_role.status == 'active'">
      <b-row>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0 widget-sm bg-primary text-white widget"
              :fetchingData="this.loading"
            >
              <p class="mb text-light">
                <time>{{
                  dashboard_condition.created_at
                    | moment("YYYY-MM-DD h:mm:ss a")
                }}</time>
              </p>
              <a href="#" class="read-link" v-b-modal.modal-monthly-condition>
                <h3>
                  {{ dashboard_condition.title }}
                </h3>
                <p class="fs-mini mt">
                  <span class="fw-semi-bold">Read</span> More &nbsp;
                </p>
              </a>
            </Widget>
          </div>
        </b-col>
        <b-col md="6" xl="3" sm="6" xs="12">
          <div class="pb-xlg h-100">
            <Widget
              class="h-100 mb-0"
              title="New Patients Today"
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
                    <h6 class="m-0">Pending Consultations</h6>
                    <p class="h2 m-0 fw-normal">
                      {{ dashboard_data.openConsultations | number }}
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
              title="Past attendances"
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
                        <h6 class="m-0">This Month</h6>
                        <p class="h2 m-0 fw-normal">
                          {{ dashboard_data.pastAttendances }}
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
                    <h6 class="m-0">Profit</h6>
                    <p class="h2 m-0 fw-normal">
                      {{ dashboard_data.amount_net | currency }}
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
                    <h6 class="m-0">Total Amount</h6>
                    <p class="value5">
                      {{ dashboard_data.amount_gross | currency }}
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
          <consultation-table></consultation-table>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";
import userRole from "../../services/user-role";
import authHeader from "../../services/auth-header";

export default {
  name: "DoctorDashboard",
  components: {
    Widget,
  },
  data() {
    return {
      dashboard_data: {},
      dashboard_condition: {},
      user_role: userRole(),
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      loading: false,
    };
  },
  methods: {
    loadMonthlyConditions() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "getMonthlyCondition", authHeader())
        .then(({ data }) => {
          this.loading = false;
          this.dashboard_condition = data.data;
          // console.log(data.data);
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
    loadDashboardData() {
      this.loading = true;
      this.$axios
        .get(`${this.$base_url}dashboard/${this.user_id}/doctor`, authHeader())
        .then(({ data }) => {
          this.loading = false;
          this.dashboard_data = data.data;
          //console.log(data.data);
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
  },
  created() {
    this.loadDashboardData();
    this.loadMonthlyConditions();
  },
};
</script>
