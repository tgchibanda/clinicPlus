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

      <!-- Payment selector -->
      <b-form-group label="Payment method *">
        <b-form-radio-group v-model="form.payment_selector" buttons stacked>
          <b-form-radio value="self">Pay normally (cash / bank / ecocash / mixed)</b-form-radio>
          <b-form-radio value="insurance">Use insurance policy</b-form-radio>
        </b-form-radio-group>
      </b-form-group>

      <!-- Self-pay -->
      <div v-if="form.payment_selector === 'self'">
        <b-form-group label="Payment Type">
          <b-form-select v-model="form.payment_method" :options="paymentTypesOptions">
            <template #first>
              <b-form-select-option :value="''" disabled>Select payment type</b-form-select-option>
            </template>
          </b-form-select>
        </b-form-group>

        <b-form-group v-if="form.payment_method === 'mixed'">
          <label class="small">Payment description (for mixed)</label>
          <b-form-input v-model="form.payment_description" placeholder="Describe the mixed payment (e.g. cash + mobile transfer split)" />
        </b-form-group>
      </div>

      <!-- Insurance flow -->
      <div v-if="form.payment_selector === 'insurance'">
        <b-form-group label="Policy Number">
          <b-input-group>
            <b-form-input v-model="policySearch.number" placeholder="Enter policy / subscription number" />
            <b-input-group-append>
              <b-button size="sm" variant="primary" @click="lookupPolicy" :disabled="policyLoading">
                {{ policyLoading ? 'Looking up…' : 'Lookup' }}
              </b-button>
            </b-input-group-append>
          </b-input-group>

          <small class="text-muted d-block mt-1">Only policies with status "covered" can be used.</small>
        </b-form-group>

        <div v-if="policy">
          <b-card class="mb-2">
            <div><strong>Policy:</strong> {{ policy.policy_number ? policy.policy_number : (policy.id || '—') }}  (<small class="text-muted">{{ (policy.plan) ? policy.plan : '—' }}</small>)</div>
            <div class="small text-muted">Owner: {{ policy.patient.first_name ? (policy.patient.first_name + ' ' + (policy.patient.last_name || '')) : '—' }}</div>
            <div class="small text-muted">Available balance: {{ money(policy.available_balance || policy.balance || 0) }}</div>
          </b-card>

          <b-form-group label="Amount to claim from policy (auto-calculated)">
            <b-input-group prepend="$">
              <b-form-input
                v-model.number="form.amount_from_policy"
                type="number"
                readonly
              />
            </b-input-group>
            <small class="text-muted">If policy balance is less than the consultation fee, the remainder will be paid using a secondary payment method below.</small>
          </b-form-group>

          <div v-if="remainingAfterPolicy > 0" class="mb-2">
            <b-form-group label="Remaining to pay after policy (you can choose payment method)">
              <b-input-group prepend="$">
                <b-form-input :value="money(remainingAfterPolicy)" disabled />
              </b-input-group>
            </b-form-group>

            <b-form-group label="Secondary payment type">
              <b-form-select v-model="form.secondary_payment_method" :options="paymentTypesOptions">
                <template #first>
                  <b-form-select-option :value="''" disabled>Select payment type</b-form-select-option>
                </template>
              </b-form-select>
            </b-form-group>

            <b-form-group v-if="form.secondary_payment_method === 'mixed'">
              <label class="small">Payment description (for mixed)</label>
              <b-form-input v-model="form.payment_description_secondary" placeholder="Describe the mixed payment" />
            </b-form-group>
          </div>
        </div>

        <div v-else class="text-muted">Enter a policy number and click Lookup to use insurance.</div>
      </div>

      <!-- Consultation Fee (disabled) -->
      <b-form-group label="Consultation Fee" description="Fee for this consultation (USD).">
        <b-input-group prepend="$">
          <b-form-input
            v-model.number="form.consultation_fee"
            type="number"
            min="0"
            step="0.01"
            disabled
          />
        </b-input-group>
      </b-form-group>

      <!-- Patient inputs -->
      <b-form-group label="Medical History">
        <b-form-textarea
          v-model="form.past_medical_history"
          placeholder="Enter relevant past medical history"
          rows="3"
          max-rows="6"
        />
      </b-form-group>

      <b-form-group label="Reason for consultation *">
        <b-form-textarea
          v-model="form.reason"
          placeholder="Enter reason for consultation"
          rows="3"
          max-rows="6"
          required
        />
      </b-form-group>

      <b-form-group label="Special instruction">
        <b-form-textarea
          v-model="form.instruction"
          placeholder="Enter any special instruction"
          rows="3"
          max-rows="6"
        />
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
      loading: { locations: false, doctors: false, availability: false },

      locations: [],
      doctors: [],

      availability: { booked_slots: [], doctor_is_super: false, locked_location_id: null, work_hours: { start: "08:00", end: "17:00" } },

      paymentTypesOptions: [
        { value: "cash", text: "Cash" },
        { value: "bank_transfer", text: "Bank Transfer" },
        { value: "ecocash", text: "Ecocash" },
        { value: "mixed", text: "Mixed (describe below)" },
      ],

      policySearch: { number: null },
      policy: null,
      policyLoading: false,

      form: {
        patient_id: null,
        location_id: "",
        doctor_id: "",
        date: isoToday,
        time: "",
        payment_selector: "self",
        payment_method: "",
        payment_description: "",
        amount_from_policy: 0,
        secondary_payment_method: "",
        payment_description_secondary: "",
        past_medical_history: "",
        reason: "",
        instruction: "",
        consultation_fee: 20.00,
      },

      minDate: isoToday,
    };
  },
  computed: {
    locationOptions() {
      return this.locations.map(function(l){ return { value: l.id, text: l.name }; });
    },
    doctorOptions() {
      // avoid optional chaining: compute name and location safely
      return this.doctors.map(function(d){
        var locName = (d.location && d.location.name) ? d.location.name : 'Unassigned';
        var label = d.name + ' — ' + locName;
        if (d.is_super_doctor) label += ' (Super)';
        return { value: d.id, text: label };
      });
    },
    slots() {
      if (!this.form.date || !this.form.doctor_id) return [];
      var start = (this.availability.work_hours && this.availability.work_hours.start) ? this.availability.work_hours.start : "08:00";
      var end   = (this.availability.work_hours && this.availability.work_hours.end) ? this.availability.work_hours.end : "17:00";
      var toMinutes = function(t){ var parts=t.split(':').map(Number); return parts[0]*60 + parts[1]; };
      var toLabel = function(mins){ var h=Math.floor(mins/60); var m=mins%60; return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0'); };
      var booked = new Set(this.availability.booked_slots || []);
      var arr = [];
      for (var t = toMinutes(start); t < toMinutes(end); t += 30) {
        var value = toLabel(t);
        arr.push({ value: value, label: value, disabled: !!(booked.has(value) || this.superLockedWrongLocation) });
      }
      return arr;
    },
    superLockedWrongLocation() {
      return (this.availability.doctor_is_super === true && this.availability.locked_location_id && String(this.availability.locked_location_id) !== String(this.form.location_id));
    },
    superLockMessage() {
      if (!this.superLockedWrongLocation) return "";
      var loc = this.locations.find(function(l){ return String(l.id) === String(this.availability.locked_location_id); }.bind(this));
      var locName = loc ? loc.name : "another location";
      return "This super doctor is already booked at " + locName + " on " + (this.form.date || '') + ". They can only receive bookings at that location for the entire day.";
    },
    canSubmit() {
      var base = (this.form.location_id && this.form.doctor_id && this.form.date && this.form.time && this.form.reason && !this.superLockedWrongLocation);
      if (!base) return false;
      if (this.form.payment_selector === 'self') {
        return !!this.form.payment_method;
      } else if (this.form.payment_selector === 'insurance') {
        if (!this.policy) return false;
        if (isNaN(this.form.amount_from_policy)) return false;
        return (this.remainingAfterPolicy <= 0) || (!!this.form.secondary_payment_method);
      }
      return false;
    },
    remainingAfterPolicy() {
      var fee = Number(this.form.consultation_fee || 0);
      var fromPolicy = Number(this.form.amount_from_policy || 0);
      var rem = Math.max(0, fee - fromPolicy);
      return Number(rem.toFixed(2));
    }
  },
  methods: {
    loadLocations() {
      this.loading.locations = true;
      return this.$axios.get(this.$base_url + "locations", authHeader())
        .then(({ data }) => { this.locations = (data && data.data) || []; })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.locations = false));
    },
    loadDoctors() {
      if (!this.form.location_id) { this.doctors = []; return Promise.resolve(); }
      this.loading.doctors = true;
      return this.$axios.get(this.$base_url + "doctors?location_id=" + this.form.location_id, authHeader())
        .then(({ data }) => { this.doctors = (data && data.data) || []; })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.doctors = false));
    },
    loadAvailability() {
      if (!this.form.doctor_id || !this.form.date) return Promise.resolve();
      this.loading.availability = true;
      var url = this.$base_url + "doctors/" + this.form.doctor_id + "/availability?date=" + this.form.date + "&location_id=" + this.form.location_id;
      return this.$axios.get(url, authHeader())
        .then(({ data }) => {
          this.availability = (data && data.data) || this.availability;
          if (this.form.time && this.slots.find(function(s){ return s.value === this.form.time; }.bind(this)) && this.slots.find(function(s){ return s.value === this.form.time; }.bind(this)).disabled) {
            this.form.time = "";
          }
        })
        .catch(this.handleXhrError)
        .finally(() => (this.loading.availability = false));
    },

    onLocationChange() { this.form.doctor_id = ""; this.form.time = ""; this.doctors = []; this.loadDoctors().then(()=>this.loadAvailability()); },
    onDoctorChange() { this.form.time = ""; this.loadAvailability(); },
    onDateChange() { this.form.time = ""; this.loadAvailability(); },

    lookupPolicy() {
      this.policy = null;
      if (!this.policySearch.number) { this.errorMessage = "Please enter policy number."; return; }
      this.policyLoading = true;
      this.errorMessage = null;
      var url = (this.$base_url ? this.$base_url : "") + "subscription/verify-consultation-by-policy?policy_number=" + this.policySearch.number;
      this.$axios.get(url, authHeader())
        .then(({ data }) => {
          var payload = (data && (data.subscription || data.data || data)) || null;
          var status = payload && payload.status ? String(payload.status).toLowerCase() : '';
          if (!payload || status !== 'covered') {
            this.errorMessage = 'Policy not eligible (must have status \"covered\").';
            this.policy = null;
            return;
          }
          this.policy = payload;
          var available = Number(payload.available_balance || payload.balance || 0);
          var fee = Number(this.form.consultation_fee || 0);
          this.form.amount_from_policy = Number(Math.min(available, fee).toFixed(2));
        })
        .catch((err) => {
          console.error('Policy lookup error', err);
          this.errorMessage = (err && err.response && err.response.data && err.response.data.message) || 'Failed to lookup policy.';
        })
        .finally(() => { this.policyLoading = false; });
    },
money(value) {
    const n = Number(value || 0);
    if (isNaN(n)) return "$0.00";
    return "$" + n.toFixed(2);
  },
    handleSubmit() {
      if (!this.canSubmit) return;
      this.submitting = true;
      this.errorMessage = null;

      var dateStr = this.form.date;
      var timeStr = this.form.time;
      var startAt = dateStr + "T" + timeStr + ":00";
      var startDateObj = new Date(startAt);
      var endDateObj = new Date(startDateObj.getTime() + 30 * 60000);
      var endAt = endDateObj.toISOString().slice(0,19);

      var payload = {
        user_id: JSON.parse(localStorage.getItem("user")).user_id,
        patient_id: this.selectedPatient.id,
        doctor_id: this.form.doctor_id,
        location_id: this.form.location_id,
        date: dateStr,
        time: timeStr,
        start_at: startAt,
        end_at: endAt,
        past_medical_history: this.form.past_medical_history,
        reason: this.form.reason,
        instruction: this.form.instruction,
        consultation_fee: Number(this.form.consultation_fee || 0),
      };

      var payments = [];
      var policy_claim = null;
      var fee = Number(this.form.consultation_fee || 0);

      if (this.form.payment_selector === 'self') {
        payments.push({
          method: this.form.payment_method || 'cash',
          amount: Number(fee),
          description: this.form.payment_description || null,
        });
      } else if (this.form.payment_selector === 'insurance') {
        var fromPolicy = Number(this.form.amount_from_policy || 0);
        if (fromPolicy > 0 && this.policy && (this.policy.id || this.policy.subscription_id)) {
          var subscriptionId = this.policy.id || this.policy.subscription_id;
          policy_claim = {
            subscription_id: subscriptionId,
            amount: fromPolicy,
            claim_for: {
              first_name: this.selectedPatient.first_name,
              last_name: this.selectedPatient.last_name,
              date_of_birth: this.selectedPatient.date_of_birth || null,
              relationship: 'self'
            }
          };
        }

        var remainder = Math.max(0, fee - fromPolicy);
        if (remainder > 0) {
          payments.push({
            method: this.form.secondary_payment_method || 'cash',
            amount: Number(remainder.toFixed(2)),
            description: this.form.payment_description_secondary || null,
          });
        }
      }

      if (payments.length) payload.payments = payments;
      if (policy_claim) payload.policy_claim = policy_claim;

      this.$axios.post(this.$base_url + "consultation", payload, authHeader())
        .then((resp) => {
          this.submitting = false;
          this.$swal("Success", "Consultation booked.", "success");
          this.$emit("booked", resp && resp.data ? resp.data : {});
          this.$bvModal.hide("modal-consultation");
        })
        .catch((err) => {
          this.submitting = false;
          this.handleXhrError(err);
          this.errors = err && err.response && err.response.data && err.response.data.errors ? err.response.data.errors : {};
        });
    },

    handleXhrError(error) {
      var msg = (error && error.response && error.response.data && error.response.data.message) || (error && error.message) || (error + '') || "An error occurred";
      this.errorMessage = msg;
    },
  },
  created() {
    this.form.patient_id = this.selectedPatient.id;
    this.loadLocations().then(() => {
      if (this.locations && this.locations.length === 1) {
        this.form.location_id = this.locations[0].id;
        this.loadDoctors();
      }
    });
  },
};
</script>

<style scoped>
.slot-grid { display:flex; flex-wrap:wrap; }
.slot-btn { min-width:84px; }
</style>
