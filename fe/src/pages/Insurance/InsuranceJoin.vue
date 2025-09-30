<template>
  <div class="auth-page">
    <b-container>
      <h5 class="auth-logo">
        <i class="fa fa-circle text-primary"></i>
        clinicPlus App
        <i class="fa fa-circle text-danger"></i>
      </h5>

      <Widget class="widget-auth mx-auto" title="<h3 class='mt-0'>Insurance Sign Up</h3>" customHeader>
        <p class="widget-auth-info">Existing patients may sign up for insurance here.</p>

        <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
          {{ errorMessage }}
        </b-alert>

        <!-- Step 1: Verify -->
        <div v-if="currentStep === 1" class="mb-3">
          <div class="form-group">
            <input
              class="form-control no-border"
              ref="verify_name"
              v-model.trim="verify.name"
              type="text"
              placeholder="Full name (as on patient record)"
            />
          </div>

          <div class="form-group">
            <input
              class="form-control no-border"
              ref="verify_phone"
              v-model.trim="verify.phone"
              type="tel"
              placeholder="Phone number"
            />
          </div>

          <div v-if="matches.length" class="mb-2 matches-list">
            <div
              v-for="m in matches"
              :key="m.id"
              @click="chooseMatch(m)"
              :class="['match-card', selected && selected.id === m.id ? 'selected' : '']"
            >
              <div class="match-info">
                <div class="font-weight-bold">{{ (m.first_name || '') + ' ' + (m.last_name || '') }}</div>
                <div class="small text-muted">Phone: {{ m.phone || '—' }} • DOB: {{ m.date_of_birth || '—' }}</div>
              </div>

              <div class="match-actions">
                <b-button size="sm" variant="outline-primary" @click.stop="chooseMatch(m)">Use this</b-button>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <b-button size="sm" variant="secondary" @click="resetAll">Reset</b-button>
            <b-button size="sm" variant="primary" :disabled="verifying || !verify.name || !verify.phone" @click="doVerify">
              {{ verifying ? 'Verifying…' : (matches.length ? 'Continue' : 'Verify') }}
            </b-button>
          </div>
        </div>

        <!-- Step 2: Plan selection & dependents -->
        <div v-if="currentStep === 2" class="mb-3">
          <h6>Select plan for owner</h6>
          <div class="d-flex flex-wrap mb-3">
            <div
              v-for="plan in plans"
              :key="plan.id"
              class="plan-card"
              :class="enrollment.owner_plan_id === plan.id ? 'selected' : ''"
            >
              <div class="font-weight-bold">{{ plan.name }}</div>
              <div class="small text-muted">{{ plan.description || '' }}</div>
              <div class="mt-1"><small>Adult: ${{ plan.price_adult }} / Child: ${{ plan.price_child }}</small></div>
              <div class="mt-2">
                <b-button size="sm" variant="outline-primary" @click="selectOwnerPlan(plan)">Select</b-button>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6>Dependents</h6>
            <b-button size="sm" variant="success" @click="addDependent">+ Add Dependent</b-button>
          </div>

          <div v-if="enrollment.dependents.length === 0">
            <div class="font-weight-bold">No dependents added.</div>
            
          </div>

          <div v-for="(dep, idx) in enrollment.dependents" :key="idx" class="enrollment-dependent">
            <div class="d-flex justify-content-between">
              <div><strong>Dependent {{ idx + 1 }}</strong></div>
              <div><b-button size="sm" variant="danger" @click="removeDependent(idx)">Remove</b-button></div>
            </div>

            <div class="form-row mt-2">
              <div class="form-group col-md-6">
                <input class="form-control" v-model.trim="dep.first_name" placeholder="First name" />
              </div>
              <div class="form-group col-md-6">
                <input class="form-control" v-model.trim="dep.last_name" placeholder="Last name" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label class="small">Date of birth</label>
                <input type="date" class="form-control" v-model="dep.date_of_birth" :max="today" />
                <div class="small text-muted" v-if="dep.date_of_birth">Age: {{ calcAge(dep.date_of_birth) }}</div>
              </div>

              <div class="form-group col-md-6">
                <label class="small">Gender</label>
                <select class="form-control" v-model="dep.gender">
                  <option value="">Select</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="small">Select plan</label>
              <div class="d-flex flex-wrap">
                <div
                  v-for="plan in plans"
                  :key="plan.id + '-dp-' + idx"
                  class="plan-card"
                  :class="dep.plan_id === plan.id ? 'selected' : ''"
                >
                  <div class="small font-weight-bold">{{ plan.name }}</div>
                  <div class="small text-muted">${{ (calcAge(dep.date_of_birth) >= 18) ? plan.price_adult : plan.price_child }} /mo</div>
                  <div class="mt-1"><b-button size="sm" variant="outline-primary" @click="dep.plan_id = plan.id">Choose</b-button></div>
                </div>
              </div>
            </div>
          </div>

          
          <!-- Summary Card -->
          <b-card class="mb-3 summary-card">
            <b-row>
              <b-col>
                <div class="small text-muted">Members</div>
                <div class="h5 mb-0">{{ 1 + enrollment.dependents.length }} member(s)</div>
              </b-col>
              <b-col class="text-right">
                <div class="small text-muted">Monthly total</div>
                <div class="h4 text-success mb-0">${{ monthlyTotal }}</div>
              </b-col>
            </b-row>
          </b-card>

          <div class="d-flex justify-content-between">
            <b-button size="sm" variant="secondary" @click="currentStep = 1">Back</b-button>
            <b-button size="sm" variant="primary" @click="toReview">Continue</b-button>
          </div>
        </div>

        <!-- Step 3: Review & submit -->
        <div v-if="currentStep === 3">
          <h6>Review & Payment</h6>

          <div class="border rounded p-3 mb-3">
            <div><strong>Owner</strong></div>
            <div class="small text-muted">{{ selected ? (selected.first_name + ' ' + selected.last_name) : '' }}</div>
            <div class="small text-muted">Phone: {{ selected ? selected.phone : '' }}</div>
            <div class="mt-2">Plan: <strong>{{ ownerPlanName }}</strong> • ${{ ownerPrice }}/mo</div>
          </div>

          <div v-if="enrollment.dependents.length" class="border rounded p-3 mb-3">
            <div><strong>Dependents</strong></div>
            <div v-for="(d, i) in enrollment.dependents" :key="'rev' + i" class="mt-2 p-2 border rounded">
              <div>{{ d.first_name }} {{ d.last_name }} — Age: {{ calcAge(d.date_of_birth) }}</div>
              <div class="small text-muted">Plan: {{ getPlanName(d.plan_id) }} — ${{ depPrice(d) }}/mo</div>
            </div>
          </div>

          <div class="form-group">
            <label class="small">Payment method</label>
            <select class="form-control" v-model="enrollment.payment_method">
              <option value="">Select</option>
              <option v-for="m in paymentMethods" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" v-model="enrollment.accept_declaration" id="decl" />
            <label class="form-check-label small" for="decl">
              I declare the information is true and accept the terms (coverage after 3 months)
            </label>
          </div>

          <div class="d-flex justify-content-between">
            <b-button size="sm" variant="secondary" @click="currentStep = 2">Back</b-button>
            <b-button size="sm" variant="success" :disabled="submitting || !enrollment.accept_declaration || !enrollment.payment_method" @click="submitEnrollment">
              {{ submitting ? 'Submitting…' : 'Complete Enrollment' }}
            </b-button>
          </div>
        </div>

        <!-- Success -->
        <div v-if="currentStep === 4" class="text-center">
          <h5 class="text-success">Enrollment Submitted</h5>
          <p>Thank you. Payment instructions will be sent to you.</p>
          <div v-if="result" class="result-box border rounded p-3">
            <div>Subscription ID: <strong>{{ result.subscription_id }}</strong></div>
            <div>Monthly: <strong>${{ result.monthly_total }}</strong></div>
            <div>Next due: <strong>{{ result.next_due_date }}</strong></div>
          </div>

          <div class="mt-3">
            <b-button size="sm" variant="primary" @click="resetAll">Enroll Another</b-button>
          </div>
        </div>
      </Widget>
    </b-container>

    <footer class="auth-footer mt-3 text-center">
      clinicPlus App &copy; {{ new Date().getFullYear() }}
    </footer>
  </div>
</template>

<script>
import Widget from "@/components/Widget/Widget";

export default {
  name: "InsuranceJoin",
  components: { Widget },
  data() {
    return {
      errorMessage: null,
      currentStep: 1,

      // verify
      verify: { name: "", phone: "" },
      verifying: false,
      matches: [],
      selected: null,

      // plans and enrollment
      plans: [],
      enrollment: {
        owner_plan_id: null,
        dependents: [],
        payment_method: "",
        accept_declaration: false
      },

      paymentMethods: [
        { value: "bank", label: "Bank Transfer" },
        { value: "cash", label: "Cash (at reception)" },
        { value: "mobile", label: "Mobile Money" }
      ],

      submitting: false,
      result: null,
      today: new Date().toISOString().split("T")[0]
    };
  },
  computed: {
    ownerPlanName() {
      const p = this.planById(this.enrollment.owner_plan_id);
      return p ? p.name : "—";
    },
    ownerPrice() {
      if (!this.enrollment.owner_plan_id || !this.selected || !this.selected.date_of_birth) return "0.00";
      const p = this.planById(this.enrollment.owner_plan_id);
      if (!p) return "0.00";
      const age = this.calcAge(this.selected.date_of_birth);
      return (age >= 18 ? Number(p.price_adult) : Number(p.price_child)).toFixed(2);
    },
    monthlyTotal() {
      let t = 0;
      t += Number(this.ownerPrice || 0);
      for (let i = 0; i < this.enrollment.dependents.length; i++) {
        t += Number(this.depPrice(this.enrollment.dependents[i]) || 0);
      }
      return t.toFixed(2);
    }
  },
  methods: {
    loadPlans() {
      const http = this.$axios || (window && window.axios) || null;
      if (!http) {
        this.errorMessage = "HTTP client not available.";
        return;
      }

      const url = (this.$base_url ? this.$base_url : "") + "plans";
      http.get(url)
        .then(resp => {
          const d = resp.data || {};
          if (d.success && d.plans) this.plans = d.plans;
          else if (Array.isArray(d)) this.plans = d;
          else if (d.data) this.plans = d.data;
        })
        .catch(err => {
          console.error(err);
          this.errorMessage = "Could not load plans.";
        });
    },

    doVerify() {
      this.errorMessage = null;
      this.matches = [];
      this.selected = null;

      if (!this.verify.name || !this.verify.phone) {
        this.errorMessage = "Please enter your name and phone.";
        return;
      }

      this.verifying = true;
      const http = this.$axios || (window && window.axios) || null;
      if (!http) {
        this.errorMessage = "HTTP client not available.";
        this.verifying = false;
        return;
      }

      const url = (this.$base_url ? this.$base_url : "") + "verify-patient";

      http.post(url, { name: this.verify.name, phone: this.verify.phone })
        .then(resp => {
          const d = resp.data || {};
          if (d.success) {
            this.matches = Array.isArray(d.matches) ? d.matches : (d.data || []);
            if (this.matches.length === 1) {
              this.selected = this.matches[0];
              this.currentStep = 2;
            }
          } else {
            this.errorMessage = d.message || "No matching patient found.";
          }
        })
        .catch(err => {
          console.error(err);
          this.errorMessage = "Verification failed.";
        })
        .finally(() => {
          this.verifying = false;
        });
    },

    chooseMatch(m) {
      this.selected = m;
      this.currentStep = 2;
    },

    selectOwnerPlan(plan) {
      this.enrollment.owner_plan_id = plan.id;
    },

    addDependent() {
      this.enrollment.dependents.push({
        first_name: "",
        last_name: "",
        date_of_birth: "",
        gender: "",
        plan_id: null,
        relationship: ""
      });
    },

    removeDependent(i) {
      this.enrollment.dependents.splice(i, 1);
    },

    calcAge(dob) {
      if (!dob) return 0;
      const b = new Date(dob);
      const now = new Date();
      let age = now.getFullYear() - b.getFullYear();
      const m = now.getMonth() - b.getMonth();
      if (m < 0 || (m === 0 && now.getDate() < b.getDate())) age--;
      return age;
    },

    planById(id) {
      if (!id) return null;
      for (let i = 0; i < this.plans.length; i++) {
        if (String(this.plans[i].id) === String(id)) return this.plans[i];
      }
      return null;
    },

    depPrice(d) {
      if (!d || !d.plan_id || !d.date_of_birth) return "0.00";
      const p = this.planById(d.plan_id);
      if (!p) return "0.00";
      const age = this.calcAge(d.date_of_birth);
      return (age >= 18 ? Number(p.price_adult) : Number(p.price_child)).toFixed(2);
    },

    getPlanName(id) {
      const p = this.planById(id);
      return p ? p.name : "—";
    },

    toReview() {
      this.errorMessage = null;
      if (!this.selected) {
        this.errorMessage = "Please verify and select your patient record first.";
        return;
      }
      if (!this.enrollment.owner_plan_id) {
        this.errorMessage = "Please select a plan for the owner.";
        return;
      }
      // validate dependents
      for (let i = 0; i < this.enrollment.dependents.length; i++) {
        const d = this.enrollment.dependents[i];
        if (!d.first_name || !d.last_name) {
          this.errorMessage = `Dependent ${i + 1}: please provide full name.`;
          return;
        }
        if (!d.date_of_birth) {
          this.errorMessage = `Dependent ${i + 1}: date of birth required.`;
          return;
        }
        if (!d.gender) {
          this.errorMessage = `Dependent ${i + 1}: gender required.`;
          return;
        }
        if (!d.plan_id) {
          this.errorMessage = `Dependent ${i + 1}: please select a plan.`;
          return;
        }
      }
      this.currentStep = 3;
    },

    submitEnrollment() {
      this.errorMessage = null;
      if (!this.enrollment.accept_declaration) {
        this.errorMessage = "Please accept the declaration.";
        return;
      }
      if (!this.enrollment.payment_method) {
        this.errorMessage = "Please select a payment method.";
        return;
      }

      const http = this.$axios || (window && window.axios) || null;
      if (!http) {
        this.errorMessage = "HTTP client not available.";
        return;
      }

      this.submitting = true;
      const payload = {
        patient_id: this.selected ? this.selected.id : null,
        owner_plan_id: this.enrollment.owner_plan_id,
        dependents: this.enrollment.dependents,
        payment_method: this.enrollment.payment_method,
        accept_declaration: this.enrollment.accept_declaration
      };

      const url = (this.$base_url ? this.$base_url : "") + "public-signup";

      http.post(url, payload)
        .then(resp => {
          const d = resp.data || {};
          if (d.success) {
            this.result = {
              subscription_id: d.subscription_id || (d.data && d.data.subscription_id) || null,
              monthly_total: d.monthly_total || this.monthlyTotal,
              next_due_date: d.next_due_date || null
            };
            this.currentStep = 4;
            this.$swal("Success", "Enrollment submitted successfully.", "success");
          } else {
            this.errorMessage = d.message || "Enrollment failed.";
          }
        })
        .catch(err => {
          console.error(err);
          this.errorMessage = "Submission failed. Try again later.";
        })
        .finally(() => {
          this.submitting = false;
        });
    },

    resetAll() {
      this.errorMessage = null;
      this.currentStep = 1;
      this.verify = { name: "", phone: "" };
      this.matches = [];
      this.selected = null;
      this.enrollment = { owner_plan_id: null, dependents: [], payment_method: "", accept_declaration: false };
      this.result = null;
    }
  },

  created() {
    this.loadPlans();
  }
};
</script>

<style scoped>
/* Page spacing */
.auth-page {
  padding-top: 20px;
  padding-bottom: 40px;
}

/* Widget sizing */
.widget-auth {
  max-width: 920px;
  width: 100%;
  box-shadow: 0 6px 18px rgba(18, 38, 63, 0.06);
}

/* Inputs */
.form-control.no-border,
.form-control {
  border-radius: 6px;
  border: 1px solid #e6e9ef;
  background: #ffffff;
  padding: 10px 12px;
  transition: box-shadow .15s ease, border-color .15s ease;
}
.form-control.no-border:focus,
.form-control:focus {
  box-shadow: 0 6px 18px rgba(37, 99, 235, 0.08);
  border-color: #2563eb;
}

/* Matches list */
.matches-list .match-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #eef1f6;
  background: #fff;
  cursor: pointer;
  transition: transform .08s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease;
}
.matches-list .match-card + .match-card { margin-top: 8px; }

.matches-list .match-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(18, 38, 63, 0.06);
  border-color: #cfe3ff;
}

.matches-list .match-card.selected,
.matches-list .match-card.selected:hover {
  background: #f3f7ff;          /* subtle blue tint */
  border-color: #2b6df6;        /* primary */
  box-shadow: 0 8px 24px rgba(43,109,246,0.06);
}

/* Match details layout */
.match-info { max-width: 70%; }
.match-actions { display: flex; align-items: center; gap: 8px; }

/* Plan cards (selection area) */
.plan-card {
  min-width: 190px;
  max-width: 260px;
  border-radius: 8px;
  border: 1px solid #e8edf3;
  padding: 12px;
  background: #fff;
  transition: box-shadow .12s ease, border-color .12s ease, transform .08s ease;
}
.plan-card + .plan-card { margin-left: 10px; }
.plan-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(18, 38, 63, 0.06);
}
.plan-card.selected {
  background: #f3f7ff;
  border-color: #2b6df6;
}

/* Dependents card tweaks */
.enrollment-dependent {
  background: #fff;
  border-radius: 8px;
  border: 1px solid #eef1f6;
  padding: 12px;
  margin-bottom: 12px;
}
.enrollment-dependent .form-row { margin-top: 8px; }

/* Summary card */
.summary-card {
  border-radius: 8px;
  border: 1px solid #e6edf6;
  padding: 14px;
  background: #f8fbff;
}

/* Buttons */
.btn {
  border-radius: 6px;
  padding: 6px 10px;
}
.btn[disabled] {
  opacity: 0.6;
}

/* Small text variants */
.small.text-muted { color: #6b7280 !important; }

/* Success/result box */
.result-box {
  border-radius: 8px;
  border: 1px solid #e6f4ea;
  background: #f6fffa;
  padding: 12px;
}

/* Footer */
.auth-footer {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .plan-card { min-width: 100%; max-width: 100%; margin-left: 0 !important; margin-bottom: 10px; }
  .matches-list .match-card { flex-direction: column; align-items: flex-start; gap: 8px; }
  .match-info { max-width: 100%; }
}
</style>
