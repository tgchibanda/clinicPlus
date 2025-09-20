<template>
  <section>
    <!-- Header -->
    <div class="d-flex align-items-center mb-3">
      <i class="fas fa-cash-register mr-2"></i>
      <h4 class="mb-0">
        Process Prescription Payment
      </h4>
    </div>

    <b-row>
      <!-- Left: Summary -->
      <b-col md="4" class="mb-3">
        <b-card class="shadow-sm rounded-3">
          <h5 class="mb-3">Prescription Summary</h5>

          <div class="d-flex align-items-start mb-3">
            <div
              class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
              style="width:56px;height:56px;"
            >
              <i class="fa fa-user text-muted"></i>
            </div>
            <div>
              <div class="text-muted">Prescription</div>
              <div class="h6 mb-1">#{{ prescription.id || '—' }}</div>
              <div class="text-muted">Created</div>
              <div>{{ dateTime(prescription.created_at) }}</div>
            </div>
          </div>

          <hr class="my-3" />

          <div class="mb-2 text-muted">Patient</div>
          <div class="h6 mb-1">{{ patientName }}</div>
          <div class="small text-muted" v-if="prescription.patient && prescription.patient.email">
            {{ prescription.patient.email }}
          </div>
          <div class="small text-muted" v-if="prescription.patient && prescription.patient.phone">
            {{ prescription.patient.phone }}
          </div>

          <hr class="my-3" />

          <div class="mb-2 text-muted">Doctor</div>
          <div class="h6 mb-0">{{ prescription.doctor ? prescription.doctor.name : '—' }}</div>
        </b-card>
      </b-col>

      <!-- Right: Items + Payment -->
      <b-col md="8" class="mb-3">
        <b-card class="shadow-sm rounded-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="mb-0">Items to Pay</h5>
            <b-badge variant="secondary">{{ form.items.length }} item(s)</b-badge>
          </div>

          <b-alert variant="info" show class="mb-3" v-if="!form.items.length">
            No payable items found on this prescription.
          </b-alert>

          <!-- Items table -->
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-3">
              <thead class="thead-light">
                <tr>
                  <th style="min-width:260px;">Drug & Dosage</th>
                  <th class="text-center">Prescribed</th>
                  <th class="text-center">Dispensed</th>
                  <th class="text-center">Remaining</th>
                  <th class="text-center" style="min-width:110px;">Qty to Pay</th>
                  <th class="text-right" style="min-width:130px;">Unit Price</th>
                  <th class="text-right" style="min-width:140px;">Line Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, idx) in form.items" :key="row.uid">
                  <!-- Drug + dosage -->
                  <td>
                    <div class="font-weight-600">
                      {{ row.drug_name || '—' }}
                    </div>
                    <div class="small text-muted">
                      {{ row.dosage_instructions || '—' }}
                    </div>
                  </td>

                  <!-- Prescribed / Dispensed / Remaining -->
                  <td class="text-center">{{ row.quantity_prescribed }}</td>
                  <td class="text-center">{{ row.quantity_dispensed }}</td>
                  <td class="text-center">
                    <b-badge :variant="row.remaining > 0 ? 'warning' : 'success'">
                      {{ row.remaining }}
                    </b-badge>
                  </td>

                  <!-- Quantity to pay (editable up to remaining) -->
                  <td class="text-center">
                    <b-form-input
                      type="number"
                      min="0"
                      :max="row.remaining || 0"
                      v-model.number="row.quantity"
                      @input="recalcLine(idx)"
                      class="text-center"
                    />
                  </td>

                  <!-- Unit Price -->
                  <td class="text-right">{{ formatMoney(row.unit_price) }}</td>

                  <!-- Line total -->
                  <td class="text-right">{{ formatMoney(lineTotal(row)) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Payment method -->
          <b-row class="mb-3">
            <b-col md="6">
              <b-form-group label="Payment Method *">
                <b-form-select
                  v-model="form.payment_method"
                  @change="onPaymentMethodChange"
                  required
                >
                  <b-form-select-option value="cash">Cash</b-form-select-option>
                  <b-form-select-option value="voucher">Voucher</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </b-col>
            <b-col md="6" v-if="form.payment_method === 'voucher'">
              <b-form-group label="Voucher Code">
                <b-form-input v-model="form.voucher_code" required />
              </b-form-group>
            </b-col>
          </b-row>

          <b-alert variant="danger" :show="!!errorMessage" class="alert-sm mb-3">
            {{ errorMessage }}
          </b-alert>

          <!-- Totals + Actions -->
          <div class="d-flex align-items-center justify-content-between">
            <div class="h5 mb-0">
              Total Amount: <span class="font-weight-bold">{{ formatMoney(grandTotal) }}</span>
            </div>
            <div>
              <b-button variant="secondary" class="mr-2" @click="goBack">Cancel</b-button>
              <b-button
                type="button"
                variant="success"
                :disabled="submitting || !form.items.length || grandTotal <= 0"
                @click="submitSale"
              >
                <b-spinner small v-if="submitting" class="mr-1" /> Process Payment
              </b-button>
            </div>
          </div>
        </b-card>
      </b-col>
    </b-row>

    <!-- Full-screen loader -->
    <vue-element-loading
      :active="loading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Loading…'"
    />
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";

var uid = 1;
function mapLineFromItem(it) {
  // Compute remaining safely from fields available
  var prescribed = Number(it.quantity_prescribed || 0);
  var dispensed = Number(it.quantity_dispensed || 0);
  var remaining = prescribed - dispensed;
  if (remaining < 0) remaining = 0;
  var price = Number(it.unit_price || (it.drug && it.drug.selling_price) || 0);

  return {
    uid: uid++,
    drug_id: it.drug_id,
    drug_name: (it.drug && it.drug.name) || "",
    dosage_instructions: it.dosage_instructions || "",
    quantity_prescribed: prescribed,
    quantity_dispensed: dispensed,
    remaining: remaining,          // shown in UI and used as max
    quantity: remaining,           // default to pay all remaining
    unit_price: price,
  };
}

export default {
  name: "ProcessSalePage",
  data() {
    return {
      loading: false,
      submitting: false,
      errorMessage: null,

      prescription: {},

      form: {
        patient_id: "",
        prescription_id: null,
        payment_method: "cash",
        voucher_code: "",
        items: [], // mapped from prescription.items
      },
    };
  },
  computed: {
    patientName() {
      var p = this.prescription.patient || {};
      var name = ((p.first_name || "") + " " + (p.last_name || "")).trim();
      return name || "—";
    },
    grandTotal() {
      var sum = 0;
      for (var i = 0; i < this.form.items.length; i++) {
        sum += this.lineTotal(this.form.items[i]);
      }
      return sum;
    },
  },
  methods: {
    // Navigation
    goBack() {
      this.$router.back();
    },

    // Load prescription; redirect back if missing id
    loadPrescription(id) {
      if (!id) {
        this.goBack();
        return;
      }
      var _this = this;
      _this.loading = true;
      var url = this.$base_url + "prescriptions/" + id;

      this.$axios
        .get(url, authHeader())
        .then(function(resp) {
          _this.prescription = (resp && resp.data && resp.data.data) || {};
          if (!_this.prescription || !_this.prescription.id) {
            _this.goBack();
            return;
          }
          _this.form.prescription_id = _this.prescription.id;
          _this.form.patient_id = _this.prescription.patient_id || "";

          // Map only items that have something remaining (or at least 0 to view)
          var src = (_this.prescription.items || []).map(mapLineFromItem);
          // keep only lines with remaining > 0 (payable)
          src = src.filter(function(r) { return Number(r.remaining || 0) > 0; });
          _this.form.items = src;
        })
        .catch(function(e) {
          _this.errorMessage =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
          // On hard error, bounce back
          _this.goBack();
        })
        .finally(function() {
          _this.loading = false;
        });
    },

    // math helpers
    recalcLine(idx) {
      var row = this.form.items[idx];
      if (!row) return;
      if (row.quantity < 0) row.quantity = 0;
      var max = Number(row.remaining || 0);
      if (max && row.quantity > max) row.quantity = max;
    },
    lineTotal(row) {
      var q = Number(row.quantity || 0);
      var p = Number(row.unit_price || 0);
      return q * p;
    },
    formatMoney(v) {
      var n = Number(v || 0);
      if (isNaN(n)) return "$0.00";
      return "$" + n.toFixed(2);
    },

    onPaymentMethodChange() {
      if (this.form.payment_method !== "voucher") this.form.voucher_code = "";
    },

    // Submit
    submitSale() {
      // Validate there is at least one payable line
      var hasQty = this.form.items.some(function(r) { return Number(r.quantity || 0) > 0; });
      if (!hasQty) {
        this.$swal("Validation", "Please enter a quantity to pay for at least one item.", "warning");
        return;
      }

      var payload = {
        patient_id: this.form.patient_id,
        prescription_id: this.form.prescription_id,
        pharmacist_id: JSON.parse(localStorage.getItem("user")).user_id,
        payment_method: this.form.payment_method,
        voucher_code: this.form.payment_method === "voucher" ? this.form.voucher_code : null,
        items: this.form.items
          .filter(function(r) { return Number(r.quantity || 0) > 0; })
          .map(function(r) {
            return {
              drug_id: r.drug_id,
              quantity: Number(r.quantity || 0),
              unit_price: Number(r.unit_price || 0),
            };
          }),
        total_amount: this.grandTotal,
      };

      var _this = this;
      this.submitting = true;
      this.$axios
        .post(this.$base_url + "sales", payload, authHeader())
        .then(function() {
          _this.submitting = false;
          _this.$swal("Success", "Payment processed.", "success");
          _this.$router.push({ name: "prescriptions" });
        })
        .catch(function(e) {
          _this.submitting = false;
          _this.errorMessage =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
        });
    },

    // date helper
    dateTime(val) {
      if (!val) return "—";
      var d = new Date(val);
      if (isNaN(d.getTime())) return val;
      return d.toLocaleString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },
  },
  created() {
    // Only prescription mode; go back if none
    var prescriptionId =
      this.$route.params.id ||
      this.$route.params.prescription ||
      this.$route.params.prescriptionId;

    if (!prescriptionId) {
      this.goBack();
      return;
    }
    this.loadPrescription(prescriptionId);
  },
};
</script>

<style scoped>
.font-weight-600 { font-weight: 600; }
/* tighten inputs in table rows a bit */
.table td .form-control {
  height: 34px;
  padding: 4px 8px;
  font-size: 0.875rem;
}
</style>
