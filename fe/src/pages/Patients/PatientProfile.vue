<template>
  <section>
    <!-- Edit Prescription Modal -->
    <b-modal
      id="modal-edit-prescription"
      ref="modal-edit-prescription"
      size="lg"
      title="Edit Prescription"
      @ok="handleUpdatePrescription"
      @hidden="resetEditForm"
    >
      <b-form @submit.prevent="handleUpdatePrescription">
        <div class="mb-3">
          <label class="form-label">Notes</label>
          <b-form-textarea
            v-model="editForm.notes"
            rows="3"
            placeholder="Additional notes about the prescription..."
          />
        </div>

        <b-form-group label="Prescribed Drugs">
          <div
            v-for="(item, idx) in editForm.drugs"
            :key="'edit-drug-'+idx"
            class="drug-item mb-3"
          >
            <div class="row align-items-start">
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

                <div v-if="selectedEditDrug(idx)" class="mt-2 d-flex flex-wrap gap-2">
                  <span class="badge badge-soft">
                    Stock: {{ selectedEditDrug(idx).stock_quantity }}
                  </span>
                  <span class="badge badge-soft">
                    Price: {{ formatMoney(selectedEditDrug(idx).price) }}
                  </span>
                </div>
              </div>

              <div class="col-md-2">
                <label class="form-label small text-muted d-block mb-1">Quantity</label>
                <b-form-input
                  type="number"
                  min="1"
                  v-model.number="item.quantity_prescribed"
                  placeholder="Qty"
                  required
                />
              </div>

              <div class="col-md-3 d-flex align-items-end justify-content-end">
                <b-button
                  v-if="editForm.drugs.length > 1"
                  variant="danger"
                  size="sm"
                  class="mt-4"
                  @click="removeEditDrug(idx)"
                >
                  <i class="fa fa-times"></i>
                </b-button>
              </div>
            </div>

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

            <hr class="my-3" v-if="idx !== editForm.drugs.length - 1" />
          </div>

          <b-button type="button" variant="secondary" size="sm" @click="addEditDrug">
            <i class="fa fa-plus"></i> Add Another Drug
          </b-button>
        </b-form-group>
      </b-form>
    </b-modal>

    <!-- Edit Doctor Notes Modal -->
    <b-modal
      id="modal-edit-notes"
      ref="modal-edit-notes"
      size="lg"
      title="Edit Doctor's Notes"
      @ok="handleUpdateNotes"
      @hidden="resetNotesForm"
    >
      <b-form @submit.prevent="handleUpdateNotes">
        <b-form-group label="Reason">
          <b-form-textarea
            v-model="notesForm.reason"
            rows="2"
            placeholder="Reason for consultation"
          />
        </b-form-group>

        <b-form-group label="Instruction">
          <b-form-textarea
            v-model="notesForm.instruction"
            rows="2"
            placeholder="Instructions"
          />
        </b-form-group>

        <b-form-group label="Examination">
          <b-form-textarea
            v-model="notesForm.examination"
            rows="3"
            placeholder="Examination findings"
          />
        </b-form-group>

        <b-form-group label="Diagnosis">
          <b-form-textarea
            v-model="notesForm.diagnosis"
            rows="3"
            placeholder="Diagnosis"
          />
        </b-form-group>

        <b-form-group label="Management">
          <b-form-textarea
            v-model="notesForm.management"
            rows="3"
            placeholder="Management plan"
          />
        </b-form-group>

        <b-form-group label="Investigation">
          <b-form-textarea
            v-model="notesForm.investigation"
            rows="2"
            placeholder="Investigations ordered"
          />
        </b-form-group>
      </b-form>
    </b-modal>

    <!-- Upload Document Modal -->
    <b-modal
      id="modal-upload-document"
      ref="modal-upload-document"
      title="Upload Document"
      @ok="handleUploadDocument"
      @hidden="resetUploadForm"
    >
      <b-form @submit.prevent="handleUploadDocument">
        <b-form-group label="Document Title *" label-for="doc-title">
          <b-form-input
            id="doc-title"
            v-model="uploadForm.title"
            placeholder="Enter document title"
            required
          />
        </b-form-group>

        <b-form-group label="Select File *" label-for="doc-file">
          <b-form-file
            id="doc-file"
            v-model="uploadForm.file"
            accept=".pdf,.jpeg,.jpg,.png,.txt,.doc,.docx"
            placeholder="Choose a file or drop it here..."
            drop-placeholder="Drop file here..."
            required
          />
          <small class="text-muted">
            Allowed: PDF, JPEG, JPG, PNG, TXT, DOC, DOCX (Max: 10MB)
          </small>
        </b-form-group>
      </b-form>
    </b-modal>

    <h1 class="page-title">
      Patient Profile — <span class="fw-semi-bold">{{ fullName }}</span>
    </h1>

    <div class="row">
      <div class="col-lg-10 mx-auto">
        <!-- Patient Summary Card -->
        <div class="card shadow-sm rounded-3 mb-4">
          <div class="card-body">
            <div class="d-flex align-items-start">
              <div
                class="avatar rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                style="width:72px;height:72px;"
              >
                <i class="fa fa-user text-muted" style="font-size: 28px;"></i>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center">
                  <h3 class="mb-0 mr-3">{{ fullName }}</h3>
                  <span class="badge" :class="statusClass">
                    {{ capFirst(patientDetails.status || '—') }}
                  </span>
                </div>

                <div class="text-muted mt-1">
                  Patient ID: <span class="text-dark">{{ patientDetails.id }}</span>
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
                        <strong>First Visit:</strong> {{ formatDateTime(patientDetails.visit_date) }}
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
                          <a :href="'tel:' + patientDetails.phone">{{ patientDetails.phone }}</a>
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

        <!-- Consultation History with Pagination -->
        <div class="card shadow-sm border-0 mb-4 consult-history">
          <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <span class="ch-icon mr-2"><i class="fa fa-history"></i></span>
              <div>
                <h5 class="mb-0 font-weight-bold">Consultation History</h5>
                <small class="text-muted d-block">
                  {{ consultations.length ? ('Showing ' + chPage + ' of ' + chTotalPages) : 'No consultations' }}
                </small>
              </div>
            </div>

            <div class="ch-pager d-none d-md-flex">
              <b-button
                size="sm"
                variant="outline-secondary"
                :disabled="chPage <= 1"
                @click="chPrev"
                class="mr-2"
              >
                <i class="fa fa-chevron-left mr-1"></i> Prev
              </b-button>
              <b-button
                size="sm"
                variant="outline-secondary"
                :disabled="chPage >= chTotalPages"
                @click="chNext"
              >
                Next <i class="fa fa-chevron-right ml-1"></i>
              </b-button>
            </div>
          </div>

          <div class="card-body pt-0">
            <b-alert v-if="chError" show variant="danger" class="alert-sm mb-3">
              {{ chError }}
            </b-alert>

            <div v-if="loading" class="text-center py-5">
              <b-spinner></b-spinner>
              <p class="mt-2">Loading consultations...</p>
            </div>

            <div v-else-if="currentConsultation" class="ch-sheet">
              <!-- Top meta row -->
              <div class="d-flex flex-wrap align-items-center mb-3">
                <div class="d-flex align-items-center mr-3">
                  <div class="ch-datepill mr-2">
                    <i class="fa fa-calendar"></i>
                  </div>
                  <div>
                    <div class="font-weight-bold">
                      {{ formatDate(currentConsultation.start_at) }}
                    </div>
                    <small class="text-muted">
                      {{ timeOnly(currentConsultation.start_at) }} – {{ timeOnly(currentConsultation.end_at) }}
                    </small>
                  </div>
                </div>

                <span
                  class="badge ml-auto ch-status"
                  :class="consultationStatusClass(currentConsultation.status)"
                >
                  {{ capFirst(statusLabel(currentConsultation.status)) }}
                </span>
              </div>

              <div class="ch-divider"></div>

              <!-- Quick facts -->
              <div class="row ch-facts">
                <div class="col-md-4 mb-3">
                  <div class="ch-label">Doctor</div>
                  <div class="ch-value">
                    {{ (currentConsultation.doctor && currentConsultation.doctor.name) || '—' }}
                    <span
                      v-if="currentConsultation.doctor && currentConsultation.doctor.is_super_doctor"
                      class="badge badge-light border ml-2"
                    >Super</span>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="ch-label">Location</div>
                  <div class="ch-value">
                    {{ (currentConsultation.location && currentConsultation.location.name) || '—' }}
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="ch-label">Booked By</div>
                  <div class="ch-value">
                    {{ (currentConsultation.creator && currentConsultation.creator.name) || '—' }}
                  </div>
                </div>
              </div>

              <div class="ch-divider"></div>

              <!-- Clinical Details -->
              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="ch-label">Reason</div>
                  <div class="ch-block prewrap">{{ currentConsultation.reason || '—' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Instruction</div>
                  <div class="ch-block prewrap">{{ currentConsultation.instruction || '—' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Past Medical History</div>
                  <div class="ch-block prewrap">{{
                    ((currentConsultation.medical_history && currentConsultation.medical_history.history)
                      || (currentConsultation.medical_histories && currentConsultation.medical_histories[0] && currentConsultation.medical_histories[0].history)
                      || '—').toString().trimStart()
                  }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Examination</div>
                  <div class="ch-block prewrap">{{ currentConsultation.examination || '—' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Diagnosis</div>
                  <div class="ch-block prewrap">{{ currentConsultation.diagnosis || '—' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Management</div>
                  <div class="ch-block prewrap">{{ currentConsultation.management || '—' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                  <div class="ch-label">Investigation</div>
                  <div class="ch-block prewrap">{{ currentConsultation.investigation || '—' }}</div>
                </div>
              </div>

              <!-- Attachments Section -->
              <div class="ch-divider"></div>
              <div class="attachments-section">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <div class="ch-label mb-0">
                    <i class="fa fa-paperclip mr-1"></i>
                    Attachments ({{ consultationDocuments.length }})
                  </div>
                  <b-button
                    size="sm"
                    variant="primary"
                    @click="showUploadModal"
                  >
                    <i class="fa fa-upload mr-1"></i> Upload Document
                  </b-button>
                </div>

                <div v-if="loadingDocuments" class="text-center py-3">
                  <b-spinner small></b-spinner>
                  <span class="ml-2">Loading documents...</span>
                </div>

                <div v-else-if="consultationDocuments.length === 0" class="text-center text-muted py-3">
                  <i class="fa fa-inbox" style="font-size: 24px;"></i>
                  <p class="mb-0 mt-2">No documents uploaded yet</p>
                </div>

                <div v-else class="documents-list">
                  <div
                    v-for="doc in consultationDocuments"
                    :key="doc.id"
                    class="document-item mb-2"
                  >
                    <div class="d-flex align-items-center p-2 border rounded">
                      <div class="doc-icon mr-3">
                        <i :class="getFileIcon(doc.file_type)" style="font-size: 24px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="doc-title font-weight-bold">{{ doc.title }}</div>
                        <small class="text-muted">
                          {{ doc.file_name }} • {{ formatFileSize(doc.file_size) }} • 
                          {{ formatDateTime(doc.created_at) }}
                        </small>
                      </div>
                      <div class="doc-actions">
                        <b-button
                          size="sm"
                          variant="outline-primary"
                          class="mr-2"
                          @click="downloadDocument(doc.id, doc.file_name)"
                        >
                          <i class="fa fa-download"></i>
                        </b-button>
                        <b-button
                          size="sm"
                          variant="outline-danger"
                          @click="confirmDeleteDocument(doc.id)"
                        >
                          <i class="fa fa-trash"></i>
                        </b-button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Prescription Section -->
              <div v-if="getPrescription(currentConsultation)" class="mt-3">
                <div class="ch-divider"></div>
                <div class="prescription-display">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="ch-label mb-0">
                      <i class="fa fa-prescription-bottle-alt mr-1"></i>
                      Prescription #{{ getPrescription(currentConsultation).id }}
                    </div>
                    <div>
                      <b-button
                        size="sm"
                        variant="warning"
                        class="mr-2"
                        @click="openEditPrescription(currentConsultation)"
                      >
                        <i class="fa fa-edit mr-1"></i> Edit Prescription
                      </b-button>
                      <b-button
                        size="sm"
                        variant="info"
                        @click="goToPrescription(currentConsultation)"
                      >
                        <i class="fa fa-eye mr-1"></i> View Full Details
                      </b-button>
                    </div>
                  </div>

                  <!-- Doctor's Notes -->
                  <div v-if="getPrescription(currentConsultation).notes" class="prescription-notes mb-3">
                    <strong><i class="fa fa-sticky-note mr-1"></i> Doctor's Notes:</strong>
                    <p class="mb-0 mt-1" style="white-space: pre-line;">{{ getPrescription(currentConsultation).notes }}</p>
                  </div>

                  <!-- Prescribed Drugs Table -->
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <thead class="thead-light">
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
                        <tr
                          v-for="(item, idx) in getPrescriptionItems(currentConsultation)"
                          :key="item.id || idx"
                        >
                          <td>{{ idx + 1 }}</td>
                          <td>{{ (item.drug && item.drug.name) || '—' }}</td>
                          <td>{{ (item.drug && item.drug.unit) || '—' }}</td>
                          <td class="text-right">{{ formatMoney(item.unit_price) }}</td>
                          <td class="text-right">{{ item.quantity_prescribed || item.quantity || 0 }}</td>
                          <td class="text-right">{{ item.quantity_dispensed || 0 }}</td>
                          <td>{{ item.dosage_instructions || '—' }}</td>
                          <td class="text-right">
                            {{ formatMoney((item.quantity_prescribed || item.quantity || 0) * item.unit_price) }}
                          </td>
                        </tr>
                        <tr v-if="!getPrescriptionItems(currentConsultation).length">
                          <td colspan="8" class="text-center text-muted">No drugs prescribed</td>
                        </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="7" class="text-right"><strong>Grand Total:</strong></td>
                          <td class="text-right">
                            <strong>{{ formatMoney(getPrescriptionTotal(currentConsultation)) }}</strong>
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Footer actions -->
              <div class="d-flex align-items-center justify-content-between mt-3">
                <small class="text-muted">
                  Created: {{ formatDateTime(currentConsultation.created_at) }}
                </small>

                <div class="d-flex">
                  <b-button
                    size="sm"
                    variant="primary"
                    class="mr-2"
                    @click="openEditNotes(currentConsultation)"
                  >
                    <i class="fa fa-edit mr-1"></i> Edit Notes
                  </b-button>

                  <b-button
                    size="sm"
                    variant="outline-secondary"
                    @click="reloadConsultation"
                  >
                    <i class="fa fa-sync-alt mr-1"></i>
                    Refresh
                  </b-button>
                </div>
              </div>
            </div>

            <div v-else class="text-center text-muted py-5">
              <i class="fa fa-info-circle mb-2 d-block" style="font-size:20px;"></i>
              No consultations to show.
            </div>
          </div>

          <!-- Compact pager for mobile -->
          <div class="card-footer bg-white border-0 pt-0 d-flex d-md-none align-items-center justify-content-between">
            <b-button size="sm" variant="outline-secondary" :disabled="chPage <= 1" @click="chPrev">
              ← Previous
            </b-button>
            <small class="text-muted">
              {{ consultations.length ? ('Page ' + chPage + ' of ' + chTotalPages) : '' }}
            </small>
            <b-button size="sm" variant="outline-secondary" :disabled="chPage >= chTotalPages" @click="chNext">
              Next →
            </b-button>
          </div>
        </div>
      </div>
    </div>

    <vue-element-loading
      :active="submitting"
      :is-full-screen="true"
      :size="'80'"
      :color="'#FF6700'"
      :text="'Processing...'"
    />
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "PatientProfilePage",
  data() {
    return {
      loading: false,
      submitting: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      user_role: userRole(),
      patientDetails: {},
      consultations: [],
      chPage: 1,
      chError: null,
      drugOptions: [],
      
      // Documents
      consultationDocuments: [],
      loadingDocuments: false,
      uploadForm: {
        title: "",
        file: null,
      },
      
      // Edit forms
      editForm: {
        prescription_id: null,
        consultation_id: null,
        notes: "",
        drugs: [],
        originalDrugs: []
      },
      
      notesForm: {
        consultation_id: null,
        reason: "",
        instruction: "",
        examination: "",
        diagnosis: "",
        management: "",
        investigation: ""
      }
    };
  },
  computed: {
    fullName() {
      const f = this.patientDetails.first_name || "";
      const l = this.patientDetails.last_name || "";
      const n = (f + " " + l).trim();
      return n || "—";
    },
    statusClass() {
      const s = (this.patientDetails.status || "").toLowerCase();
      if (s === "completed") return "badge-success";
      if (s === "waiting" || s === "pending" || s === "booked") return "badge-warning";
      return "badge-secondary";
    },
    drugSelectOptions() {
      return this.drugOptions.map(d => {
        const formattedDate = d.expiry_date
          ? new Date(d.expiry_date).toLocaleDateString("en-GB", {
              day: "2-digit",
              month: "short",
              year: "numeric"
            })
          : "—";

        return {
          value: d.id,
          text: `${d.name} - Batch ${d.batch_number || "N/A"} - Expiring ${formattedDate}`
        };
      });
    },
    chTotalPages() {
      return Math.max(1, this.consultations.length);
    },
    currentConsultation() {
      if (!this.consultations || !this.consultations.length) return null;
      const idx = this.chPage - 1;
      if (idx < 0 || idx >= this.consultations.length) return null;
      return this.consultations[idx];
    },
  },
  watch: {
    currentConsultation(newVal) {
      if (newVal && newVal.id) {
        this.loadConsultationDocuments(newVal.id);
      }
    },
  },
  methods: {
    loadPatient(id) {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "walk_in_patient_details/" + id + "/walk-in-patient-details", authHeader())
        .then(({ data }) => {
          this.loading = false;
          this.patientDetails = (data && data.data) ? data.data : {};
        })
        .catch((error) => {
          this.loading = false;
          this.$swal("Error!", "There was an error loading patient: " + error, "error");
        });
    },

    loadConsultations(patientId) {
      this.loading = true;
      const url = this.$base_url + "walk-in-patient/" + patientId + "/consultation_history";
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          this.loading = false;
          let list = (data && data.data) ? data.data : [];
          // Sort by date descending (most recent first)
          this.consultations = list.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
          });
          this.chError = null;
          this.chPage = 1; // Reset to first page
        })
        .catch((error) => {
          this.loading = false;
          this.chError =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
        });
    },

    loadDrugOptions() {
      this.$axios
        .get(this.$base_url + "drugs", authHeader())
        .then(({ data }) => {
          const list = (data && data.data && data.data.drugs && data.data.drugs.data) ? data.data.drugs.data : [];
          this.drugOptions = list.map(function (d) {
            return {
              id: d.id,
              name: d.name,
              batch_number: d.batch_number,
              expiry_date: d.expiry_date,
              price: Number(d.selling_price || 0),
              stock_quantity: Number(d.stock_quantity || 0),
            };
          });
        })
        .catch((error) => {
          console.error("Error loading documents:", error);
        });
    },

    showUploadModal() {
      if (!this.currentConsultation || !this.currentConsultation.id) {
        this.$swal("Error", "No consultation selected", "error");
        return;
      }
      this.$bvModal.show("modal-upload-document");
    },

    resetUploadForm() {
      this.uploadForm = {
        title: "",
        file: null,
      };
    },

    handleUploadDocument(bvModalEvt) {
      bvModalEvt.preventDefault();
      
      if (!this.uploadForm.title || !this.uploadForm.file) {
        this.$swal("Validation", "Please provide both title and file", "warning");
        return;
      }

      const formData = new FormData();
      formData.append("consultation_id", this.currentConsultation.id);
      formData.append("uploaded_by", this.currentConsultation.doctor.id);
      formData.append("title", this.uploadForm.title);
      formData.append("document", this.uploadForm.file);

      this.$axios
        .post(this.$base_url + "consultation-documents/upload", formData, {
          headers: {
            ...authHeader().headers,
            "Content-Type": "multipart/form-data",
          },
        })
        .then(({ data }) => {
          this.$swal("Success", "Document uploaded successfully", "success");
          this.$bvModal.hide("modal-upload-document");
          this.resetUploadForm();
          this.loadConsultationDocuments(this.currentConsultation.id);
        })
        .catch((error) => {
          const msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
          this.$swal("Error", msg, "error");
        });
    },

    downloadDocument(docId, fileName) {
      this.$axios
        .get(this.$base_url + "consultation-documents/" + docId + "/download", {
          ...authHeader(),
          responseType: "blob",
        })
        .then((response) => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", fileName);
          document.body.appendChild(link);
          link.click();
          link.remove();
          window.URL.revokeObjectURL(url);
        })
        .catch((error) => {
          const msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
          this.$swal("Error", "Failed to download: " + msg, "error");
        });
    },

    confirmDeleteDocument(docId) {
      this.$swal({
        title: "Are you sure?",
        text: "This document will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteDocument(docId);
        }
      });
    },

    deleteDocument(docId) {
      this.$axios
        .delete(this.$base_url + "consultation-documents/" + docId, authHeader())
        .then(({ data }) => {
          this.$swal("Deleted!", "Document has been deleted.", "success");
          this.loadConsultationDocuments(this.currentConsultation.id);
        })
        .catch((error) => {
          const msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
          this.$swal("Error", "Failed to delete: " + msg, "error");
        });
    },

    getFileIcon(fileType) {
      const type = (fileType || "").toLowerCase();
      if (type === "pdf") return "fa fa-file-pdf text-danger";
      if (type === "doc" || type === "docx") return "fa fa-file-word text-primary";
      if (type === "txt") return "fa fa-file-alt text-secondary";
      if (type === "jpg" || type === "jpeg" || type === "png") return "fa fa-file-image text-success";
      return "fa fa-file text-muted";
    },

    formatFileSize(bytes) {
      if (!bytes) return "0 B";
      const k = 1024;
      const sizes = ["B", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + " " + sizes[i];
    },

    getPrescription(consultation) {
      if (!consultation) return null;
      if (consultation.prescription && consultation.prescription.id) {
        return consultation.prescription;
      }
      if (consultation.prescriptions && consultation.prescriptions.length > 0) {
        return consultation.prescriptions[0];
      }
      return null;
    },

    getPrescriptionItems(consultation) {
      const prescription = this.getPrescription(consultation);
      if (!prescription) return [];
      
      // Handle different possible response structures
      if (prescription.items && Array.isArray(prescription.items)) {
        return prescription.items;
      }
      if (prescription.prescription_items && Array.isArray(prescription.prescription_items)) {
        return prescription.prescription_items;
      }
      return [];
    },

    getPrescriptionTotal(consultation) {
      const items = this.getPrescriptionItems(consultation);
      if (!items.length) return 0;
      
      return items.reduce((sum, item) => {
        const qty = Number(item.quantity_prescribed || item.quantity || 0);
        const price = Number(item.unit_price || 0);
        return sum + (qty * price);
      }, 0);
    },

    hasPrescription(c) {
      return this.getPrescription(c) !== null;
    },

    getPrescriptionId(c) {
      const prescription = this.getPrescription(c);
      return prescription ? prescription.id : null;
    },

    goToPrescription(c) {
      const pid = this.getPrescriptionId(c);
      if (!pid) return;
      this.$router.push({ name: "prescriptionpage", params: { prescription: pid } });
    },

    openEditPrescription(consultation) {
      const prescription = this.getPrescription(consultation);
      if (!prescription) {
        this.$swal("Error", "No prescription found for this consultation", "error");
        return;
      }

      // Check if prescription has items loaded
      if (!prescription.items || !Array.isArray(prescription.items) || prescription.items.length === 0) {
        this.$swal("Error", "Prescription items not loaded. Please refresh and try again.", "error");
        return;
      }

      this.editForm = {
        prescription_id: prescription.id,
        consultation_id: consultation.id,
        notes: prescription.notes || "",
        drugs: prescription.items.map(item => ({
          id: item.id,
          drug_id: item.drug_id,
          quantity_prescribed: item.quantity_prescribed || item.quantity,
          dosage_instructions: item.dosage_instructions,
        })),
        originalDrugs: JSON.parse(JSON.stringify(prescription.items.map(item => ({
          id: item.id,
          drug_id: item.drug_id,
          quantity_prescribed: item.quantity_prescribed || item.quantity
        }))))
      };

      this.$bvModal.show("modal-edit-prescription");
    },

    openEditNotes(consultation) {
      this.notesForm = {
        consultation_id: consultation.id,
        reason: consultation.reason || "",
        instruction: consultation.instruction || "",
        examination: consultation.examination || "",
        diagnosis: consultation.diagnosis || "",
        management: consultation.management || "",
        investigation: consultation.investigation || ""
      };

      this.$bvModal.show("modal-edit-notes");
    },

    handleUpdatePrescription(bvModalEvt) {
      bvModalEvt.preventDefault();

      if (!this.editForm.drugs.length) {
        this.$swal("Validation", "Please add at least one drug", "warning");
        return;
      }

      const invalid = this.editForm.drugs.some(d => {
        return !d.drug_id || !d.quantity_prescribed || !d.dosage_instructions;
      });

      if (invalid) {
        this.$swal("Validation", "Please complete all drug fields", "warning");
        return;
      }

      this.submitting = true;

      const payload = {
        prescription_id: this.editForm.prescription_id,
        notes: this.editForm.notes,
        drugs: this.editForm.drugs.map(d => ({
          id: d.id || null,
          drug_id: d.drug_id,
          quantity_prescribed: d.quantity_prescribed,
          dosage_instructions: d.dosage_instructions
        })),
        original_drugs: this.editForm.originalDrugs
      };

      this.$axios
        .put(
          this.$base_url + "prescriptions/" + this.editForm.prescription_id + "/update",
          payload,
          authHeader()
        )
        .then(({ data }) => {
          this.submitting = false;
          
          // Check the message type from backend
          const messageType = data.message_type || 'success';
          const summary = data.summary || {};
          
          // Build detailed message for user
          let alertMessage = data.message;
          
          // Show appropriate alert based on what happened
          if (messageType === 'warning') {
            // Some actions were blocked
            this.$swal({
              title: "Prescription Updated with Restrictions",
              html: alertMessage,
              icon: "warning",
              confirmButtonText: "OK"
            });
          } else {
            // Everything succeeded
            this.$swal("Success!", alertMessage, "success");
          }
          
          this.$bvModal.hide("modal-edit-prescription");
          this.loadConsultations(this.$route.params.id);
        })
        .catch((error) => {
          this.submitting = false;
          const msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
          this.$swal("Error", msg, "error");
        });
    },

    handleUpdateNotes(bvModalEvt) {
      bvModalEvt.preventDefault();

      this.submitting = true;

      this.$axios
        .put(
          this.$base_url + "consultations/" + this.notesForm.consultation_id,
          this.notesForm,
          authHeader()
        )
        .then(({ data }) => {
          this.submitting = false;
          this.$swal("Success!", "Consultation notes updated successfully", "success");
          this.$bvModal.hide("modal-edit-notes");
          this.loadConsultations(this.$route.params.id);
        })
        .catch((error) => {
          this.submitting = false;
          const msg =
            (error && error.response && error.response.data && error.response.data.message) ||
            (error && error.message) || error + '';
          this.$swal("Error", msg, "error");
        });
    },

    resetEditForm() {
      this.editForm = {
        prescription_id: null,
        consultation_id: null,
        notes: "",
        drugs: [],
        originalDrugs: []
      };
    },

    resetNotesForm() {
      this.notesForm = {
        consultation_id: null,
        reason: "",
        instruction: "",
        examination: "",
        diagnosis: "",
        management: "",
        investigation: ""
      };
    },

    addEditDrug() {
      this.editForm.drugs.push({
        id: null,
        drug_id: "",
        quantity_prescribed: 1,
        dosage_instructions: ""
      });
    },

    removeEditDrug(index) {
      this.editForm.drugs.splice(index, 1);
    },

    selectedEditDrug(idx) {
      const id = (this.editForm.drugs[idx] && this.editForm.drugs[idx].drug_id) || null;
      if (!id) return null;
      for (let i = 0; i < this.drugOptions.length; i++) {
        if (String(this.drugOptions[i].id) === String(id)) return this.drugOptions[i];
      }
      return null;
    },

    chPrev() { 
      if (this.chPage > 1) this.chPage -= 1; 
    },
    
    chNext() { 
      if (this.chPage < this.chTotalPages) this.chPage += 1; 
    },

    reloadConsultation() {
      this.loadConsultations(this.$route.params.id);
    },

    consultationStatusClass(status) {
      const s = (status || 0);
      if (s === 4 || s === 'completed' || s === 'done') return "badge-success";
      if (s === 0 || s === 'pending' || s === 'booked') return "badge-warning";
      return "badge-secondary";
    },

    statusLabel(status) {
      if (parseInt(status) === 4) {
        return "Notes Added";
      }
      return "No Notes";
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
        : d.toLocaleString("en-AU", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
          });
    },

    timeOnly(val) {
      if (!val) return "—";
      const d = new Date(val);
      if (isNaN(d.getTime())) return val;
      const hh = String(d.getHours()).padStart(2, "0");
      const mm = String(d.getMinutes()).padStart(2, "0");
      return hh + ":" + mm;
    }
  },
  created() {
    const patientId = this.$route.params.id;
    this.loadPatient(patientId);
    this.loadConsultations(patientId);
    this.loadDrugOptions();
  }
};
</script>

<style scoped>
.ch-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #f3f4f6;
  color: #111827;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.consult-history .ch-pager .btn {
  min-width: 92px;
}

.ch-sheet {
  border: 1px solid #eef1f5;
  border-radius: 12px;
  padding: 18px;
  background: #fafbfc;
}

.ch-divider {
  border-top: 1px dashed #e6e9ef;
  margin: 14px 0;
}

.ch-label {
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: .04em;
  color: #6b7280;
  margin-bottom: 6px;
  font-weight: 600;
}

.ch-value {
  font-weight: 600;
  color: #111827;
}

.ch-block {
  background: #fff;
  border: 1px solid #eef1f5;
  border-radius: 10px;
  padding: 10px 12px;
  min-height: 44px;
}

.ch-datepill {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: #eef2ff;
  color: #3730a3;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}

.ch-status.badge-success {
  background: #16a34a !important;
}

.ch-status.badge-warning {
  background: #f59e0b !important;
  color: #111827;
}

.ch-status.badge-secondary {
  background: #6b7280 !important;
}

.prewrap {
  white-space: pre-wrap;
  word-break: break-word;
}

.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: #111827; }
.badge-secondary { background: #6c757d; color: white; }

.badge-soft {
  background: #f2f4f7;
  color: #334155;
  border-radius: 9999px;
  padding: 0.25rem 0.5rem;
  font-weight: 600;
  font-size: 0.75rem;
}

.prescription-display {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
}

.prescription-notes {
  background: white;
  padding: 10px 12px;
  border-radius: 6px;
  border-left: 3px solid #007bff;
}

.documents-list .document-item {
  transition: all 0.2s ease;
}

.documents-list .document-item:hover {
  background: #f8f9fa;
}

.doc-title {
  font-size: 0.9rem;
}

.gap-2 > * {
  margin-right: .5rem;
  margin-bottom: .5rem;
}

.drug-select {
  min-width: 100%;
}

.form-label.small {
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.avatar i {
  font-size: 28px;
}

.table td,
.table th {
  vertical-align: middle;
}
</style>