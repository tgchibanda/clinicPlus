<template>
  <section>
    <<b-modal
  id="modal-doctor-notes"
  ref="modal-doctor-notes"
  size="lg"
  title="Doctor's notes"
  hide-footer
>
  <doctors-notes
    :consultation="currentConsultation"
    :patient-details="patientDetails"
    @saved="closeModalDoctorNotes"
  />
</b-modal>

    <h1 class="page-title">
      Patient — <span class="fw-semi-bold">Details</span>
    </h1>

    <div class="row">
      <div class="col-lg-10 mx-auto">
        <!-- Patient summary card -->
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-body">
            <div class="d-flex align-items-start">
              <div
                class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                style="width:72px;height:72px;"
              >
                <i class="fa fa-user text-muted"></i>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center">
                  <h3 class="mb-0 mr-3">
                    {{ fullName }}
                  </h3>
                  <span class="badge" :class="statusClass">
                    {{ capFirst(patientDetails.status || '—') }}
                  </span>
                </div>

                <div class="text-muted mt-1">
                  ID: <span class="text-dark">{{ patientDetails.id }}</span>
                </div>

                <div class="mt-3 row">
                  <div class="col-md-6">
                    <ul class="list-unstyled mb-0 small">
                      <li class="mb-2">
                        <i class="fa fa-venus-mars mr-1"></i>
                        <strong>Gender:</strong> {{ capFirst(patientDetails.gender) || '—' }}
                      </li>
                      <li class="mb-2">
                        <i class="fa fa-birthday-cake mr-1"></i>
                        <strong>D.O.B:</strong> {{ formatDate(patientDetails.date_of_birth) }}
                      </li>
                      <li class="mb-2">
                        <i class="fa fa-clock-o mr-1"></i>
                        <strong>Visit Date:</strong> {{ formatDateTime(patientDetails.visit_date) }}
                      </li>
                    </ul>
                  </div>

                  <div class="col-md-6">
                    <ul class="list-unstyled mb-0 small">
                      <li class="mb-2">
                        <i class="fa fa-envelope mr-1"></i>
                        <strong>Email:</strong> {{ patientDetails.email || '—' }}
                      </li>
                      <li class="mb-2">
                        <i class="fa fa-phone mr-1"></i>
                        <strong>Phone:</strong>
                        <template v-if="patientDetails.phone">
                          <a :href="'tel:' + patientDetails.phone">{{ patientDetails.phone }}</a>
                        </template>
                        <template v-else>—</template>
                      </li>
                      <li class="mb-2">
                        <i class="fa fa-credit-card mr-1"></i>
                        <strong>Payment:</strong> {{ capFirst(patientDetails.payment_method) || '—' }}
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="mt-3">
                  <b-button variant="secondary" @click="$router.back()">
                    <i class="fa fa-arrow-left mr-1"></i> Back
                  </b-button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Consultation History (one record per page) -->
        <!-- Consultation History (redesigned) -->
<div class="card shadow-sm border-0 mb-4 consult-history">
  <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
      <span class="ch-icon mr-2"><i class="fa fa-history"></i></span>
      <div>
        <h5 class="mb-0 font-weight-bold">Consultation History</h5>
        <small class="text-muted d-block">
          {{ consultations.length ? ('Showing ' + chPage + ' of ' + chTotalPages) : 'No consultations' }}
        </small>
      </div>
    </div>

    <div class="ch-pager d-none d-md-flex">
      <b-button
        size="sm"
        variant="outline-secondary"
        :disabled="chPage <= 1"
        @click="chPrev"
        class="mr-2"
      >
        <i class="fa fa-chevron-left mr-1"></i> Prev
      </b-button>
      <b-button
        size="sm"
        variant="outline-secondary"
        :disabled="chPage >= chTotalPages"
        @click="chNext"
      >
        Next <i class="fa fa-chevron-right ml-1"></i>
      </b-button>
    </div>
  </div>

  <div class="card-body pt-0">
    <b-alert v-if="chError" show variant="danger" class="alert-sm mb-3">
      {{ chError }}
    </b-alert>

    <div v-if="currentConsultation" class="ch-sheet">
      <!-- Top meta row -->
      <div class="d-flex flex-wrap align-items-center mb-3">
        <div class="d-flex align-items-center mr-3">
          <div class="ch-datepill mr-2">
            <i class="fa fa-calendar"></i>
          </div>
          <div>
            <div class="font-weight-bold">
              {{ formatDate(currentConsultation.start_at) }}
            </div>
            <small class="text-muted">
              {{ timeOnly(currentConsultation.start_at) }} – {{ timeOnly(currentConsultation.end_at) }}
            </small>
          </div>
        </div>

        <span
          class="badge ml-auto ch-status"
          :class="consultationStatusClass(currentConsultation.status)"
        >
          {{ capFirst(statusLabel(currentConsultation.status)) }}
        </span>
      </div>

      <div class="ch-divider"></div>

      <!-- Quick facts -->
      <div class="row ch-facts">
        <div class="col-md-4 mb-3">
          <div class="ch-label">Doctor</div>
          <div class="ch-value">
            {{ (currentConsultation.doctor && currentConsultation.doctor.name) || '—' }}
            <span
              v-if="currentConsultation.doctor && currentConsultation.doctor.is_super_doctor"
              class="badge badge-light border ml-2"
            >Super</span>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="ch-label">Location</div>
          <div class="ch-value">
            {{ (currentConsultation.location && currentConsultation.location.name) || '—' }}
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="ch-label">Booked By</div>
          <div class="ch-value">
            {{ (currentConsultation.creator && currentConsultation.creator.name) || '—' }}
          </div>
        </div>
      </div>

      <div class="ch-divider"></div>

      <!-- Details grid -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <div class="ch-label">Reason</div>
          <div class="ch-block prewrap">{{ currentConsultation.reason || '—' }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Instruction</div>
          <div class="ch-block prewrap">{{ currentConsultation.instruction || '—' }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Past Medical History</div>
          <div class="ch-block prewrap">{{
            ((currentConsultation.medical_history && currentConsultation.medical_history.history)
              || (currentConsultation.medical_histories && currentConsultation.medical_histories[0] && currentConsultation.medical_histories[0].history)
              || '—').toString().trimStart()
          }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Examination</div>
          <div class="ch-block prewrap">{{ currentConsultation.examination || '—' }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Diagnosis</div>
          <div class="ch-block prewrap">{{ currentConsultation.diagnosis || '—' }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Management</div>
          <div class="ch-block prewrap">{{ currentConsultation.management || '—' }}</div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="ch-label">Investigation</div>
          <div class="ch-block prewrap">{{ currentConsultation.investigation || '—' }}</div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <div v-if="!currentConsultation || !currentConsultation.diagnosis">
            <b-button variant="primary" v-b-modal.modal-doctor-notes>
              <span class="fa fa-file-word-o" /> Add Doctor's notes
            </b-button>
          </div>
        </div>
      </div>

                 


      <!-- Footer actions -->
      <div class="d-flex align-items-center justify-content-between mt-2">
                <small class="text-muted">
                  Created: {{ formatDateTime(currentConsultation.created_at) }}
                </small>

                <div class="d-flex">
                  <b-button
                    v-if="hasPrescription(currentConsultation)"
                    size="sm"
                    variant="primary"
                    class="mr-2"
                    @click="goToPrescription(currentConsultation)"
                  >
                    <i class="fa fa-prescription-bottle-alt mr-1"></i>
                    View Prescription
                  </b-button>

                  <b-button
                    size="sm"
                    variant="outline-secondary"
                    @click="reloadOne(currentConsultation)"
                  >
                    <i class="fa fa-sync-alt mr-1"></i>
                    Refresh
                  </b-button>
                </div>
              </div>
            </div>

    <div v-else class="text-center text-muted py-5">
      <i class="fa fa-info-circle mb-2 d-block" style="font-size:20px;"></i>
      No consultations to show.
    </div>
  </div>

  <!-- Compact pager for mobile -->
  <div class="card-footer bg-white border-0 pt-0 d-flex d-md-none align-items-center justify-content-between">
    <b-button size="sm" variant="outline-secondary" :disabled="chPage <= 1" @click="chPrev">
      ← Previous
    </b-button>
    <small class="text-muted">
      {{ consultations.length ? ('Page ' + chPage + ' of ' + chTotalPages) : '' }}
    </small>
    <b-button size="sm" variant="outline-secondary" :disabled="chPage >= chTotalPages" @click="chNext">
      Next →
    </b-button>
  </div>
</div>


        <!-- Prescription Card -->
        <div class="card rounded-3 shadow-sm">
          <div class="card-header">
            <h4 class="mb-0">
              <i class="fas fa-prescription-bottle-alt"></i>
              Create Prescription for {{ fullName }}
            </h4>
          </div>

          <div class="card-body">
            <b-form @submit.prevent="submitPrescription">
              <input type="hidden" name="patient_id" :value="patientDetails.id" />

              <div class="mb-3">
                <label class="form-label">Notes</label>
                <b-form-textarea
                  v-model="prescription.notes"
                  rows="3"
                  placeholder="Additional notes about the prescription..."
                />
              </div>

              <b-form-group label="Prescribe Drugs">
                <div
                  v-for="(item, idx) in prescription.drugs"
                  :key="'drug-'+idx"
                  class="drug-item mb-3"
                >
                  <div class="row align-items-start">
                    <!-- Drug select wider -->
                    <div class="col-md-7">
                      <label class="form-label small text-muted d-block mb-1">Drug</label>
                      <b-form-select
                        v-model="item.drug_id"
                        :options="drugSelectOptions"
                        required
                        class="drug-select"
                      >
                        <template #first>
                          <b-form-select-option :value="''" disabled>
                            Select Drug
                          </b-form-select-option>
                        </template>
                      </b-form-select>

                      <!-- Inline stock & price -->
                      <div v-if="selectedDrug(idx)" class="mt-2 d-flex flex-wrap gap-2">
                        <span class="badge badge-soft">
                          Stock: {{ selectedDrug(idx).stock_quantity }}
                        </span>
                        <span class="badge badge-soft">
                          Price: {{ formatMoney(selectedDrug(idx).price) }}
                        </span>
                      </div>
                    </div>

                    <div class="col-md-2">
                      <label class="form-label small text-muted d-block mb-1">Quantity</label>
                      <b-form-input
                        type="number"
                        min="1"
                        v-model.number="item.quantity"
                        placeholder="Qty"
                        required
                      />
                    </div>

                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                      <b-button
                        v-if="prescription.drugs.length > 1"
                        variant="danger"
                        size="sm"
                        class="mt-4"
                        @click="removeDrug(idx)"
                      >
                        <i class="fa fa-times"></i>
                      </b-button>
                    </div>
                  </div>

                  <!-- Dosage instructions -->
                  <div class="row mt-2">
                    <div class="col-12">
                      <label class="form-label small text-muted d-block mb-1">Dosage instructions</label>
                      <b-form-textarea
                        v-model="item.dosage_instructions"
                        placeholder="e.g., 1 tablet, 3 times a day after meals"
                        rows="2"
                        max-rows="4"
                        required
                      />
                    </div>
                  </div>

                  <hr class="my-3" v-if="idx !== prescription.drugs.length - 1" />
                </div>

                <b-button type="button" variant="secondary" size="sm" @click="addDrug">
                  <i class="fa fa-plus"></i> Add Another Drug
                </b-button>
              </b-form-group>

              <div class="d-flex justify-content-between mt-4">
                <b-button variant="secondary" @click="$router.back()">Cancel</b-button>
                <b-button type="submit" variant="primary" :disabled="submitting">
                  <b-spinner small v-if="submitting" class="mr-2" /> Create Prescription
                </b-button>
              </div>
            </b-form>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "WalkInPatientPage",
  data() {
    return {
      // patient summary
      loadingPersonalDetails: false,
      submitting: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user_role: userRole(),
      patientDetails: {},

      // prescription
      drugOptions: [],
      prescription: {
        notes: "",
        drugs: [{ drug_id: "", quantity: 1, dosage_instructions: "" }],
      },

      // consultation history
      consultations: [], // full list (we paginate one-per-page)
      chPage: 1,         // current page (1-based)
      chError: null,
      activeConsultation: null,
    };
  },
  computed: {
    fullName() {
      const f = this.patientDetails.first_name || "";
      const l = this.patientDetails.last_name || "";
      const n = (f + " " + l).trim();
      return n || "—";
    },
    statusClass() {
      const s = (this.patientDetails.status || "").toLowerCase();
      if (s === "completed") return "badge-success";
      if (s === "waiting" || s === "pending" || s === "booked") return "badge-warning";
      return "badge-secondary";
    },
    drugSelectOptions() {
  return this.drugOptions.map(d => {
    const formattedDate = d.expiry_date
      ? new Date(d.expiry_date).toLocaleDateString("en-GB", {
          day: "2-digit",
          month: "short",
          year: "numeric"
        })
      : "—";

    return {
      value: d.id,
      text: `${d.name} - Batch ${d.batch_number || "N/A"} - Expiring ${formattedDate}`
    };
  });
}
,
    // one record per page
    chTotalPages() {
      return Math.max(1, this.consultations.length);
    },
    currentConsultation() {
      if (!this.consultations || !this.consultations.length) return null;
      var idx = this.chPage - 1;
      if (idx < 0 || idx >= this.consultations.length) return null;
      return this.consultations[idx];
    },
  },
  methods: {
    /* =======================
       Data Loads
       ======================= */
    closeModalDoctorNotes() {
  // close modal
  this.$refs["modal-doctor-notes"]?.hide();
  // reload the current patient's consultations
  this.loadConsultations(this.$route.params.patient);
  this.$swal("Success!", "Doctor's notes saved", "success");
},
getPrescriptionId(c) {
      if (!c) return null;
      if (c.prescription_id) return c.prescription_id;
      if (c.prescription && c.prescription.id) return c.prescription.id;
      if (c.prescriptions && c.prescriptions.length && c.prescriptions[0].id) return c.prescriptions[0].id;
      return null;
    },
goToPrescription(c) {
      const pid = this.getPrescriptionId(c);
      if (!pid) return;
      this.$router.push({ name: "prescriptionpage", params: { prescription: pid } });
    },
loadPatient(id) {
      this.loadingPersonalDetails = true;
      this.$axios
        .get(this.$base_url + "walk_in_patient_details/" + id + "/walk-in-patient-details", authHeader())
        .then(({ data }) => {
          this.loadingPersonalDetails = false;
          this.patientDetails = (data && data.data) ? data.data : {};
        })
        .catch((error) => {
          this.loadingPersonalDetails = false;
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    loadDrugOptions() {
      this.$axios
        .get(this.$base_url + "drugs", authHeader())
        .then(({ data }) => {
          var list = (data && data.data && data.data.drugs && data.data.drugs.data) ? data.data.drugs.data : [];
          this.drugOptions = list.map(function (d) {
            return {
              id: d.id,
              name: d.name,
              batch_number: d.batch_number,
              expiry_date: d.expiry_date,
              price: Number(d.selling_price || 0),
              stock_quantity: Number(d.stock_quantity || 0),
            };
          });
        })
        .catch((error) => {
          this.$swal("error!", "Could not load drugs: " + error, "error");
        });
    },
    loadConsultations(patientId) {
      // Your chosen endpoint:
      var url = this.$base_url + "walk-in-patient/" + patientId + "/consultation_history";
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          var list = (data && data.data) ? data.data : [];
          this.consultations = list;
          this.chPage = 1;
          this.chError = null;
          this.activeConsultation = this.currentConsultation.id;
        })
        .catch((error) => {
          this.chError =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
        });

        
    },

    /* =======================
       Consultation History UI
       ======================= */
    chPrev() { if (this.chPage > 1) this.chPage -= 1; },
    chNext() { if (this.chPage < this.chTotalPages) this.chPage += 1; },
    consultationStatusClass(status) {
      var s = (status || 0);
      if (s === 4 || s === 'completed' || s === 'done') return "badge-success";
      if (s === 0 || s === 'pending' || s === 'booked') return "badge-warning";
      return "badge-secondary";
    },
    statusLabel(status) {
  if (parseInt(status) === 4) {
    return "Notes Added";
  }
  return "No Notes";
},

    hasPrescription(c) {
      
      if (!c) return false;
      if (c.prescription_id) return true;
      if (c.prescription && c.prescription.id) return true;
      if (c.prescriptions && c.prescriptions.length && c.prescriptions[0].id) return true;
      return false;
    },
    getPrescriptionId(c) {
      if (!c) return null;
      if (c.prescription_id) return c.prescription_id;
      if (c.prescription && c.prescription.id) return c.prescription.id;
      if (c.prescriptions && c.prescriptions.length && c.prescriptions[0].id) return c.prescriptions[0].id;
      return null;
      },
    goToPrescription(c) {
      const pid = this.getPrescriptionId(c);
      if (!pid) return;
      this.$router.push({ name: "prescriptionpage", params: { prescription: pid } });
    },
    reloadOne(c) {
      this.loadConsultations(this.$route.params.patient);
    },

    selectedDrug(idx) {
      var id = (this.prescription.drugs[idx] && this.prescription.drugs[idx].drug_id) || null;
      if (!id) return null;
      for (var i = 0; i < this.drugOptions.length; i++) {
        if (String(this.drugOptions[i].id) === String(id)) return this.drugOptions[i];
      }
      return null;
    },
    formatMoney(v) {
      var n = Number(v || 0);
      return isNaN(n) ? "$0.00" : "$" + n.toFixed(2);
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    formatDate(val) {
      if (!val) return "—";
      var d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" });
    },
    formatDateTime(val) {
      if (!val) return "—";
      var d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleString("en-AU", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
    },
    timeOnly(val) {
      if (!val) return "—";
      var d = new Date(val);
      if (isNaN(d.getTime())) return val;
      var hh = String(d.getHours()).padStart(2, "0");
      var mm = String(d.getMinutes()).padStart(2, "0");
      return hh + ":" + mm;
    },

    addDrug() {
      this.prescription.drugs.push({ drug_id: "", quantity: 1, dosage_instructions: "" });
    },
    removeDrug(index) {
      this.prescription.drugs.splice(index, 1);
    },
    submitPrescription() {
      if (!(this.patientDetails && this.patientDetails.id)) {
        this.$swal("Error", "Patient not loaded yet.", "error");
        return;
      }
      var invalid = this.prescription.drugs.some(function (d) {
        return !d.drug_id || !d.quantity || !d.dosage_instructions;
      });
      if (invalid) {
        this.$swal("Validation", "Please complete all drug rows.", "warning");
        return;
      }
      this.submitting = true;
      var payload = {
        patient_id: this.patientDetails.id,
        consultation_id: this.activeConsultation.id,
        doctor_id: this.user_id,
        notes: this.prescription.notes,
        drugs: this.prescription.drugs.map(function (d) {
          return {
            drug_id: d.drug_id,
            quantity: d.quantity,
            dosage_instructions: d.dosage_instructions,
          };
        }),
      };
      this.$axios
        .post(this.$base_url + "prescriptions", payload, authHeader())
        .then(({ data }) => {
          this.submitting = false;
          this.$swal("Success", "Prescription created.", "success");
          this.prescription = {
            notes: "",
            drugs: [{ drug_id: "", quantity: 1, dosage_instructions: "" }],
          };
          var id = (data && data.data && data.data.id) ? data.data.id : null;
          if (id) {
            this.$router.push({ name: "prescriptionpage", params: { prescription: id } });
          }
        })
        .catch((error) => {
          this.submitting = false;
          var msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || (error + '');
          this.$swal("Error", msg, "error");
        });
    },
  },
  created() {
    var patientId = this.$route.params.patient;
    this.loadPatient(patientId);
    this.loadDrugOptions();
    this.loadConsultations(patientId);
  },
};
</script>

<style scoped>
.consult-history .ch-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #f3f4f6;
  color: #111827;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.consult-history .ch-pager .btn {
  min-width: 92px;
}

.consult-history .ch-sheet {
  border: 1px solid #eef1f5;
  border-radius: 12px;
  padding: 18px;
  background: #fafbfc;
}

.consult-history .ch-divider {
  border-top: 1px dashed #e6e9ef;
  margin: 14px 0;
}

.consult-history .ch-label {
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: .04em;
  color: #6b7280;
  margin-bottom: 6px;
}

.consult-history .ch-value {
  font-weight: 600;
  color: #111827;
}

.consult-history .ch-block {
  background: #fff;
  border: 1px solid #eef1f5;
  border-radius: 10px;
  padding: 10px 12px;
  min-height: 44px;
}

.consult-history .ch-datepill {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #eef2ff;
  color: #3730a3;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.consult-history .ch-status.badge-success {
  background: #16a34a !important;
}
.consult-history .ch-status.badge-warning {
  background: #f59e0b !important;
  color: #111827;
}
.consult-history .ch-status.badge-secondary {
  background: #6b7280 !important;
}
.prewrap { white-space: pre-wrap; word-break: break-word; }
.avatar i { font-size: 28px; }
.badge-success { background: #28a745; }
.badge-warning { background: #ffc107; }
.badge-secondary { background: #6c757d; }

.drug-select { min-width: 100%; }

.badge-soft {
  background: #f2f4f7;
  color: #334155;
  border-radius: 9999px;
  padding: 0.25rem 0.5rem;
  font-weight: 600;
  font-size: 0.75rem;
}

.form-label.small {
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.gap-2 > * { margin-right: .5rem; margin-bottom: .5rem; }

.prewrap { white-space: pre-wrap; word-break: break-word; }

/* --- Consultation card readability tweaks --- */
.consultation-card {
  border: 1px solid #eef0f3;
  border-radius: .5rem;
  background: #fff;
}
.soft-hr {
  border: 0;
  border-top: 1px solid #eef0f3;
  margin: .75rem 0 1rem;
}
.label-muted {
  text-transform: uppercase;
  color: #6b7280;
  font-size: .75rem;
  letter-spacing: .02em;
  margin-bottom: .25rem;
}
.value-strong {
  font-weight: 600;
  color: #111827;
}
</style>
