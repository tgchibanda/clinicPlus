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
              <b-form-group label="Primary Payment Option *">
                <b-form-select
                  v-model="form.payment_selector"
                  @change="onPaymentSelectorChange"
                  required
                >
                  <b-form-select-option value="self">Pay directly (cash / bank / ecocash / mixed)</b-form-select-option>
                  <b-form-select-option value="policy">Pay with policy / insurance</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </b-col>

            <!-- When paying directly: choose method -->
            <b-col md="6" v-if="form.payment_selector === 'self'">
              <b-form-group label="Payment Method *">
                <b-form-select
                  v-model="form.payment_method"
                  @change="onPaymentMethodChange"
                  required
                >
                  <b-form-select-option value="cash">Cash</b-form-select-option>
                  <b-form-select-option value="bank_transfer">Bank Transfer</b-form-select-option>
                  <b-form-select-option value="ecocash">Ecocash</b-form-select-option>
                  <b-form-select-option value="mixed">Mixed (describe)</b-form-select-option>
                </b-form-select>
              </b-form-group>
            </b-col>

            <!-- Mixed description -->
            <b-col md="12" v-if="form.payment_selector === 'self' && form.payment_method === 'mixed'">
              <b-form-group label="Payment description (for Mixed)">
                <b-form-input v-model="form.payment_description" placeholder="e.g. split between cash and transfer" />
              </b-form-group>
            </b-col>

            <!-- Policy flow -->
          
            <b-col md="12" v-if="form.payment_selector === 'policy'">
              <b-row>
                <b-col md="8">
                  <b-form-group label="Policy / Subscription number">
                    <b-form-input v-model="policyNumber" placeholder="Enter policy number" />
                  </b-form-group>
                </b-col>
                <b-col md="4" class="d-flex align-items-end">
                  <b-button :disabled="!policyNumber || verifyingPolicy" variant="primary" @click="verifyPolicy">
                    {{ verifyingPolicy ? 'Verifying…' : 'Verify Policy' }}
                  </b-button>
                </b-col>
              </b-row>

              <div v-if="policyError" class="text-danger small mb-2">{{ policyError }}</div>

              <div v-if="policy && policy.id" class="">
                <div><strong>Policy #{{ policy.id }}</strong> — {{ policy.plan || '' }}</div>
                <div class="small text-muted">Owner: {{ policy.patient_name }} • Balance: {{ formatMoney(policy.balance) }}</div>

                <b-row class="mt-2">
                  <b-col md="6">
                    <b-form-group label="Amount to apply from policy">
                      <b-form-input v-model.number="amountFromPolicy" type="number" :max="policy.balance" :min="0" step="0.01" :disabled="true" />
                      <small class="text-muted">Readonly — you may adjust policy via backend. Default: min(balance, total)</small>
                    </b-form-group>
                  </b-col>

                  <b-col md="6">
                    <b-form-group label="Remaining to pay">
                      <b-form-input :value="formatMoney(remainingAfterPolicy)" disabled />
                    </b-form-group>
                  </b-col>
                </b-row>

                <!-- If remainder > 0 allow user to pick a secondary payment -->
                <div v-if="remainingAfterPolicy > 0" class="mt-2">
                  <b-row>
                    <b-col md="6">
                      <b-form-group label="Secondary payment method">
                        <b-form-select
                          v-model="form.secondary_payment_method"
                          @change="onSecondaryPaymentMethodChange"
                        >
                          <b-form-select-option :value="null" disabled>Select</b-form-select-option>
                          <b-form-select-option value="cash">Cash</b-form-select-option>
                          <b-form-select-option value="bank_transfer">Bank Transfer</b-form-select-option>
                          <b-form-select-option value="ecocash">Ecocash</b-form-select-option>
                          <b-form-select-option value="mixed">Mixed (describe)</b-form-select-option>
                        </b-form-select>
                      </b-form-group>
                    </b-col>

                    <b-col md="6" v-if="form.secondary_payment_method === 'mixed'">
                      <b-form-group label="Secondary payment description">
                        <b-form-input v-model="form.payment_description_secondary" placeholder="Describe mixed payment split" />
                      </b-form-group>
                    </b-col>
                  </b-row>
                </div>
              </div>
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
                :disabled="submitting || !form.items.length || grandTotal <= 0 || !canProcess"
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
    remaining: remaining,
    quantity: remaining,
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
        consultation_id: null,
        // payment_selector: 'self' or 'policy'
        payment_selector: "self",
        // primary for self-pay
        payment_method: "",
        payment_description: "",
        // secondary when policy used
        secondary_payment_method: null,
        payment_description_secondary: "",
        items: [],
      },

      // policy verification state
      policyNumber: "",
      policy: null, // { id, subscription_id?, balance, patient_name, plan }
      verifyingPolicy: false,
      policyError: null,
      amountFromPolicy: 0,
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
    remainingAfterPolicy() {
      // how much remains after deducting policy portion
      var apply = Number(this.amountFromPolicy || 0);
      var rem = Number(this.grandTotal || 0) - apply;
      return rem > 0 ? Number(rem.toFixed(2)) : 0;
    },
    canProcess() {
      // Ensure selector specific requirements met
      if (this.form.payment_selector === "self") {
        if (!this.form.payment_method) return false;
        if (this.form.payment_method === "mixed" && !this.form.payment_description) return false;
        return true;
      } else {
        // policy selected
        if (!this.policy || !this.policy.id) return false;
        // policy amount must be set (we set default) and not greater than balance
        if (!(Number(this.amountFromPolicy) >= 0)) return false;
        if (Number(this.amountFromPolicy) > Number(this.policy.balance || 0)) return false;
        // if remainder exists, a secondary payment method must be chosen
        if (this.remainingAfterPolicy > 0) {
          if (!this.form.secondary_payment_method) return false;
          if (this.form.secondary_payment_method === "mixed" && !this.form.payment_description_secondary) return false;
        }
        return true;
      }
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
          _this.form.consultation_id = _this.prescription.consultation.id;
          _this.form.patient_id = _this.prescription.patient_id || "";

          var src = (_this.prescription.items || []).map(mapLineFromItem);
          src = src.filter(function(r) { return Number(r.remaining || 0) > 0; });
          _this.form.items = src;
        })
        .catch(function(e) {
          _this.errorMessage =
            (e.response && e.response.data && e.response.data.message) ||
            e.message ||
            e.toString();
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

    onPaymentSelectorChange() {
      // reset policy-related fields when switching to self
      if (this.form.payment_selector === "self") {
        this.policy = null;
        this.policyNumber = "";
        this.policyError = null;
        this.amountFromPolicy = 0;
        this.form.secondary_payment_method = null;
        this.form.payment_description_secondary = "";
      }
    },

    onPaymentMethodChange() {
      if (this.form.payment_method !== "mixed") this.form.payment_description = "";
    },

    onSecondaryPaymentMethodChange() {
      if (this.form.secondary_payment_method !== "mixed") this.form.payment_description_secondary = "";
    },

    // verify policy endpoint — adjust URL if your backend route differs
    verifyPolicy() {
      this.policyError = null;
      this.policy = null;

      if (!this.policyNumber) {
        this.policyError = "Please enter a policy/subscription number to verify.";
        return;
      }

      this.verifyingPolicy = true;
      const url = (this.$base_url ? this.$base_url : "") + "subscription/verify-by-policy?policy_number=" + encodeURIComponent(this.policyNumber);

      this.$axios.get(url, authHeader())
        .then(({ data }) => {
          // expect { success: true, data: { id, subscription_id?, balance, patient:{...}, plan } }
          const payload = data && (data.data || data) || {};
          // support different shapes
          const found = payload.subscription || payload || data;
          // sanitize fields
          const id = found.id || found.subscription_id || found.subscription_id;
          const balance = Number(found.balance || found.policy_balance || found.available_balance || 0);
          const patientName = (found.patient && ((found.patient.first_name || '') + ' ' + (found.patient.last_name || ''))) || (found.patient_name || '');
          const plan = (found.plan && (found.plan.name || found.plan)) || found.plan || '';

          if (!id) {
            this.policyError = "Policy not found or invalid response from server.";
            return;
          }

          this.policy = {
            id: id,
            subscription_id: found.subscription_id || id,
            balance: isNaN(balance) ? 0 : balance,
            patient_name: patientName.trim(),
            plan: plan
          };

          // default amount from policy = min(balance, grandTotal)
          const defaultApply = Math.min(Number(this.policy.balance || 0), Number(this.grandTotal || 0));
          this.amountFromPolicy = Number(defaultApply.toFixed(2));
        })
        .catch((err) => {
          this.policyError =
            (err.response && err.response.data && (err.response.data.message || err.response.data.error)) ||
            err.message ||
            "Failed to verify policy.";
        })
        .finally(() => {
          this.verifyingPolicy = false;
        });
    },

    // Submit
    submitSale() {
      // Validate there is at least one payable line
      var hasQty = this.form.items.some(function(r) { return Number(r.quantity || 0) > 0; });
      if (!hasQty) {
        this.$swal("Validation", "Please enter a quantity to pay for at least one item.", "warning");
        return;
      }

      if (!this.canProcess) {
        this.$swal("Validation", "Please complete payment details.", "warning");
        return;
      }

      var payload = {
        patient_id: this.form.patient_id,
        prescription_id: this.form.prescription_id,
        pharmacist_id: JSON.parse(localStorage.getItem("user")).user_id,
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

      // build payments + policy_claim according to selection
      var payments = [];
      var policy_claim = null;

      if (this.form.payment_selector === 'self') {
        // single payment - method could be mixed (we just send the method + description)
        payments.push({
          method: this.form.payment_method,
          amount: Number(this.grandTotal),
          description: (this.form.payment_method === 'mixed') ? (this.form.payment_description || null) : null
        });
      } else {
        // policy selected
        var fromPolicy = Number(this.amountFromPolicy || 0);
        if (fromPolicy > 0 && this.policy && (this.policy.id || this.policy.subscription_id)) {
          var subscriptionId = this.policy.subscription_id || this.policy.id;
          policy_claim = {
            subscription_id: subscriptionId,
            amount: fromPolicy,
            claim_for: {
              first_name: this.prescription.patient && this.prescription.patient.first_name ? this.prescription.patient.first_name : null,
              last_name: this.prescription.patient && this.prescription.patient.last_name ? this.prescription.patient.last_name : null,
              date_of_birth: this.prescription.patient && this.prescription.patient.date_of_birth ? this.prescription.patient.date_of_birth : null,
              relationship: 'self'
            },
            note: null
          };
        }

        var remainder = Math.max(0, this.grandTotal - fromPolicy);
        if (remainder > 0) {
          payments.push({
            method: this.form.secondary_payment_method || 'cash',
            amount: Number(remainder.toFixed(2)),
            description: (this.form.secondary_payment_method === 'mixed') ? (this.form.payment_description_secondary || null) : null
          });
        }
      }

      if (payments.length) payload.payments = payments;
      if (policy_claim) payload.policy_claim = policy_claim;

      var _this = this;
      this.submitting = true;
      this.errorMessage = null;

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
