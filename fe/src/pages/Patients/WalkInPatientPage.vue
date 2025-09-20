<template>
  <section>
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
                          <a :href="`tel:${patientDetails.phone}`">{{ patientDetails.phone }}</a>
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

              <b-form-group label="Prescribed Drugs">
                <div
                  v-for="(item, idx) in prescription.drugs"
                  :key="idx"
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
                        <i class="fas fa-times"></i>
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
                  <i class="fas fa-plus"></i> Add Another Drug
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
  name: "WalkInPatientDetailsPage",
  data() {
    return {
      loadingPersonalDetails: false,
      submitting: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user_role: userRole(),
      patientDetails: {},
      drugOptions: [],
      prescription: {
        notes: "",
        drugs: [{ drug_id: "", quantity: 1, dosage_instructions: "" }],
      },
    };
  },
  computed: {
    fullName() {
      const f = this.patientDetails.first_name || "";
      const l = this.patientDetails.last_name || "";
      return `${f} ${l}`.trim() || "—";
    },
    statusClass() {
      const s = (this.patientDetails.status || "").toLowerCase();
      if (s === "completed") return "badge-success";
      if (s === "waiting" || s === "pending") return "badge-warning";
      return "badge-secondary";
    },
    drugSelectOptions() {
      return this.drugOptions.map((d) => ({ value: d.id, text: d.name }));
    },
  },
  methods: {
    loadPatient(id) {
      this.loadingPersonalDetails = true;
      this.$axios
        .get(this.$base_url + "walk_in_patient_details/" + id + "/walk-in-patient-details", authHeader())
        .then(({ data }) => {
          this.loadingPersonalDetails = false;
          this.patientDetails = data?.data || {};
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
          const list = data?.data?.drugs?.data || [];
          this.drugOptions = list.map((d) => ({
            id: d.id,
            name: d.name,
            price: Number(d.selling_price || 0),
            stock_quantity: Number(d.stock_quantity || 0),
          }));
        })
        .catch((error) => {
          this.$swal("error!", "Could not load drugs: " + error, "error");
        });
    },
    selectedDrug(idx) {
      const id = this.prescription.drugs[idx]?.drug_id;
      if (!id) return null;
      return this.drugOptions.find((d) => String(d.id) === String(id)) || null;
    },
    formatMoney(v) {
      const n = Number(v || 0);
      return isNaN(n) ? "$0.00" : "$" + n.toFixed(2);
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    formatDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" });
    },
    formatDateTime(val) {
      if (!val) return "—";
      const d = new Date(val);
      return isNaN(d.getTime())
        ? val
        : d.toLocaleString("en-AU", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
    },
    addDrug() {
      this.prescription.drugs.push({ drug_id: "", quantity: 1, dosage_instructions: "" });
    },
    removeDrug(index) {
      this.prescription.drugs.splice(index, 1);
    },
    submitPrescription() {
      if (!this.patientDetails?.id) {
        this.$swal("Error", "Patient not loaded yet.", "error");
        return;
      }
      const invalid = this.prescription.drugs.some(
        (d) => !d.drug_id || !d.quantity || !d.dosage_instructions
      );
      if (invalid) {
        this.$swal("Validation", "Please complete all drug rows.", "warning");
        return;
      }
      this.submitting = true;
      const payload = {
        patient_id: this.patientDetails.id,
        doctor_id: this.user_id,
        notes: this.prescription.notes,
        drugs: this.prescription.drugs.map((d) => ({
          drug_id: d.drug_id,
          quantity: d.quantity,
          dosage_instructions: d.dosage_instructions,
        })),
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
          const id = data?.data?.id;
          if (id) {
            this.$router.push({ name: "prescriptionpage", params: { prescription: id } });
          }
        })
        .catch((error) => {
          this.submitting = false;
          const msg =
            error.response?.data?.message || error.message || error.toString();
          this.$swal("Error", msg, "error");
        });
    },
  },
  created() {
    this.loadPatient(this.$route.params.patient);
    this.loadDrugOptions();
  },
};
</script>

<style scoped>
.avatar i { font-size: 28px; }
.badge-success { background: #28a745; }
.badge-warning { background: #ffc107; }
.badge-secondary { background: #6c757d; }

.drug-select {
  min-width: 100%;
}

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

.gap-2 > * {
  margin-right: .5rem;
  margin-bottom: .5rem;
}
</style>
