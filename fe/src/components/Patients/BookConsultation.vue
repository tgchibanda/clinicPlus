<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>

      <h4 class="mb-3">
        Book Consultation for {{ selectedPatient.first_name }} {{ selectedPatient.last_name }}
      </h4>

      <!-- Location -->
      <b-form-group label="Location *" description="Choose the clinic/location for this consultation.">
        <b-form-select
          v-model="form.location_id"
          :options="locationOptions"
          :disabled="loading.locations"
          required
          @change="onLocationChange"
        >
          <template #first>
            <b-form-select-option :value="''" disabled>Select location</b-form-select-option>
          </template>
        </b-form-select>
      </b-form-group>

      <!-- Doctor -->
      <b-form-group label="Doctor *" description="Doctors filtered by the selected location.">
        <b-form-select
          v-model="form.doctor_id"
          :options="doctorOptions"
          :disabled="!form.location_id || loading.doctors"
          required
          @change="onDoctorChange"
        >
          <template #first>
            <b-form-select-option :value="''" disabled>Select doctor</b-form-select-option>
          </template>
        </b-form-select>
        <small v-if="superLockMessage" class="text-warning d-block mt-1">
          <i class="fa fa-exclamation-triangle"></i> {{ superLockMessage }}
        </small>
      </b-form-group>

      <!-- Date -->
      <b-form-group label="Date *" description="Pick the appointment date.">
        <b-form-datepicker
          v-model="form.date"
          :min="minDate"
          :disabled="!form.location_id || !form.doctor_id || loading.availability"
          @input="onDateChange"
          required
        />
      </b-form-group>

      <!-- Time slots (30 min) -->
      <b-form-group label="Time *" description="Select an available 30-minute slot.">
        <div class="slot-grid">
          <b-button
            v-for="slot in slots"
            :key="slot.value"
            size="sm"
            class="slot-btn mb-2 mr-2"
            :variant="slot.value === form.time ? 'primary' : 'outline-secondary'"
            :disabled="slot.disabled"
            @click="form.time = slot.value"
          >
            {{ slot.label }}
          </b-button>
        </div>
        <small v-if="!slots.length && form.date" class="text-muted">No slots available for this date.</small>
      </b-form-group>


      <b-form-group label="Payment method *">
        <b-form-select
          v-model="form.payment_method"
          :state="'payment_method' in errors ? false : null"
        >
          <option value="" selected>Choose payment method</option>
          <option value="Cash">Cash</option>
        </b-form-select>
        <b-form-invalid-feedback>
          {{ "payment_method" in errors ? errors.payment_method[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>


      <b-form-group
        label="Consultation Fee"
        description="Optional fee charged for this consultation."
      >
        <b-input-group prepend="$">
          <b-form-input
            v-model.number="form.consultation_fee"
            type="number"
            min="0"
            step="0.01"
            placeholder="0.00"
            :state="'consultation_fee' in errors ? false : null"
          />
        </b-input-group>
        <b-form-invalid-feedback>
          {{ "consultation_fee" in errors ? errors.consultation_fee[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Patient inputs -->
      <b-form-group label="Medical History">
        <b-form-textarea
          v-model="form.past_medical_history"
          placeholder="Enter relevant past medical history"
          rows="3"
          max-rows="6"
          :state="'past_medical_history' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ "past_medical_history" in errors ? errors.past_medical_history[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Reason for consultation *">
        <b-form-textarea
          v-model="form.reason"
          placeholder="Enter reason for consultation"
          rows="3"
          max-rows="6"
          required
          :state="'reason' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ "reason" in errors ? errors.reason[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Special instruction">
        <b-form-textarea
          v-model="form.instruction"
          placeholder="Enter any special instruction"
          rows="3"
          max-rows="6"
          :state="'instruction' in errors ? false : null"
        />
        <b-form-invalid-feedback>
          {{ "instruction" in errors ? errors.instruction[0] : true }}
        </b-form-invalid-feedback>
      </b-form-group>

      <div class="d-flex justify-content-between mt-4">
        <b-button variant="secondary" @click="$emit('cancel'); $bvModal.hide('modal-consultation')">
          <i class="fa fa-times mr-1"></i> Cancel
        </b-button>

        <b-button type="submit" variant="primary" :disabled="submitting || !canSubmit">
          <b-spinner small v-if="submitting" class="mr-2" /> Submit
        </b-button>
      </div>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "BookConsultation",
  props: {
    selectedPatient: { type: Object, required: true },
  },
  data() {
    const today = new Date();
    const isoToday = today.toISOString().slice(0, 10);
    return {
      errorMessage: null,
      submitting: false,
      errors: {},
      loading: {
        locations: false,
        doctors: false,
        availability: false,
      },

      // dropdown data
      locations: [],
      doctors: [],

      // availability payload we cache from backend
      availability: {
        booked_slots: [],           // e.g. ["09:00","09:30",...]
        doctor_is_super: false,     // boolean
        locked_location_id: null,   // if super doctor already locked to a location for the day
        work_hours: { start: "08:00", end: "17:00" }, // optional override by backend
      },

      // form
      form: {
        patient_id: null,
        location_id: "",
        doctor_id: "",
        date: isoToday,
        time: "",
        payment_method: "",
        past_medical_history: "",
        reason: "",
        instruction: "",
        consultation_fee: null,
      },
      minDate: isoToday,
    };
  },
  computed: {
    locationOptions() {
      return this.locations.map(l => ({ value: l.id, text: l.name }));
    },
    doctorOptions() {
      return this.doctors.map(d => ({
        value: d.id,
        text: `${d.name} — ${d.location?.name || 'Unassigned'}` + (d.is_super_doctor ? ' (Super)' : ''),
      }));
    },
    // Build 30-min slots inside work hours, mark booked as disabled
    slots() {
      if (!this.form.date || !this.form.doctor_id) return [];
      const start = this.availability.work_hours?.start || "08:00";
      const end   = this.availability.work_hours?.end   || "17:00";

      const toMinutes = t => {
        const [h, m] = t.split(":").map(Number);
        return h * 60 + m;
      };
      const toLabel = mins => {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}`;
      };

      const booked = new Set(this.availability.booked_slots || []);
      const slots = [];

      for (let t = toMinutes(start); t < toMinutes(end); t += 30) {
        const value = toLabel(t);
        slots.push({
          value,
          label: value,
          disabled: booked.has(value) || this.superLockedWrongLocation,
        });
      }
      return slots;
    },
    // “Super doctor” lock warning if date locked to a different location
    superLockedWrongLocation() {
      return (
        this.availability.doctor_is_super === true &&
        this.availability.locked_location_id &&
        String(this.availability.locked_location_id) !== String(this.form.location_id)
      );
    },
    superLockMessage() {
      if (!this.superLockedWrongLocation) return "";
      const loc = this.locations.find(l => String(l.id) === String(this.availability.locked_location_id));
      const locName = loc ? loc.name : "another location";
      return `This super doctor is already booked at ${locName} on ${this.form.date}. They can only receive bookings at that location for the entire day.`;
    },
    canSubmit() {
      return (
        this.form.location_id &&
        this.form.doctor_id &&
        this.form.date &&
        this.form.time &&
        this.form.reason &&
        !this.superLockedWrongLocation
      );
    },
  },
  methods: {
    // Load lists
    loadLocations() {
      this.loading.locations = true;
      return this.$axios
        .get(this.$base_url + "locations", authHeader())
        .then(({ data }) => {
          this.locations = (data && data.data) || [];
        })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.locations = false));
    },
    loadDoctors() {
      if (!this.form.location_id) {
        this.doctors = [];
        return Promise.resolve();
      }
      this.loading.doctors = true;
      return this.$axios
        .get(this.$base_url + `doctors?location_id=${this.form.location_id}`, authHeader())
        .then(({ data }) => {
          this.doctors = (data && data.data) || [];
        })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.doctors = false));
    },
    loadAvailability() {
      // Expect API: GET /doctors/{id}/availability?date=YYYY-MM-DD&location_id=#
      if (!this.form.doctor_id || !this.form.date) return Promise.resolve();
      this.loading.availability = true;
      const url =
        this.$base_url +
        `doctors/${this.form.doctor_id}/availability?date=${this.form.date}&location_id=${this.form.location_id}`;
      return this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          this.availability = (data && data.data) || this.availability;
          // Reset picked time if now invalid
          if (this.form.time && this.slots.find(s => s.value === this.form.time)?.disabled) {
            this.form.time = "";
          }
        })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.availability = false));
    },

    // Events
    onLocationChange() {
      this.form.doctor_id = "";
      this.form.time = "";
      this.doctors = [];
      this.loadDoctors().then(() => this.loadAvailability());
    },
    onDoctorChange() {
      this.form.time = "";
      this.loadAvailability();
    },
    onDateChange() {
      this.form.time = "";
      this.loadAvailability();
    },

    handleSubmit() {
      if (!this.canSubmit) return;

      this.submitting = true;

      const dateStr = this.form.date; // e.g. "2025-09-22"
      const timeStr = this.form.time; // e.g. "08:00"
      const startAt = `${dateStr}T${timeStr}:00`; // → "2025-09-22T08:00:00"

       // Compute end_at (+30 minutes)
      const startDateObj = new Date(startAt);
      const endDateObj = new Date(startDateObj.getTime() + 30 * 60000);
      const endAt = endDateObj.toISOString().slice(0, 19); // "YYYY-MM-DDTHH:mm:ss"

      const payload = {
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
        patient_id: this.selectedPatient.id,
        doctor_id: this.form.doctor_id,
        location_id: this.form.location_id,
        date: dateStr,
        time: timeStr,
        start_at: startAt,
        end_at: endAt,
        payment_method: this.form.payment_method,
        past_medical_history: this.form.past_medical_history,
        reason: this.form.reason,
        instruction: this.form.instruction,
        consultation_fee: this.form.consultation_fee,
      };

      // POST to your consultations endpoint
      this.$axios
        .post(this.$base_url + "consultation", payload, authHeader())
        .then((response) => {
          this.submitting = false;
          this.$swal("Success", "Consultation booked.", "success");
          this.$emit("booked", response?.data);
          this.$bvModal.hide("modal-consultation");
          this.loadWalkInPatients();
        })
        .catch((error) => {
          this.submitting = false;
          this.handleXhrError(error);
          this.errors = error?.response?.data?.errors || {};
        });
    },

    handleXhrError(error) {
      const msg =
        (error?.response && error.response.data && error.response.data.message) ||
        error?.message ||
        error?.toString() ||
        "An error occurred";
      this.errorMessage = msg;
    },
  },
  created() {
    // seed form
    this.form.patient_id = this.selectedPatient.id;
    // fetch lists
    this.loadLocations().then(() => {
      // Optionally preselect if only one location
      if (this.locations.length === 1) {
        this.form.location_id = this.locations[0].id;
        this.loadDoctors();
      }
    });
  },
};
</script>

<style scoped>
.slot-grid {
  display: flex;
  flex-wrap: wrap;
}
.slot-btn {
  min-width: 84px;
}
</style>
