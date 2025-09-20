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
      <doctors-notes :consultation="this.consultationDetails"></doctors-notes>
    </b-modal>
    <h1 class="page-title">
      Patient - <span class="fw-semi-bold">Details</span>
    </h1>
    <b-row>
      <b-col md="6" xl="3" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget
            class="h-100 mb-0"
            :fetchingData="this.loadingPersonalDetails"
          >
            <div class="d-flex flex-wrap justify-content-left">
              <span class="avatar rounded-circle thumb-xl float-left mr-2 mt-5">
                <img
                  class="rounded-circle"
                  src="../../assets/people/default.png"
                  alt="..."
                  width="150"
                />
              </span>
              <div class="mt-3">
                <h4>{{ consultationDetails.fullname }}</h4>
                <p class="text-secondary mb-1">
                  {{ consultationDetails.status_description }}
                </p>
                <p class="text-secondary mb-1">
                  {{ consultationDetails.gender }}
                </p>
                <p class="text-secondary mb-1">
                  Date of birth {{ consultationDetails.dob }}
                </p>
                <p class="text-secondary mb-1">
                  {{ consultationDetails.email }}
                </p>
                <p class="text-muted font-size-sm">
                  <a :href="`tel:${consultationDetails.mobile_no}`"
                    >Call {{ consultationDetails.mobile_no }}</a
                  >
                </p>
                <div v-if="user_role.role == 'doctor'">
                  <div v-if="consultationDetails.status_level == 2">
                    <b-button variant="success" @click="accept(row)"
                      ><span class="fa fa-check" /> Accept</b-button
                    >
                    <b-button variant="danger" @click="decline(row)"
                      ><span class="fa fa-times" /> Decline</b-button
                    >
                  </div>
                  <div v-if="consultationDetails.status_level == 3">
                    <b-button variant="primary" v-b-modal.modal-doctor-notes
                      ><span class="fa fa-file-word-o" /> Add Doctors
                      notes</b-button
                    >
                  </div>
                </div>
              </div>
            </div>
          </Widget>
        </div>
      </b-col>

      <b-col md="6" xl="4" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Consultation Reason">
            <div class="mb-2">{{ consultationDetails.reason }}</div>
          </Widget>
        </div>
      </b-col>
      <b-col md="6" xl="5" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Medical History">
            <div class="mb-2">{{ consultationDetails.history }}</div>
            <i class="fa fa-calendar" aria-hidden="true"></i>
            {{ consultationDetails.consultation_date }}
          </Widget>
        </div>
      </b-col>
    </b-row>
    <b-row>
      <b-col
        md="6"
        xl="3"
        sm="6"
        xs="12"
        v-if="consultationDetails.examination"
      >
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Medical Examination">
            {{ consultationDetails.examination }}
          </Widget>
        </div>
      </b-col>
      <b-col md="6" xl="3" sm="6" xs="12" v-if="consultationDetails.diagnosis">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Medical Diagnosis">
            {{ consultationDetails.diagnosis }}
          </Widget>
        </div>
      </b-col>
      <b-col
        md="6"
        xl="3"
        sm="6"
        xs="12"
        v-if="consultationDetails.investigation"
      >
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Medical Investigation">
            {{ consultationDetails.investigation }}
          </Widget>
        </div>
      </b-col>
      <b-col md="6" xl="3" sm="6" xs="12" v-if="consultationDetails.management">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Medical Management">
            {{ consultationDetails.management }}
          </Widget>
        </div>
      </b-col>
    </b-row>
    <b-row>
      <b-col md="6" xl="3" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Address">
            {{ consultationDetails.address_line1 }}<br />
            {{ consultationDetails.address_line2 }}<br />
            {{ consultationDetails.address_line3 }}<br />
            <i class="fa fa-calendar" aria-hidden="true"></i>
            {{ consultationDetails.consultation_date }}
            &nbsp;&nbsp;&nbsp;
            <i class="fa fa-map-marker" aria-hidden="true"></i>
            {{ consultationDetails.city }}
          </Widget>
        </div>
      </b-col>
      <b-col md="6" xl="3" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Payment Details">
            Payment Ref {{ consultationDetails.order_number }}<br />
          </Widget>
        </div>
      </b-col>
      
    </b-row>
  </section>
</template>

<script>
import userRole from "../../services/user-role";

export default {
  name: "WalkInPatientDetails",
  data() {
    return {
      errorMessage: null,
      loadingPersonalDetails: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user_role: userRole(),
      isLoading: false,
    };
  },
  methods: {
    //
  },
};
</script>
<style src="./PatientDetails.scss" lang="scss" scoped />


