<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>
      <h4>Book Consultation for {{ this.selectedPatient.fullname }}</h4>
      <b-form-group label="Medical History">
        <b-form-textarea
          v-model="patient.past_medical_history"
          id="textarea"
          placeholder="Enter reason for consultation"
          rows="3"
          max-rows="6"
          :state="'past_medical_history' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{
            "past_medical_history" in errors
              ? errors.past_medical_history[0]
              : true
          }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Reason for consultation *">
        <b-form-textarea
          v-model="patient.reason"
          id="textarea"
          placeholder="Enter reason for consultation"
          rows="3"
          max-rows="6"
          :state="'reason' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{ "reason" in errors ? errors.reason[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-form-group label="Special instruction">
        <b-form-textarea
          v-model="patient.instruction"
          id="textarea"
          placeholder="Enter any Special instruction"
          rows="3"
          max-rows="6"
          :state="'instruction' in errors ? false : null"
        ></b-form-textarea>
        <b-form-invalid-feedback>
          {{ "instruction" in errors ? errors.instruction[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>
      <b-button block type="submit" variant="primary">Submit</b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
export default {
  name: "BookConsultation",
  props: {
    selectedPatient: Object,
  },
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      currentUser: {},
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      patient: {
        past_medical_history: "",
        reason: "",
        instruction: "",
        patient_id: this.selectedPatient.id,
      },
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "consultation", this.patient, authHeader())
        .then((response) => {
          Fire.$emit("closeModalConsultation", response);
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
