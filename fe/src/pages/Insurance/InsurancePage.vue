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
              id="payment-amount"
              v-model.number="paymentForm.amount"
              type="number"
              step="0.01"
              min="0.01"
              required
              readonly
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
        <div class="card shadow-sm rounded-3 mb-4" v-if="subscriptionLoaded">
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
                <div>Policy Number: <strong>{{ subscription.policy_number || '—' }}</strong></div>
                <div>Plan: <strong>{{ planName }}</strong></div>
              </div>

              <div class="mt-3 d-flex flex-wrap">
                <div class="mr-4 small text-muted">Started: {{ formatDate(subscription.started_at) }}</div>
                <div class="mr-4 small text-muted">Next due: {{ formatDate(subscription.next_due_date) }}</div>
                <div class="mr-4 small text-muted">Total paid: {{ money(subscription.total_paid_amount || totalPayments) }}</div>
                <div class="mr-4 small text-muted">Due count: {{ subscription.due_count || 0 }}</div>
              </div>

              <div class="mt-3">
                <b-button variant="secondary" @click="$router.back()">
                  <i class="fa fa-arrow-left mr-1"></i> Back
                </b-button>
                
                <b-button variant="success" class="ml-2" @click="openAddPayment" :disabled="!subscription.id">
                  <i class="fa fa-plus mr-1"></i> Add Payment
                </b-button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-4 text-muted">Loading subscription…</div>

        <!-- Owner & Members row -->
        <div class="row" v-if="subscriptionLoaded">
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
                        <div class="small text-muted">Gender: {{ d.gender || '—' }}</div>
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
        <div v-if="subscriptionLoaded" class="card shadow-sm rounded-3 mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <strong>Payment Statement</strong>
              <div class="small text-muted">Payments & claims for this subscription</div>
            </div>
            <div>
              <b-button size="sm" variant="outline-secondary" @click="refreshAll">Refresh</b-button>
            </div>
          </div>

          <div class="card-body p-0">
            <div v-if="paymentsLoading || claimsLoading" class="p-3 text-center text-muted">Loading transactions…</div>

            <div v-else>
              <div v-if="!payments.length && !claims.length" class="p-3 text-center text-muted">No payments or claims recorded.</div>

              <div v-else class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th>Date</th>
                      <th>Type</th>
                      <th>Reference / Notes</th>
                      <th class="text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Payments (increase balance) -->
                    <tr v-for="p in payments" :key="'p'+p.id">
                      <td>{{ formatDateTime(p.paid_at || p.created_at || p.date) }}</td>
                      <td><span class="badge badge-success">Payment</span> — {{ p.payment_method || p.method || p.method_display || '—' }}</td>
                      <td>{{ p.transaction_ref }} - {{ p.note }}</td>
                      <td class="text-right text-success">+ {{ money(p.amount || p.value || p.total || 0) }}</td>
                    </tr>

                    <!-- Policy claims (reduce balance) -->
                    <tr v-for="c in claims" :key="'c'+c.id" class="table-warning">
                      <td>{{ formatDateTime(c.created_at || c.claimed_at || c.date) }}</td>
                      <td><span class="badge badge-danger">Claim</span></td>
                      <td>{{ c.claim_category + ' - Claim for ' + (c.claim_holder_first_name || '') + ' ' + (c.claim_holder_last_name || '') }}</td>
                      <td class="text-right text-danger">- {{ money(c.amount || 0) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="p-3 d-flex justify-content-end">
                <div class="text-right">
                  <div class="small text-muted">Total paid</div>
                  <div class="h6 mb-0">{{ money(totalPayments) }}</div>
                  <div class="small text-muted mt-2">Total claims</div>
                  <div class="h6 mb-0">{{ money(totalClaims) }}</div>
                  <div class="small text-muted mt-2">Policy balance</div>
                  <div class="h4 mb-0">{{ money(policyBalance) }}</div>
                  <div class="small text-muted mt-2">Monthly total (owner + dependents)</div>
                  <div class="h5 mb-0">{{ money(monthlyTotal) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mb-5 d-flex justify-content-end" v-if="subscriptionLoaded">
          <b-button variant="success" @click="openAddPayment"><span class="fa fa-credit-card" /> Add Payment</b-button>
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
      subscriptionLoaded: false,
      payments: [],
      claims: [],
      loading: false,
      paymentsLoading: false,
      claimsLoading: false,

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
      // Use subscription.monthly_total if provided (preferred)
      if (this.subscription && (this.subscription.monthly_total || this.subscription.monthly_total === 0)) {
        return Number(this.subscription.monthly_total);
      }
      if (!this.subscription || !this.subscription.plan || !this.subscription.patient) return 0;
      const plan = this.subscription.plan;
      const age = this.ageFromDate(this.subscription.patient.date_of_birth);
      return Number(age >= 18 ? (plan.price_adult || 0) : (plan.price_child || 0));
    },
    monthlyTotal() {
      // Sum owner + dependents prices (best effort)
      let total = 0;
      total += Number(this.ownerMonthly || 0);
      if (Array.isArray(this.subscription.dependents)) {
        for (const d of this.subscription.dependents) {
          total += Number(this.dependentMonthly(d) || 0);
        }
      }
      return Number(total);
    },
    totalPayments() {
      return (this.payments || []).reduce((s, p) => s + Number(p.amount || p.value || 0), 0);
    },
    totalClaims() {
      return (this.claims || []).reduce((s, c) => s + Number(c.amount || 0), 0);
    },
    policyBalance() {
      return Number((this.totalPayments || 0) - (this.totalClaims || 0));
    },
    validPaymentForm() {
      return this.paymentForm.amount && Number(this.paymentForm.amount) > 0 && this.paymentForm.method;
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
      console.info(`[InsurancePage] fetching subscription from: ${url}`);
      this.$axios.get(url, authHeader())
        .then(({ data }) => {
          let sub = null;
          if (data && data.data) sub = data.data;
          else if (data && data.subscription) sub = data.subscription;
          else if (data && data.id) sub = data;
          else sub = data;
          this.subscription = sub || {};
          this.subscriptionLoaded = true;
          this.loading = false;
          // load transactions
          this.loadPayments();
          this.loadClaims();
        })
        .catch((err) => {
          this.loading = false;
          const msg = (err && err.response && err.response.data && err.response.data.message) || (err && err.message) || "Failed to load subscription";
          this.$swal("Error", msg, "error");
        });
    },

    loadPayments() {
      if (!this.subscription || !this.subscription.id) {
        this.payments = [];
        return;
      }
      this.paymentsLoading = true;
      const tryUrl = (this.$base_url ? this.$base_url : "") + "subscriptions/" + this.subscription.id + "/payments";
      const fallbackUrl = (this.$base_url ? this.$base_url : "") + "payments?subscription_id=" + this.subscription.id;

      this.$axios.get(tryUrl, authHeader())
        .then(({ data }) => {
          if (Array.isArray(data)) this.payments = data;
          else if (data && data.data && Array.isArray(data.data)) this.payments = data.data;
          else if (data && data.payments && Array.isArray(data.payments)) this.payments = data.payments;
          else this.payments = [];
        })
        .catch(() => {
          // fallback try
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

    loadClaims() {
      if (!this.subscription || !this.subscription.id) {
        this.claims = [];
        return;
      }
      this.claimsLoading = true;
      // Try endpoint: subscriptions/{id}/claims (recommended)
      const tryUrl = (this.$base_url ? this.$base_url : "") + "subscriptions/" + this.subscription.id + "/claims";
      // Fallback: policy_claims?subscription_id=...
      const fallbackUrl = (this.$base_url ? this.$base_url : "") + "policy_claims?subscription_id=" + this.subscription.id;

      this.$axios.get(tryUrl, authHeader())
        .then(({ data }) => {
          if (Array.isArray(data)) this.claims = data;
          else if (data && data.data && Array.isArray(data.data)) this.claims = data.data;
          else if (data && data.claims && Array.isArray(data.claims)) this.claims = data.claims;
          else this.claims = [];
        })
        .catch(() => {
          this.$axios.get(fallbackUrl, authHeader())
            .then(({ data }) => {
              if (Array.isArray(data)) this.claims = data;
              else if (data && data.data && Array.isArray(data.data)) this.claims = data.data;
              else this.claims = [];
            })
            .catch(() => { this.claims = []; })
            .finally(() => { this.claimsLoading = false; });
        })
        .finally(() => { this.claimsLoading = false; });
    },

    dependentMonthly(d) {
      if (!d) return 0;
      // Prefer embedded plan object
      const plan = d.plan || (d.plan_id ? this.subscription.plan : null) || null;
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
    capFirst(v) {
    if (v === null || typeof v === 'undefined') return '';
    const s = String(v);
    if (!s.length) return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
  },

    subscriptionStatusClass() {
      const s = (this.subscription && this.subscription.status) ? String(this.subscription.status).toLowerCase() : "";
      if (s === "active" || s === "paid" || s === "covered" || s === "completed") return "badge-success";
      if (s === "pending" || s === "due" || s === "lapsed") return "badge-warning";
      if (s === "lapsed" || s === "closed" || s === "cancelled") return "badge-danger";
      return "badge-secondary";
    },

    /* ===== Add payment modal helpers ===== */
    openAddPayment() {
      // prefill amount suggestion as monthlyTotal
      this.paymentError = null;
      // If there is an outstanding monthly amount you want to suggest, use monthlyTotal
      this.paymentForm.amount = Number(this.monthlyTotal || 0);
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
        note: this.paymentForm.note || null,
        paid_at: new Date().toISOString()
      };

      const url = (this.$base_url ? this.$base_url : "") + "subscriptions/" + subscriptionId + "/payments";

      this.$axios.post(url, payload, authHeader())
        .then(({ data }) => {
          // Attempt to extract created payment object
          let created = null;
          if (data && data.payment) created = data.payment;
          else if (data && data.data && data.data.id) created = data.data;
          else if (data && data.id) created = data;
          // If we got a created object, prepend; otherwise reload
          if (created) {
            this.payments = [created].concat(this.payments || []);
          } else {
            this.loadPayments();
          }
          // close modal and reload subscription to refresh totals
          this.$refs.modalAddPayment.hide();
          this.$swal("Success", "Payment recorded successfully.", "success");
          // refresh subscription and transactions
          this.loadSubscription();
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
    },

    refreshAll() {
      this.loadPayments();
      this.loadClaims();
      // optionally reload subscription
      this.loadSubscription();
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
.table-warning td { background: #fff7e6 !important; }
</style>
