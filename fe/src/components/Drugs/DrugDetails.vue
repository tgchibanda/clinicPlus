<template>
  <div>
    <b-form @submit.prevent="handleSubmit">
      <!-- Top error -->
      <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
        {{ errorMessage }}
      </b-alert>

      <!-- Name -->
      <b-form-group label="Drug Name *">
        <b-form-input
          v-model.trim="form.name"
          placeholder="e.g., Amoxicillin 500mg"
          :state="fieldState('name')"
          required
        />
        <b-form-invalid-feedback>
          {{ firstError('name') }}
        </b-form-invalid-feedback>
      </b-form-group>

      <!-- Category + Unit -->
      <div class="form-row">
        <div class="col-md-6">
          <b-form-group label="Category">
            <b-form-input
              v-model.trim="form.category"
              placeholder="e.g., Antibiotic"
              :state="fieldState('category')"
            />
            <b-form-invalid-feedback>
              {{ firstError('category') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>
        <div class="col-md-6">
          <b-form-group label="Unit *">
            <b-form-input
              v-model.trim="form.unit"
              placeholder="e.g., tablet, bottle, ml"
              :state="fieldState('unit')"
              required
            />
            <b-form-invalid-feedback>
              {{ firstError('unit') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>



      </div>

      <!-- Prices -->
      <div class="form-row">
        <div class="col-md-4">
          <b-form-group label="Batch Number *">
            <b-input-group>
              <b-form-input
                type="text"
                v-model.number="form.batch_number"
                :state="fieldState('batch_number')"
                required
              />
            </b-input-group>
            <b-form-invalid-feedback>
              {{ firstError('batch_number') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>
        
        <div class="col-md-4">
          <b-form-group label="Selling Price *">
            <b-input-group>
              <b-input-group-prepend is-text>$</b-input-group-prepend>
              <b-form-input
                type="number"
                step="0.01"
                min="0"
                v-model.number="form.selling_price"
                placeholder="0.00"
                :state="fieldState('selling_price')"
                required
              />
            </b-input-group>
            <b-form-invalid-feedback>
              {{ firstError('selling_price') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>

        <div class="col-md-4">
          <b-form-group label="Stock Quantity *">
            <b-form-input
              type="text"
              v-model.number="form.stock_quantity"
              placeholder="e.g., 100"
              :state="fieldState('stock_quantity')"
              required
            />
            <b-form-invalid-feedback>
              {{ firstError('stock_quantity') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>

      </div>

      <!-- Stock -->
      <div class="form-row">
        <div class="col-md-6">
          <b-form-group label="Minimum Stock Level *">
            <b-form-input
              type="text"
              v-model.number="form.minimum_stock_level"
              placeholder="e.g., 10"
              :state="fieldState('minimum_stock_level')"
              required
            />
            <b-form-invalid-feedback>
              {{ firstError('minimum_stock_level') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>
        <div class="col-md-6">
          <b-form-group label="Expiry Date">
            <b-form-datepicker
              v-model="form.expiry_date"
              placeholder="YYYY-MM-DD"
              :state="fieldState('expiry_date')"
            />
            <b-form-invalid-feedback>
              {{ firstError('expiry_date') }}
            </b-form-invalid-feedback>
          </b-form-group>
        </div>
      </div>

      <!-- Description -->
      <b-form-group label="Description">
        <b-form-textarea
          v-model.trim="form.description"
          placeholder="Basic use of the medication…"
          rows="3"
          max-rows="6"
          :state="fieldState('description')"
        />
        <b-form-invalid-feedback>
          {{ firstError('description') }}
        </b-form-invalid-feedback>
      </b-form-group>

      <b-button block type="submit" variant="primary" :disabled="submitting">
        <b-spinner small v-if="submitting" class="mr-2" /> Save Drug
      </b-button>
    </b-form>
  </div>
</template>

<script>
import authHeader from "../../services/auth-header";

export default {
  name: "DrugCreateModal",
  data() {
    return {
      submitting: false,
      errorMessage: null,
      errors: {},
      form: {
        name: "",
        category: "",
        unit: "",
        batch_number: "",
        selling_price: "",
        stock_quantity: "",
        minimum_stock_level: "",
        expiry_date: null,  // "YYYY-MM-DD"
        description: "",
      },
    };
  },
  methods: {
    fieldState(field) {
      return field in this.errors ? false : null;
    },
    firstError(field) {
      return this.errors?.[field]?.[0] || true;
    },
    handleSubmit() {
      this.submitting = true;
      this.errorMessage = null;
      this.errors = {};

      // Build payload exactly as backend expects
      const payload = {
        name: this.form.name,
        category: this.form.category || null,
        unit: this.form.unit,
        batch_number: this.form.batch_number,
        selling_price: this.form.selling_price,
        stock_quantity: this.form.stock_quantity,
        minimum_stock_level: this.form.minimum_stock_level,
        expiry_date: this.form.expiry_date, // keep as YYYY-MM-DD string or null
        description: this.form.description || null,
      };

      this.$axios
        .post(this.$base_url + "drugs", payload, authHeader())
        .then(({ data }) => {
          // Notify parent to refresh table and close modal
          this.$emit("saved", data?.data || null);
          this.resetForm();
          this.submitting = false;
          this.$bvToast?.toast("Drug created successfully.", {
            title: "Success",
            variant: "success",
            autoHideDelay: 3000,
          });
        })
        .catch((error) => {
          this.submitting = false;
          this.errorMessage =
            error?.response?.data?.message ||
            error?.message ||
            "An error occurred";
          this.errors = error?.response?.data?.errors || {};
        });
    },
    resetForm() {
      this.form = {
        name: "",
        category: "",
        unit: "",
        batch_number: "",
        stock_quantity: "",
        minimum_stock_level: "",
        expiry_date: null,
        description: "",
        selling_price: null,
      };
      this.errors = {};
      this.errorMessage = null;
    },
  },
};
</script>
