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
              <th>Actions</th>
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
                <div class="action-buttons">
                  <b-button 
                    size="sm" 
                    variant="info" 
                    class="mr-2 mb-1"
                    @click="viewProfile(row)"
                    title="View complete patient profile and history"
                  >
                    <i class="fa fa-user-circle"></i> View Profile
                  </b-button>
                  <b-button 
                    size="sm" 
                    variant="primary" 
                    class="mb-1"
                    @click="viewDetails(row)"
                    title="Manage consultation and prescription"
                  >
                    <i class="fa fa-book"></i> Manage
                  </b-button>
                </div>
              </td>
            </tr>

            <tr v-if="!pagedPatients.length && !loading">
              <td colspan="10" class="text-center text-muted py-4">
                <i class="fa fa-inbox mb-2 d-block" style="font-size: 24px;"></i>
                No patients found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-bar d-flex justify-content-between align-items-center mt-3" v-if="totalPages > 1">
        <b-button 
          size="sm" 
          variant="outline-secondary" 
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          <i class="fa fa-chevron-left mr-1"></i> Prev
        </b-button>

        <div class="pagination-info">
          <span class="mx-3 small text-muted">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          
          <!-- Page number buttons for quick navigation -->
          <b-button-group size="sm" class="ml-2">
            <b-button
              v-for="page in visiblePages"
              :key="page"
              :variant="currentPage === page ? 'primary' : 'outline-secondary'"
              @click="goToPage(page)"
            >
              {{ page }}
            </b-button>
          </b-button-group>
        </div>

        <b-button 
          size="sm" 
          variant="outline-secondary" 
          :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)"
        >
          Next <i class="fa fa-chevron-right ml-1"></i>
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
      pageSize: 10,
      pageSizeOptions: [5, 10, 15, 20, 50],

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
          const firstName = (p.first_name || "").toLowerCase();
          const lastName = (p.last_name || "").toLowerCase();
          const fullName = firstName + " " + lastName;
          return (
            firstName.includes(q) ||
            lastName.includes(q) ||
            fullName.includes(q)
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
      const end = start + this.pageSize;
      return this.filteredPatients.slice(start, end);
    },

    startRow() {
      return this.filteredPatients.length
        ? (this.currentPage - 1) * this.pageSize + 1
        : 0;
    },

    endRow() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.filteredPatients.length);
    },

    // Calculate visible page numbers for pagination
    visiblePages() {
      const pages = [];
      const total = this.totalPages;
      const current = this.currentPage;
      
      if (total <= 7) {
        // Show all pages if 7 or fewer
        for (let i = 1; i <= total; i++) {
          pages.push(i);
        }
      } else {
        // Always show first page
        pages.push(1);
        
        if (current > 3) {
          pages.push('...');
        }
        
        // Show pages around current page
        for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
          pages.push(i);
        }
        
        if (current < total - 2) {
          pages.push('...');
        }
        
        // Always show last page
        pages.push(total);
      }
      
      return pages.filter(p => p !== '...' || pages.indexOf(p) === pages.lastIndexOf(p));
    }
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

          // Reset to page 1 after loading
          this.currentPage = 1;
          this.loading = false;
        })
        .catch(error => {
          this.loading = false;
          const msg = (error && error.response && error.response.data && error.response.data.message)
            || (error && error.message) || "Failed to load patients";
          this.$swal("Error!", msg, "error");
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

    goToPage(page) {
      if (page === '...') return; // Ignore ellipsis clicks
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        // Scroll to top of table
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    // Navigation actions
    viewProfile(item) {
      this.$router.push({ 
        name: "patientprofile", 
        params: { id: item.id } 
      });
    },

    viewDetails(item) {
      this.$router.push({ 
        name: "walkinpatient", 
        params: { patient: item.id } 
      });
    },

    sendInfo(item) {
      this.selectedPatient = item;
      // Button already has v-b-modal to open the modal
    },

    // Helper methods
    capFirst(v) {
      return v ? v.charAt(0).toUpperCase() + v.slice(1) : "—";
    },

    safeDate(val) {
      if (!val) return "—";
      const d = new Date(val);
      if (isNaN(d.getTime())) return val;
      return d.toLocaleDateString("en-AU", {
        day: "2-digit",
        month: "short",
        year: "numeric"
      });
    },
  },

  created() {
    this.loadWalkInPatients();
  },
};
</script>

<style scoped>
/* Toolbar padding + layout */
.widget-wrap {
  position: relative;
}

/* Put the button back in the header's top-right corner */
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

.page-size {
  min-width: 120px;
}

/* Action buttons container */
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  min-width: 200px;
}

.action-buttons .btn {
  white-space: nowrap;
}

/* Pagination bar */
.pagination-bar {
  padding: 12px 16px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 10px;
}

.pagination-info {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Table spacing tweaks */
.table td,
.table th {
  vertical-align: middle !important;
  padding-top: 0.8rem;
  padding-bottom: 0.8rem;
}

/* Badge colors */
.badge.bg-success {
  background-color: #28a745 !important;
  color: white;
}

.badge.bg-info {
  background-color: #17a2b8 !important;
  color: white;
}

.badge.bg-warning {
  background-color: #ffc107 !important;
  color: #212529;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .action-buttons {
    flex-direction: column;
    min-width: auto;
  }
  
  .action-buttons .btn {
    width: 100%;
    margin-bottom: 0.25rem;
  }
  
  .pagination-info .btn-group {
    display: none;
  }
}
</style>