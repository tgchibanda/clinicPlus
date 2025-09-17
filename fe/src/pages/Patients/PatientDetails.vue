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
      <!-- <b-col md="6" xl="6" sm="6" xs="12">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Location Details">
            <div>
              <h1 class="mapTitle page-title"></h1>
              <div class="mapContainer">
                <GmapMap
                  :center="{ lat: -33.80672617589333, lng: 18.48931609383872 }"
                  :zoom="12"
                  style="width: 100%; height: inherit"
                >
                  <GmapMarker
                    :position="{
                      lat: -33.80672617589333,
                      lng: 18.48931609383872,
                    }"
                  />
                </GmapMap>
              </div>
            </div>
          </Widget>
        </div>
      </b-col> -->
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "PatientDetails",
  data() {
    return {
      errorMessage: null,
      loadingPersonalDetails: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      consultationDetails: {},
      user_role: userRole(),
      isLoading: false,
      consultation: {
        doctor_id: JSON.parse(localStorage.getItem("user")).user_id,
        id: this.$route.params.patient,
      },
    };
  },
  methods: {
    loadConsultation(consultationId) {
      this.loadingPersonalDetails = true;
      this.$axios
        .get(this.$base_url + "getconsultation/" + consultationId, authHeader())
        .then(({ data }) => {
          this.consultationDetails = data.data[0];
          this.loadingPersonalDetails = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    accept() {
      this.loadingPersonalDetails = true;
      this.$axios
        .post(
          this.$base_url + "accept_consultation",
          this.consultation,
          authHeader()
        )
        .then((response) => {
          this.loadingPersonalDetails = false;
          this.$swal("Success!", response.message, "success");
          this.loadConsultation(this.$route.params.patient);
        })
        .catch((error) => {
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
          this.errors = error.response.data.errors;
        });
    },
    requestForms(consultationId, formType) {
      this.loadingPersonalDetails = true;
      this.$axios({
        url: `${this.$base_url}request_form/${consultationId}/${formType}`,
        method: "GET",
        responseType: "blob", // important
      })
        .then((response) => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", `${formType}_form.pdf`);
          document.body.appendChild(link);
          link.click();

          this.loadingPersonalDetails = false;
        })
        .catch((error) => {
          this.loadingPersonalDetails = false;
          this.$swal(
            "Failed!",
            "There was something wrong in pathology " + error,
            "warning"
          );
        });
    },
    decline() {},
    closeModalDoctorNotes(doctorNotes) {
      this.$refs["modal-doctor-notes"].hide();
      this.loadConsultation(this.$route.params.patient);
      if (
        typeof doctorNotes.request_forms !== "undefined" &&
        doctorNotes.request_forms.length > 0
      ) {
        if (doctorNotes.request_forms.includes("pathology")) {
          this.requestForms(this.$route.params.patient, "pathology");
        }
        if (doctorNotes.request_forms.includes("radiology")) {
          this.requestForms(this.$route.params.patient, "radiology");
        }
        if (doctorNotes.request_forms.includes("script")) {
          this.requestForms(this.$route.params.patient, "script");
        }
        if (doctorNotes.request_forms.includes("allied")) {
          this.requestForms(this.$route.params.patient, "allied");
        }
        if (doctorNotes.request_forms.includes("specialist")) {
          this.requestForms(this.$route.params.patient, "specialist");
        }
        if (doctorNotes.request_forms.includes("imaging")) {
          this.requestForms(this.$route.params.patient, "imaging");
        }
        if (
          doctorNotes.request_forms.includes("pathology") &&
          doctorNotes.request_forms.includes("radiology") &&
          doctorNotes.request_forms.includes("script") &&
          doctorNotes.request_forms.includes("allied") &&
          doctorNotes.request_forms.includes("specialist") &&
          doctorNotes.request_forms.includes("imaging")
        ) {
          this.requestForms(this.$route.params.patient, "radiology");
          this.requestForms(this.$route.params.patient, "pathology");
          this.requestForms(this.$route.params.patient, "script");
          this.requestForms(this.$route.params.patient, "allied");
          this.requestForms(this.$route.params.patient, "specialist");
          this.requestForms(this.$route.params.patient, "imaging");
        }
      }
      this.$swal("Success!", "Doctors Notes Saved", "success");
    },
  },
  created() {
    this.loadConsultation(this.$route.params.patient);
    Fire.$on("closeModalDoctorNotes", (doctorNotes) => {
      this.closeModalDoctorNotes(doctorNotes);
    });
  },
};
</script>
<style src="./PatientDetails.scss" lang="scss" scoped />


