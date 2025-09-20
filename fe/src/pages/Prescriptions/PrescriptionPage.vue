<template>
  <section>
    <h1 class="page-title">
      Prescription — <span class="fw-semi-bold">Details</span>
    </h1>

    <div class="row">
      <div class="col-lg-10 mx-auto">
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-start">
              <div class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width:72px;height:72px;">
                <i class="fa fa-file-text-o text-muted"></i>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center">
                  <h3 class="mb-0 mr-3">#{{ prescriptionId }}</h3>
                  <span class="badge" :class="statusClass">{{ capFirst(prescription.status) || '—' }}</span>
                </div>

                <div class="text-muted mt-2 small">
                  <span v-if="prescription.created_at">
                    <i class="fa fa-calendar-plus-o mr-1"></i>
                    Created: <span class="text-dark">{{ formatDateTime(prescription.created_at) }}</span>
                  </span>
                  <span v-if="prescription.updated_at" class="ml-3">
                    <i class="fa fa-refresh mr-1"></i>
                    Updated: <span class="text-dark">{{ formatDateTime(prescription.updated_at) }}</span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Patient & Doctor -->
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-body">
            <div class="row">
              <!-- Patient -->
              <div class="col-md-6">
                <h5 class="mb-2"><i class="fa fa-user mr-1"></i> Patient</h5>
                <ul class="list-unstyled small mb-0">
                  <li class="mb-1"><strong>Name:</strong> {{ patientName }}</li>
                  <li class="mb-1"><strong>Email:</strong> {{ patient.email || '—' }}</li>
                  <li class="mb-1">
                    <strong>Phone:</strong>
                    <template v-if="patient.phone">
                      <a :href="`tel:${patient.phone}`">{{ patient.phone }}</a>
                    </template>
                    <template v-else>—</template>
                  </li>
                  <li class="mb-1" v-if="patient.gender"><strong>Gender:</strong> {{ capFirst(patient.gender) }}</li>
                  <li class="mb-1" v-if="patient.date_of_birth"><strong>D.O.B:</strong> {{ formatDate(patient.date_of_birth) }}</li>
                </ul>
              </div>

              <!-- Doctor -->
              <div class="col-md-6">
                <h5 class="mb-2"><i class="fa fa-user-md mr-1"></i> Prescribing Doctor</h5>
                <ul class="list-unstyled small mb-0">
                  <li class="mb-1"><strong>Name:</strong> {{ doctor.name || '—' }}</li>
                  <li class="mb-1"><strong>Email:</strong> {{ doctor.email || '—' }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Doctor's Notes -->
        <div class="card shadow-sm rounded-3 mb-4" v-if="prescription.notes">
          <div class="card-header">
            <h5 class="mb-0"><i class="fa fa-sticky-note mr-1"></i> Doctor’s Notes</h5>
          </div>
          <div class="card-body">
            <p class="mb-0" style="white-space: pre-line;">{{ prescription.notes }}</p>
          </div>
        </div>

        <!-- Items -->
        <div class="card shadow-sm rounded-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-prescription-bottle-alt mr-1"></i> Prescribed Drugs</h5>
            <div class="text-right">
              <strong>Total:</strong> {{ formatMoney(grandTotal) }}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-lg mb-0">
                <thead>
                  <tr class="text-muted">
                    <th>#</th>
                    <th>Drug</th>
                    <th>Unit</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Qty (Prescribed)</th>
                    <th class="text-right">Qty (Dispensed)</th>
                    <th>Dosage Instructions</th>
                    <th class="text-right">Line Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(it, idx) in items" :key="it.id || idx">
                    <td>{{ idx + 1 }}</td>
                    <td>{{ (it.drug && it.drug.name) || '—' }}</td>
                    <td>{{ (it.drug && it.drug.unit) || '—' }}</td>
                    <td class="text-right">{{ formatMoney(it.unit_price) }}</td>
                    <td class="text-right">{{ it.quantity_prescribed }}</td>
                    <td class="text-right">{{ it.quantity_dispensed }}</td>
                    <td>{{ it.dosage_instructions || '—' }}</td>
                    <td class="text-right">{{ formatMoney(lineTotal(it)) }}</td>
                  </tr>
                  <tr v-if="!items.length">
                    <td colspan="8" class="text-center text-muted">No drugs on this prescription.</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between mt-3">
              <b-button variant="secondary" @click="$router.back()">
                <i class="fa fa-arrow-left mr-1"></i> Back
              </b-button>
              <b-button variant="outline-primary" @click="printPage">
                <i class="fa fa-print mr-1"></i> Print
              </b-button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <vue-element-loading
      :active="loading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Loading prescription…'"
    />
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "PrescriptionDetailsPage",
  data() {
    return {
      loading: false,
      prescription: {}, // full object from API
    };
  },
  computed: {
    // Guarded accessors to avoid optional chaining in template
    patient() {
      return this.prescription.patient || {};
    },
    doctor() {
      return this.prescription.doctor || {};
    },
    items() {
      return this.prescription.items || [];
    },
    prescriptionId() {
      return this.prescription && this.prescription.id ? this.prescription.id : "—";
    },
    grandTotal() {
      return this.items.reduce((sum, it) => {
        const q = Number(it.quantity_prescribed || 0);
        const p = Number(it.unit_price || 0);
        return sum + q * p;
      }, 0);
    },
    statusClass() {
      const s = (this.prescription.status || "").toLowerCase();
      if (s === "completed") return "badge-success";
      if (s === "pending" || s === "waiting") return "badge-warning";
      if (s === "cancelled") return "badge-danger";
      return "badge-secondary";
    },
    patientName() {
      const f = this.patient.first_name || "";
      const l = this.patient.last_name || "";
      const name = (f + " " + l).trim();
      return name || "—";
    },
  },
  methods: {
    loadPrescription(id) {
      this.loading = true;
      const url = this.$base_url + "prescriptions/" + id ;
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          this.loading = false;
          this.prescription = (data && data.data) || {};
        })
        .catch((error) => {
          this.loading = false;
          const msg =
            (error.response && error.response.data && error.response.data.message) ||
            error.message || error.toString();
          this.$swal("Error", msg, "error");
        });
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    formatMoney(v) {
      const n = Number(v || 0);
      return isNaN(n) ? "$" + v : "$" + n.toFixed(2);
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
        : d.toLocaleString("en-AU", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });
    },
    lineTotal(it) {
      const q = Number(it.quantity_prescribed || 0);
      const p = Number(it.unit_price || 0);
      return q * p;
    },
    printPage() {
      window.print();
    },
  },
  created() {
    const id = this.$route.params.id || this.$route.params.prescription;
    this.loadPrescription(id);
  },
};
</script>

<style scoped>
.avatar i { font-size: 26px; }
.badge-success { background: #28a745; }
.badge-warning { background: #ffc107; }
.badge-danger  { background: #dc3545; }
.badge-secondary { background: #6c757d; }
</style>
