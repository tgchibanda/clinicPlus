<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <h4>Doctors notes for {{ this.consultation.fullname }}</h4>
      <b-form-group label="Examination *">
        <b-form-textarea
          v-model="consultationObject.examination"
          id="textarea"
          placeholder="Enter reason for Examination"
          rows="3"
          max-rows="6"
          :state="'examination' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{
            "examination" in errors
              ? errors.examination[0]
              : true
          }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Diagnosis">
        <b-form-textarea
          v-model="consultationObject.diagnosis"
          id="textarea"
          placeholder="Enter reason for Diagnosis"
          rows="3"
          max-rows="6"
          :state="'diagnosis' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{ "diagnosis" in errors ? errors.diagnosis[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Investigation">
        <b-form-textarea
          v-model="consultationObject.investigation"
          id="textarea"
          placeholder="Enter reason for investigation"
          rows="3"
          max-rows="6"
          :state="'investigation' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{ "investigation" in errors ? errors.investigation[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Management">
        <b-form-textarea
          v-model="consultationObject.management"
          id="textarea"
          placeholder="Enter Management"
          rows="3"
          max-rows="6"
          :state="'management' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{ "management" in errors ? errors.management[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group   label="Generate forms">
        <b-form-checkbox-group v-model="consultationObject.request_forms">
          <b-form-checkbox value="pathology">Generate Pathology Form</b-form-checkbox>
          <b-form-checkbox value="radiology">Generate Radiology Form</b-form-checkbox>
          <b-form-checkbox value="script">Generate Script Pad</b-form-checkbox>
          <b-form-checkbox value="allied">Allied Health Form</b-form-checkbox>
          <b-form-checkbox value="specialist">Specialist Referral</b-form-checkbox>
          <b-form-checkbox value="imaging">Imaging Request Form</b-form-checkbox>
        </b-form-checkbox-group>
      </b-form-group>
      <b-button block type="submit" variant="primary">Submit</b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "DoctorsNotes",
  props: {
    consultation: Object,
  },
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      consultationObject: {
        examination: "",
        diagnosis: "",
        investigation: "",
        management: "",
        consultation_id: this.consultation.consultation_id,
        request_forms: []
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "doctors_notes", this.consultationObject, authHeader())
        .then((response) => {
          Fire.$emit("closeModalDoctorNotes", this.consultationObject);
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
  },
  created() {
    this.isLoading = true;
    // this.loadCurrentUser();
  },
};
</script>
