<template>
  <div>
    <vue-element-loading :active="isLoading" :is-full-screen="true" size="80" color="#FF6700" text="Loading…" />

    <Widget title="<h5>My <span class='fw-semi-bold'>Patients</span></h5>" bodyClass="widget-table-overflow"
      customHeader :fetchingData="loading">
      <b-button v-b-modal.modal-new-walk-in-patient variant="primary" class="modal-button">
        <i class="fa fa-plus"></i> Add New Patient
      </b-button>

      <!-- Toolbar -->
      <div class="toolbar d-flex flex-wrap align-items-center mb-3">
        <!-- Search -->
        <b-input-group class="mr-3 mb-2 search-box">
          <b-form-input v-model.trim="searchInput" placeholder="Search by patient name…"
            @keydown.enter.prevent="applySearch" />
          <b-input-group-append>
            <b-button variant="primary" @click="applySearch">
              <i class="fa fa-search mr-1" /> Search
            </b-button>
          </b-input-group-append>
        </b-input-group>

        <!-- Officer filter -->
        <div class="mr-3 mb-2">
          <b-form-select v-model="selectedOfficer" :options="officerOptions" @change="applyFilters" />
        </div>

        <!-- Page size -->
        <div class="d-flex align-items-center mb-2 page-size">
          <label class="mb-0 mr-2 text-muted small">Rows</label>
          <b-form-select v-model.number="pageSize" :options="pageSizeOptions" @change="onPageSizeChange" />
        </div>

        <!-- Summary -->
        <div class="text-muted small ml-auto mb-2">
          <template v-if="filteredPatients.length">
            Showing {{ startRow }}–{{ endRow }} of {{ filteredPatients.length }}
          </template>
          <template v-else>No results</template>
        </div>
      </div>


      <!-- 🔹 Table -->
      <div class="table-responsive">
        <table class="table table-striped table-lg mb-0">
          <thead>
            <tr class="text-muted">
              <th>Officer</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Gender</th>
              <th>D.O.B</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Doctor</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in pagedPatients" :key="row.id">
              <td>{{ row.user ? row.user.name : '—' }}</td>
              <td>{{ row.first_name }}</td>
              <td>{{ row.last_name }}</td>
              <td>{{ capFirst(row.gender) }}</td>
              <td>{{ safeDate(row.date_of_birth) }}</td>
              <td>{{ row.email || '—' }}</td>
              <td>{{ row.phone || '—' }}</td>
              <td>{{ row.doctor ? row.doctor.name : '—' }}</td>

              <td>
                <span class="badge" :class="{
                  'bg-success': row.status === 'completed',
                  'bg-info': row.status === 'booked',
                  'bg-warning': row.status !== 'completed' && row.status !== 'booked'
                }">
                  {{ capFirst(row.status) }}
                </span>
              </td>

              <td>
                <b-button size="sm" variant="primary" @click="viewDetails(row)">
                  <i class="fa fa-book"></i> Consult
                </b-button>
              </td>
            </tr>

            <tr v-if="!pagedPatients.length && !loading">
              <td colspan="10" class="text-center text-muted">
                No patients found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-bar mt-3" v-if="totalPages > 1">
        <b-button size="sm" variant="outline-secondary" :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)">
          ← Prev
        </b-button>

        <span class="mx-3 small">
          Page {{ currentPage }} / {{ totalPages }}
        </span>

        <b-button size="sm" variant="outline-secondary" :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)">
          Next →
        </b-button>
      </div>
    </Widget>

    <!-- Modals -->
    <b-modal id="modal-consultation" size="lg" hide-footer>
      <book-consultation :selectedPatient="selectedPatient" @booked="loadWalkInPatients" />
    </b-modal>

    <b-modal id="modal-new-walk-in-patient" size="lg" hide-footer>
      <walk-in-patient-details />
    </b-modal>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "WalkInPatientsTable",

  data() {
  return {
    userRole: userRole(),
    loading: false,
    isLoading: false,

    patients: [],

    searchInput: "",
    selectedOfficer: "all",

    currentPage: 1,
    pageSize: 5,
    pageSizeOptions: [5, 10, 15, 20],

    selectedPatient: null,
    user_id: JSON.parse(localStorage.getItem("user")).user_id,
  };
},

  computed: {
  officerOptions() {
    const officers = [];

    this.patients.forEach(p => {
      if (p.user && p.user.id) {
        officers.push({
          value: p.user.id,
          text: p.user.name
        });
      }
    });

    const unique = [
      { value: "all", text: "All Officers" },
      ...Array.from(
        new Map(officers.map(o => [o.value, o])).values()
      )
    ];

    return unique;
  },

  filteredPatients() {
    let list = Array.isArray(this.patients) ? this.patients : [];

    // Officer filter
    if (this.selectedOfficer !== "all") {
      list = list.filter(
        p => p.user && p.user.id === this.selectedOfficer
      );
    }

    // Name search
    const q = this.searchInput.toLowerCase().trim();
    if (q) {
      list = list.filter(p => {
        return (
          (p.first_name || "").toLowerCase().includes(q) ||
          (p.last_name || "").toLowerCase().includes(q)
        );
      });
    }

    return list;
  },

  totalPages() {
    return Math.max(1, Math.ceil(this.filteredPatients.length / this.pageSize));
  },

  pagedPatients() {
    const start = (this.currentPage - 1) * this.pageSize;
    return this.filteredPatients.slice(start, start + this.pageSize);
  },

  startRow() {
    return this.filteredPatients.length
      ? (this.currentPage - 1) * this.pageSize + 1
      : 0;
  },

  endRow() {
    return Math.min(
      this.currentPage * this.pageSize,
      this.filteredPatients.length
    );
  },
},


  methods: {
  loadWalkInPatients() {
    this.loading = true;

    this.$axios
      .get(this.$base_url + "walk_in_patient_details", authHeader())
      .then(({ data }) => {
        // Normalize response safely
        let list = [];

        if (Array.isArray(data)) {
          list = data;
        } else if (data && Array.isArray(data.data)) {
          list = data.data;
        } else if (data && data.data && Array.isArray(data.data.data)) {
          list = data.data.data;
        }

        this.patients = list.map(p => ({
          id: p.id,
          first_name: p.first_name,
          last_name: p.last_name,
          gender: p.gender,
          date_of_birth: p.date_of_birth,
          email: p.email,
          phone: p.phone,
          user: p.user || null,     // officer
          doctor: p.doctor || null,
          status: p.status || "pending",
        }));

        this.currentPage = 1;
        this.loading = false;
      })
      .catch(error => {
        this.loading = false;
        this.$swal("error!", "Failed to load patients", "error");
        console.error(error);
      });
  },

  applySearch() {
    this.currentPage = 1;
  },

  applyFilters() {
    this.currentPage = 1;
  },

  onPageSizeChange() {
    this.currentPage = 1;
  },

    // actions
    viewDetails(item) {
      this.$router.push({ name: "walkinpatientpage", params: { patient: item.id } });
    },
    sendInfo(item) {
      this.selectedPatient = item;
      // Button already has v-b-modal to open the modal
    },

    // helpers
  capFirst(v) {
    return v ? v.charAt(0).toUpperCase() + v.slice(1) : "—";
  },

  safeDate(val) {
    if (!val) return "—";
    const d = new Date(val);
    return isNaN(d.getTime()) ? val : d.toLocaleDateString();
  },
},


  created() {
    this.loadWalkInPatients();
  },
};
</script>


<style scoped>
/* Toolbar padding + layout (kept to match your prescriptions page) */
.widget-wrap {
  position: relative;
}

/* Put the button back in the header’s top-right corner */
.modal-button {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 10;
  font-size: 0.875rem;
}

.toolbar {
  padding: 14px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

.search-box {
  min-width: 280px;
  max-width: 420px;
}

.page-size-select {
  min-width: 100px;
}

/* Pagination bar padding */
.pagination-bar {
  padding: 12px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

/* Table spacing tweaks */
.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}
</style>
