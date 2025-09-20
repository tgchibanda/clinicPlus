<template>
  <div>
    <vue-element-loading
      :active="isLoading"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Loading…'"
    />

    <Widget
      title="<h5>My <span class='fw-semi-bold'>Prescriptions</span></h5>"
      bodyClass="widget-table-overflow"
      customHeader
      :fetchingData="loading"
    >
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0 requests-table">
          <thead>
            <tr class="text-muted">
              <th>#</th>
              <th>Patient</th>
              <th>Doctor</th>
              <th>Notes</th>
              <th>Items</th>
              <th>Status</th>
              <th>Created</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in prescriptions" :key="row.id">
              <td>{{ row.id }}</td>

              <td>
                {{ fullName(row.patient) }}
                <div class="small text-muted">
                  <span v-if="row.patient && row.patient.email">{{ row.patient.email }}</span>
                  <span v-if="row.patient && row.patient.phone">
                    <span v-if="row.patient && row.patient.email"> • </span>{{ row.patient.phone }}
                  </span>
                </div>
              </td>

              <td>{{ row.doctor ? row.doctor.name : '—' }}</td>

              <td>
                <span :title="row.notes">{{ truncate(row.notes, 50) }}</span>
              </td>

              <td>
                <span class="badge bg-secondary">{{ (row.items && row.items.length) || 0 }}</span>
              </td>

              <td>
                <span class="badge" :class="statusClass(row.status)">
                  {{ capFirst(row.status || '') || '—' }}
                </span>
              </td>

              <td>{{ dateTime(row.created_at) }}</td>

              <td class="text-right">
                <b-button size="sm" variant="primary" class="mr-2" @click="viewPrescription(row)">
                  <span class="fa fa-eye" /> View
                </b-button>

                <!-- Make Payment: routes to Sales page with prescription id -->
                <b-button size="sm" variant="success" @click="makePayment(row)">
                  <span class="fa fa-credit-card" /> Pay
                </b-button>
              </td>
            </tr>

            <tr v-if="!prescriptions.length && !loading">
              <td colspan="8" class="text-center text-muted">No prescriptions found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Simple Prev/Next pagination using Laravel links -->
      <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination">
        <b-button
          size="sm"
          variant="outline-secondary"
          :disabled="!pagination.prev_page_url"
          @click="goPage(pagination.prev_page_url)"
        >
          ← Previous
        </b-button>

        <div class="text-muted small">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </div>

        <b-button
          size="sm"
          variant="outline-secondary"
          :disabled="!pagination.next_page_url"
          @click="goPage(pagination.next_page_url)"
        >
          Next →
        </b-button>
      </div>
    </Widget>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "PrescriptionsTable",
  data() {
    return {
      errorMessage: null,
      loading: false,
      isLoading: false,

      prescriptions: [], // array of prescriptions
      pagination: null,  // the paginator object you want to keep (current_page, next_page_url, etc.)
    };
  },
  methods: {
    loadPrescriptions(pageUrl) {
      this.loading = true;
      const url = pageUrl || (this.$base_url + "prescriptions"); // GET /api/prescriptions
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          // Your payload shape: { success, message, data: { current_page, data: [...], ... } }
          const p = data && data.data ? data.data : {};
          this.prescriptions = p.data || [];
          this.pagination = {
            current_page: p.current_page,
            last_page: p.last_page,
            next_page_url: p.next_page_url,
            prev_page_url: p.prev_page_url,
          };
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          const msg =
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            error.toString();
          this.$swal("Error", msg, "error");
        });
    },

    goPage(url) {
      if (!url) return;
      // If your API returns absolute URLs, you can call them directly.
      // If you need to replace host/port, adjust here.
      this.loadPrescriptions(url);
    },

    // Actions
    viewPrescription(item) {
      this.$router.push({ name: "prescriptionpage", params: { prescription: item.id } });
    },

    makePayment(item) {
      this.$router.push({ name: "sales", params: { prescription: item.id } });
    },

    // Display helpers (Vue 2 / bublé-safe)
    fullName(p) {
      if (!p) return "—";
      const f = p.first_name || "";
      const l = p.last_name || "";
      const n = (f + " " + l).trim();
      return n || "—";
    },
    capFirst(v) {
      if (!v) return "";
      return v.charAt(0).toUpperCase() + v.slice(1);
    },
    truncate(v, n) {
      if (!v) return "—";
      if (v.length <= n) return v;
      return v.substr(0, n) + "…";
    },
    dateTime(val) {
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
    statusClass(status) {
      const s = (status || "").toLowerCase();
      if (s === "paid" || s === "completed") return "bg-success";
      if (s === "pending") return "bg-warning";
      if (s === "failed" || s === "cancelled" || s === "canceled") return "bg-danger";
      return "bg-secondary";
    },
  },
  created() {
    this.loadPrescriptions();
  },
};
</script>

<style scoped>
.modal-button {
  position: absolute;
  z-index: 1;
  top: 4px;
  right: 4px;
  font-size: 0.875rem;
}
</style>
