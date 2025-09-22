<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>

      <h4>Doctor’s notes for {{ patientName }}</h4>

      <b-form-group label="Examination *">
        <b-form-textarea
          v-model.trim="form.examination"
          placeholder="Enter examination findings"
          rows="3"
          max-rows="6"
          :state="'examination' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.examination ? errors.examination[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Diagnosis">
        <b-form-textarea
          v-model.trim="form.diagnosis"
          placeholder="Enter diagnosis"
          rows="3"
          max-rows="6"
          :state="'diagnosis' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.diagnosis ? errors.diagnosis[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Investigation">
        <b-form-textarea
          v-model.trim="form.investigation"
          placeholder="Enter investigations"
          rows="3"
          max-rows="6"
          :state="'investigation' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.investigation ? errors.investigation[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Management">
        <b-form-textarea
          v-model.trim="form.management"
          placeholder="Enter management plan"
          rows="3"
          max-rows="6"
          :state="'management' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ errors.management ? errors.management[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-button block type="submit" variant="primary" :disabled="submitting">
        <b-spinner small v-if="submitting" class="mr-2" /> Submit
      </b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "DoctorsNotes",
  props: {
    consultation: { type: Object, required: true },
    // optional for header display
    patientDetails: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      submitting: false,
      errorMessage: null,
      errors: {},
      form: {
        examination: "",
        diagnosis: "",
        investigation: "",
        management: "",
      },
    };
  },
  computed: {
    consultationId() {
      return this.consultation?.id;
    },
    patientName() {
      const f = this.patientDetails?.first_name || "";
      return f || "patient";
    },
  },
  methods: {
    handleSubmit() {
      this.errorMessage = null;
      this.errors = {};

      if (!this.consultationId) {
        this.errorMessage = "Missing consultation reference.";
        return;
      }
      if (!this.form.examination?.trim()) {
        this.errors = { examination: ["Examination is required"] };
        return;
      }

      const payload = {
        consultation_id: this.consultationId,
        examination: this.form.examination,
        diagnosis: this.form.diagnosis || null,
        investigation: this.form.investigation || null,
        management: this.form.management || null,
      };

      this.submitting = true;
      this.$axios
        .post(this.$base_url + "doctors_notes", payload, authHeader())
        .then(({ data }) => {
          // emit both via event bus (to match your existing handler) and a component event
          const returned = data?.data || payload;
          if (window.Fire) {
            Fire.$emit("closeModalDoctorNotes", returned);
          }
          this.$emit("saved", returned);
        })
        .catch((error) => {
          const msg =
            error?.response?.data?.message ||
            error?.message ||
            error?.toString() ||
            "Failed to save notes";
          this.errorMessage = msg;
          this.errors = error?.response?.data?.errors || {};
        })
        .finally(() => {
          this.submitting = false;
        });
    },
  },
};
</script>
