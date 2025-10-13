<template>
  <section>
    <!-- Add Payment Modal -->
    <b-modal
      id="modal-add-payment"
      ref="modalAddPayment"
      title="Add Payment"
      hide-footer
      centered
    >
      <b-form @submit.prevent="submitPayment">
        <b-alert v-if="paymentError" show variant="danger" class="mb-3">
          {{ paymentError }}
        </b-alert>

        <b-form-group label="Subscription" label-for="payment-subscription">
          <b-form-input
            id="payment-subscription"
            :value="subscription.id ? '#'+subscription.id : '—'"
            disabled
          />
        </b-form-group>

        <b-form-group label="Amount (USD)" label-for="payment-amount" description="Enter the amount to apply to the subscription.">
          <b-input-group prepend="$">
            <b-form-input
              readonly
              id="payment-amount"
              v-model.number="paymentForm.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              placeholder="e.g. 50.00"
            />
          </b-input-group>
        </b-form-group>

        <b-form-group label="Payment Method" label-for="payment-method">
          <b-form-select
            id="payment-method"
            v-model="paymentForm.method"
            :options="paymentMethodsOptions"
            required
          />
        </b-form-group>

        <b-form-group label="Transaction Reference (optional)" label-for="payment-ref">
          <b-form-input
            id="payment-ref"
            v-model="paymentForm.reference"
            placeholder="Bank ref / transaction id"
          />
        </b-form-group>

        <b-form-group label="Note (optional)" label-for="payment-note">
          <b-form-textarea
            id="payment-note"
            v-model="paymentForm.note"
            rows="3"
            placeholder="Add a note about the payment (who received it, where, etc.)"
          />
        </b-form-group>

        <div class="d-flex justify-content-end">
          <b-button variant="secondary" @click="$refs.modalAddPayment.hide()">Cancel</b-button>
          <b-button
            type="submit"
            variant="success"
            class="ml-2"
            :disabled="paymentSubmitting || !validPaymentForm"
          >
            <b-spinner small v-if="paymentSubmitting" class="mr-2" />{{ paymentSubmitting ? 'Submitting…' : 'Submit Payment' }}
          </b-button>
        </div>
      </b-form>
    </b-modal>

    <!-- Page title -->
    <h1 class="page-title">Subscription — <span class="fw-semi-bold">Details</span></h1>

    <div class="row">
      <div class="col-lg-10 mx-auto">
        <!-- Subscription summary -->
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-body d-flex align-items-center">
            <div
              class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
              style="width:72px;height:72px;"
            >
              <i class="fa fa-shield-alt text-muted"></i>
            </div>

            <div class="flex-grow-1">
              <div class="d-flex align-items-center flex-wrap">
                <h3 class="mb-0 mr-3">{{ subscriptionTitle }}</h3>
                <span class="badge" :class="subscriptionStatusClass(capFirst(subscription.status))">{{ capFirst(subscription.status) || '—' }}</span>
              </div>

              <div class="text-muted mt-1">
                <div>Subscription ID: <strong>#{{ subscription.id || '—' }}</strong></div>
                <div>Plan: <strong>{{ planName }}</strong></div>
              </div>

              <div class="mt-3 d-flex flex-wrap">
                <div class="mr-4 small text-muted">Started: {{ formatDate(subscription.started_at) }}</div>
                <div class="mr-4 small text-muted">Next due: {{ formatDate(subscription.next_due_date) }}</div>
                <div class="mr-4 small text-muted">Total paid: {{ money(subscription.total_paid_amount || 0) }}</div>
                <div class="mr-4 small text-muted">Due count: {{ subscription.due_count || 0 }}</div>
              </div>

              <div class="mt-3">
                <b-button variant="secondary" @click="$router.back()">
                  <i class="fa fa-arrow-left mr-1"></i> Back
                </b-button>
                
                <b-button variant="success" :disabled="subscription.status === 'lapsed'" class="ml-2" @click="openAddPayment">
                  <i class="fa fa-plus mr-1"></i> Add Payment
                </b-button>
              </div>
            </div>
          </div>
        </div>

        <!-- Owner & Members row -->
        <div class="row">
          <!-- Owner -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm rounded-3 h-100">
              <div class="card-header">
                <strong>Owner</strong>
              </div>
              <div class="card-body">
                <div v-if="subscription.patient">
                  <div class="font-weight-bold h5 mb-1">{{ ownerFullName }}</div>
                  <div class="small text-muted mb-2">Phone: <a :href="'tel:' + subscription.patient.phone">{{ subscription.patient.phone }}</a></div>
                  <div class="small text-muted mb-2">Email: {{ subscription.patient.email || '—' }}</div>
                  <div class="small text-muted">DOB: {{ formatDate(subscription.patient.date_of_birth) }} • Age: {{ ownerAge }}</div>
                  <hr />
                  <div>
                    <div class="small text-muted">Owner Plan</div>
                    <div class="font-weight-bold">{{ planName }} — {{ money(ownerMonthly) }}/mo</div>
                  </div>
                </div>
                <div v-else class="text-muted">Owner details not available.</div>
              </div>
            </div>
          </div>

          <!-- Members -->
          <div class="col-md-6 mb-4">
            <div class="card shadow-sm rounded-3 h-100">
              <div class="card-header">
                <strong>Members</strong>
                <small class="text-muted ml-2">({{ dependentsCount + 1 }} total)</small>
              </div>
              <div class="card-body">
                <!-- Owner row -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <div class="font-weight-medium">{{ ownerFullName }} <small class="text-muted"> (Owner)</small></div>
                      <div class="small text-muted">Plan: {{ planName }}</div>
                    </div>
                    <div class="text-right">
                      <div class="font-weight-bold">{{ money(ownerMonthly) }}/mo</div>
                      <div class="small text-muted">Owner</div>
                    </div>
                  </div>
                </div>

                <div v-if="subscription.dependents && subscription.dependents.length">
                  <hr />
                  <div v-for="(d, i) in subscription.dependents" :key="i" class="mb-3">
                    <div class="d-flex justify-content-between">
                      <div>
                        <div class="font-weight-medium">{{ d.first_name }} {{ d.last_name }}</div>
                        <div class="small text-muted">DOB: {{ formatDate(d.date_of_birth) }} • Age: {{ ageFromDate(d.date_of_birth) }}</div>
                        <div class="small text-muted">Relationship: {{ d.relationship || '—' }}</div>
                      </div>
                      <div class="text-right">
                        <div class="font-weight-bold">{{ money(dependentMonthly(d)) }}/mo</div>
                        <div class="small text-muted">{{ dependentPlanName(d) }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="text-muted">No dependents on this subscription.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payments / Statement -->
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <strong>Payment Statement</strong>
              <div class="small text-muted">Payments applied to this subscription</div>
            </div>
            <div>
              <b-button size="sm" variant="outline-secondary" @click="loadPayments">Refresh</b-button>
            </div>
          </div>

          <div class="card-body p-0">
            <div v-if="paymentsLoading" class="p-3 text-center text-muted">Loading payments…</div>

            <div v-else>
              <div v-if="!payments || !payments.length" class="p-3 text-center text-muted">No payments recorded.</div>

              <div v-else class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>Date</th>
                      <th>Reference</th>
                      <th>Method</th>
                      <th>Note</th>
                      <th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="p in payments" :key="p.id">
                      <td>{{ formatDateTime(p.created_at || p.paid_at || p.date) }}</td>
                      <td>{{ p.reference || p.transaction_id || '—' }}</td>
                      <td>{{ p.method || p.payment_method || '—' }}</td>
                      <td>{{ p.note || p.notes || '—' }}</td>
                      <td>{{ money(p.amount || p.value || p.total || 0) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="p-3 d-flex justify-content-end">
                <div class="text-right">
                  <div class="mr-4 small text-muted">Total paid: {{ money(subscription.total_paid_amount || 0) }}</div>
                  <div class="mr-4 small text-muted">Total claims: {{ money(totalClaims) }}</div>
                  <div class="mr-4 small text-muted">Available balance: {{ money(balance) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mb-5 d-flex justify-content-end">
          <b-button variant="success" :disabled="subscription.status === 'lapsed'" @click="openAddPayment"><span class="fa fa-credit-card" /> Add Payment</b-button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import authHeader from "@/services/auth-header";

export default {
  name: "InsurancePage",
  data() {
    return {
      subscription: {},
      payments: [],
      loading: false,
      paymentsLoading: false,

      // add payment form state
      paymentForm: {
        amount: null,
        method: "",
        reference: "",
        note: ""
      },
      paymentSubmitting: false,
      paymentError: null,
      paymentMethodsOptions: [
        { value: "bank_transfer", text: "Bank Transfer" },
        { value: "cash", text: "Cash (Reception)" },
        { value: "mobile", text: "Mobile Money" },
      ]
    };
  },
  computed: {
    subscriptionTitle() {
      if (this.subscription && this.subscription.plan && this.subscription.plan.name) {
        return this.subscription.plan.name;
      }
      if (this.subscription && this.subscription.id) {
        return "Subscription #" + this.subscription.id;
      }
      return "Subscription";
    },
    planName() {
      return (this.subscription && this.subscription.plan && this.subscription.plan.name) ? this.subscription.plan.name : "—";
    },
    ownerFullName() {
      if (!this.subscription || !this.subscription.patient) return "—";
      const p = this.subscription.patient;
      return ((p.first_name || "") + " " + (p.last_name || "")).trim() || "—";
    },
    ownerAge() {
      if (!this.subscription || !this.subscription.patient) return "—";
      return this.ageFromDate(this.subscription.patient.date_of_birth);
    },
    dependentsCount() {
      if (!this.subscription || !this.subscription.dependents) return 0;
      return this.subscription.dependents.length;
    },
    ownerMonthly() {
      // If the backend already gives monthly_total per subscription, prefer that for ownerMonthly calculation fallback:
      if (this.subscription && this.subscription.monthly_total) {
        // monthly_total is for entire subscription; ownerMonthly should be part of it. Keep fallback simple:
        return Number(this.subscription.monthly_total) || 0;
      }
      if (!this.subscription || !this.subscription.plan || !this.subscription.patient) return 0;
      const plan = this.subscription.plan;
      const age = this.ageFromDate(this.subscription.patient.date_of_birth);
      return Number(age >= 18 ? (plan.price_adult || 0) : (plan.price_child || 0));
    },
    totalPayments() {
      return (this.payments || []).reduce((s, p) => s + Number(p.amount || p.value || p.total || 0), 0);
    },
    validPaymentForm() {
      return this.paymentForm.amount && Number(this.paymentForm.amount) > 0 && this.paymentForm.method;
    },
    totalClaims() {
    // if subscription includes claims
    if (this.subscription && Array.isArray(this.subscription.claims)) {
      return this.subscription.claims.reduce((s, c) => s + Number(c.amount || 0), 0);
    }
    // fallback to API-sourced computed number you may fetch separately
    return 0;
  },

  balance() {
    // available balance = total_paid_amount - total_claims
    const paid = Number(this.subscription.total_paid_amount || 0);
    const claims = Number(this.totalClaims || 0);
    return (paid - claims).toFixed(2);
  }
  },
  methods: {
    loadSubscription() {
      this.loading = true;
      const id = this.$route.params.insurance || this.$route.params.subscription || this.$route.params.id;
      if (!id) {
        this.$swal("Error", "Subscription id missing in route.", "error");
        this.loading = false;
        return;
      }
      const url = (this.$base_url ? this.$base_url : "") + "subscriptions/" + id;
      this.$axios.get(url, authHeader())
        .then(({ data }) => {
          let sub = null;
          if (data && data.data) sub = data.data;
          else if (data && data.subscription) sub = data.subscription;
          else if (data && data.id) sub = data;
          else sub = data;
          this.subscription = sub || {};
          this.loading = false;
          this.loadPayments();
        })
        .catch((err) => {
          this.loading = false;
          const msg = (err && err.response && err.response.data && err.response.data.message) || (err && err.message) || "Failed to load subscription";
          this.$swal("Error", msg, "error");
        });
    },

    loadPayments() {
      if (this.subscription && Array.isArray(this.subscription.payments) && this.subscription.payments.length) {
        this.payments = this.subscription.payments;
        return;
      }
      const id = this.subscription && this.subscription.id ? this.subscription.id : (this.$route.params.insurance || this.$route.params.subscription);
      if (!id) {
        this.payments = [];
        return;
      }
      this.paymentsLoading = true;
      const tryUrl = (this.$base_url ? this.$base_url : "") + "subscriptions/" + id + "/payments";
      const fallbackUrl = (this.$base_url ? this.$base_url : "") + "payments?subscription_id=" + id;

      this.$axios.get(tryUrl, authHeader())
        .then(({ data }) => {
          if (Array.isArray(data)) this.payments = data;
          else if (data && data.data && Array.isArray(data.data)) this.payments = data.data;
          else if (data && data.payments && Array.isArray(data.payments)) this.payments = data.payments;
          else this.payments = [];
        })
        .catch(() => {
          this.$axios.get(fallbackUrl, authHeader())
            .then(({ data }) => {
              if (Array.isArray(data)) this.payments = data;
              else if (data && data.data && Array.isArray(data.data)) this.payments = data.data;
              else this.payments = [];
            })
            .catch(() => { this.payments = []; })
            .finally(() => { this.paymentsLoading = false; });
        })
        .finally(() => { this.paymentsLoading = false; });
    },

    dependentMonthly(d) {
      if (!d) return 0;
      const plan = d.plan || (d.plan_id ? this.subscription.plan : null);
      if (!plan) return 0;
      const age = this.ageFromDate(d.date_of_birth);
      return Number(age >= 18 ? (plan.price_adult || 0) : (plan.price_child || 0));
    },

    dependentPlanName(d) {
      if (!d) return "—";
      if (d.plan && d.plan.name) return d.plan.name;
      if (d.plan_id && this.subscription && this.subscription.plan && String(this.subscription.plan.id) === String(d.plan_id)) return this.subscription.plan.name;
      return "—";
    },

    ageFromDate(d) {
      if (!d) return "—";
      const dt = new Date(d);
      if (isNaN(dt.getTime())) return "—";
      const now = new Date();
      let age = now.getFullYear() - dt.getFullYear();
      const m = now.getMonth() - dt.getMonth();
      if (m < 0 || (m === 0 && now.getDate() < dt.getDate())) age--;
      return age;
    },

    money(v) {
      const n = Number(v || 0);
      return isNaN(n) ? "$0.00" : "$" + n.toFixed(2);
    },

    formatDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      if (isNaN(d.getTime())) return val;
      return d.toLocaleDateString("en-AU", { day: "2-digit", month: "short", year: "numeric" });
    },

    formatDateTime(val) {
      if (!val) return "—";
      const d = new Date(val);
      if (isNaN(d.getTime())) return val;
      return d.toLocaleString("en-AU", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
    },

    capFirst(s) {
      if (!s) return "";
      return String(s).charAt(0).toUpperCase() + String(s).slice(1);
    },

    subscriptionStatusClass(status) {
      const s = (status) ? String(this.subscription.status).toLowerCase() : "";
      if (s === "active" || s === "paid" || s === "completed") return "badge-success";
      if (s === "covered") return "badge-info";
      if (s === "pending" || s === "due") return "badge-warning";
      if (s === "lapsed" || s === "closed" || s === "cancelled") return "badge-danger";
      return "badge-secondary";
    },

    paymentStatusClass(s) {
      const v = (s || "").toString().toLowerCase();
      if (v === "success" || v === "paid" || v === "completed") return "badge-success";
      if (v === "pending") return "badge-warning";
      if (v === "failed" || v === "cancelled") return "badge-danger";
      return "badge-secondary";
    },

    goToPatientPrescriptions() {
      if (!this.subscription.patient) return;
      this.$router.push({ name: "patientprescriptions", params: { patient: this.subscription.patient.id } }).catch(()=>{});
    },

    goToAddPayment() {
      const id = this.subscription && this.subscription.id;
      if (!id) return;
      this.openAddPayment();
    },

    fullName(p) {
      if (!p) return "—";
      return ((p.first_name || "") + " " + (p.last_name || "")).trim() || "—";
    },

    /* ===== Add payment modal helpers ===== */
    openAddPayment() {
      // prefill amount suggestion (ownerMonthly + dependents)
      this.paymentError = null;

      // If backend provides subscription.monthly_total use it (prefer), else compute
      const backendTotal = this.subscription && (this.subscription.monthly_total || this.subscription.monthly_amount || this.subscription.monthly);
      if (typeof backendTotal !== "undefined" && backendTotal !== null && backendTotal !== "") {
        // try parse number safely
        const n = Number(backendTotal);
        this.paymentForm.amount = isNaN(n) ? null : n;
      } else {
        // compute owner + dependents
        let total = 0;
        // ownerMonthly may be a fallback that returns the whole subscription monthly_total; ensure we compute properly:
        try {
          // If subscription.plan & patient exist, compute owner's price
          if (this.subscription && this.subscription.plan && this.subscription.patient && this.subscription.patient.date_of_birth) {
            const plan = this.subscription.plan;
            const age = this.ageFromDate(this.subscription.patient.date_of_birth);
            total += Number(age >= 18 ? (plan.price_adult || 0) : (plan.price_child || 0));
          } else if (this.ownerMonthly) {
            total += Number(this.ownerMonthly || 0);
          }

          // dependents
          if (this.subscription && Array.isArray(this.subscription.dependents)) {
            this.subscription.dependents.forEach(d => {
              total += Number(this.dependentMonthly(d) || 0);
            });
          }
        } catch (e) {
          // fallback safety
        }
        this.paymentForm.amount = Number(total) || null;
      }

      this.paymentForm.method = "";
      this.paymentForm.reference = "";
      this.paymentForm.note = "";
      this.$nextTick(() => {
        this.$refs.modalAddPayment.show();
      });
    },

    submitPayment() {
      this.paymentError = null;
      if (!this.validPaymentForm) {
        this.paymentError = "Please enter an amount and select a payment method.";
        return;
      }
      const subscriptionId = this.subscription && this.subscription.id;
      if (!subscriptionId) {
        this.paymentError = "Subscription id missing.";
        return;
      }
      this.paymentSubmitting = true;
      const payload = {
        amount: Number(this.paymentForm.amount),
        payment_method: this.paymentForm.method,
        reference: this.paymentForm.reference || null,
        note: this.paymentForm.note || null
      };

      const url = (this.$base_url ? this.$base_url : "") + "subscriptions/" + subscriptionId + "/payments";

      this.$axios.post(url, payload, authHeader())
        .then(({ data }) => {
          // If API returns the new payment object, merge; otherwise reload payments
          if (data && (data.payment || data.data || data)) {
            // Try to extract created payment
            let created = null;
            if (data.payment) created = data.payment;
            else if (data.data && Array.isArray(data.data)) created = data.data[0] || null;
            else if (data.data && data.data.id) created = data.data;
            else if (data.id) created = data;
            if (created) {
              // prepend to payments list
              this.payments = [created].concat(this.payments || []);
            } else {
              // fallback reload
              this.loadPayments();
            }
            this.$refs.modalAddPayment.hide();
            this.$swal("Success", "Payment recorded successfully.", "success");
            // reload subscription (so totals update)
            this.loadSubscription();
          } else {
            // fallback: reload payments
            this.loadPayments();
            this.$refs.modalAddPayment.hide();
            this.$swal("Success", "Payment recorded successfully.", "success");
            this.loadSubscription();
          }
        })
        .catch((err) => {
          console.error("Payment error:", err);
          this.paymentError =
            (err && err.response && err.response.data && (err.response.data.message || (err.response.data.error || err.response.data))) ||
            err.message ||
            "Failed to submit payment.";
        })
        .finally(() => {
          this.paymentSubmitting = false;
        });
    }
  },

  created() {
    this.loadSubscription();
  }
};
</script>

<style scoped>
.page-title { margin-bottom: 12px; }
.avatar i { font-size: 28px; color: #374151; }
.badge-success { background: #28a745; color: #fff; }
.badge-warning { background: #f59e0b; color: #111827; }
.badge-danger { background: #dc3545; color: #fff; }
.badge-secondary { background: #6c757d; color: #fff; }
.card { border-radius: 10px; }
.table th, .table td { vertical-align: middle; }
.small { font-size: 0.85rem; }
.font-weight-medium { font-weight: 600; }

/* modal form tweaks */
.modal-body .form-control { min-height: 38px; }
</style>
