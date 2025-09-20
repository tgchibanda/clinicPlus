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
      <doctors-notes :consultation="this.userDetail"></doctors-notes>
    </b-modal>
    <h1 class="page-title">User - <span class="fw-semi-bold">Details</span></h1>
    <b-row>
      <b-col md="6" xl="4" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget
            class="h-100 mb-0"
            :fetchingData="this.loadingPersonalDetails"
          >
            <div class="d-flex flex-wrap justify-content-left">
              <span class="avatar rounded-circle thumb-xl float-left mr-2 mt-1">
                <!-- <img
                  class="rounded-circle"
                  v-bind:src="'..' + profile.upload"
                  alt="..."
                  width="150"
                /> -->
              </span>
              <div class="mt-3">
                <h4>{{ userDetail.first_name }}</h4>

                <p class="text-secondary mb-1">
                  Reg Number
                  <span class="font-weight-bold">{{
                    userDetail.reg_number
                  }}</span>
                </p>
                <p class="text-secondary mb-1">
                  {{ userDetail.email }}
                </p>
                <p class="text-muted font-size-sm">
                  <a href=""
                    >Call {{ userDetail.mobile_no }}</a
                  >
                </p>
                <div v-if="userDetail.status == 'pending'">
                  <b-button variant="success" @click="accept(row)"
                    ><span class="fa fa-check" /> Accept</b-button
                  >
                  <b-button variant="danger" @click="decline(row)"
                    ><span class="fa fa-times" /> Decline</b-button
                  >
                </div>
                <div v-if="userDetail.status_level == 3">
                  <b-button variant="primary" v-b-modal.modal-doctor-notes
                    ><span class="fa fa-file-word-o" /> Add Doctors
                    notes</b-button
                  >
                </div>
              </div>
            </div>
          </Widget>
        </div>
      </b-col>

      <b-col md="6" xl="4" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Address">
            {{ userDetail.unit_number }}<br />
            {{ userDetail.street_name }}<br />
            {{ userDetail.suburb }}<br />
            {{ userDetail.city }}<br />
          </Widget>
        </div>
      </b-col>
      <b-col md="6" xl="4" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget
            class="h-100 mb-0"
            title="Practice data"
            :fetchingData="this.loadingfiles"
          >
            <div class="table-responsive">
              <table class="table table-striped table-lg mb-0 requests-table">
                <thead>
                  <tr class="text-muted">
                    <th>Date</th>
                    <th>File Name</th>
                    <th>Download</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in files" :key="row.id">
                    <td>{{ row.created_at }}</td>
                    <td>{{ row.description }}</td>
                    <td>{{ row.upload_name }}</td>

                    <td>
                      <b-button variant="primary" @click="download(row)"
                        ><span class="fa fa-download" />
                      </b-button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </Widget>
        </div>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "DrugPage",
  data() {
    return {
      errorMessage: null,
      loadingPersonalDetails: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      userDetail: {},
      profile: {},
      files: {},
      user_role: userRole(),
      loadingfiles: false,
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
        .get(this.$base_url + "walk_in_patient_details/" + id + "/drug-details", authHeader())
        .then(({ data }) => {
          this.loadingPersonalDetails = false;
          this.userDetail = data.data.main_details[0];
          this.profile = data.data.profile[0];
          this.files = data.data.files;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },

    
  },
  created() {
    this.loadUser(this.$route.params.patient);
  },
};
</script>


