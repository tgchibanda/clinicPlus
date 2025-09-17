<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      Add - <span class="fw-semi-bold">Directory</span>
    </h1>
    <b-row v-if="user_role.role == 'admin'">
      <b-col md="6" xl="6" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Directory" close>
          <b-form @submit.prevent="handleSubmit">
            <b-alert class="alert-sm" variant="danger" :show="!!errorMessage">
              {{ errorMessage }}
            </b-alert>

            <b-form-group label="Type">
              <b-form-select
                v-model="directory.type"
                :state="'type' in errors ? false : null"
              >
                <option value="">Choose Service Provide Type</option>
                <option value="Pharmacies">Pharmacies</option>
                <option value="Pathology">Pathology</option>
                <option value="Radiology">Radiology</option>
                <option value="Allied Health">Allied Health</option>
              </b-form-select>
              <b-form-invalid-feedback>
                {{ "type" in errors ? errors.type[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider name *">
              <b-form-input
                v-model="directory.name"
                type="text"
                placeholder="Enter name"
                :state="'name' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "name" in errors ? errors.name[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider address *">
              <b-form-input
                v-model="directory.address"
                type="text"
                placeholder="Enter address"
                :state="'address' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "address" in errors ? errors.address[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider mobile_no *">
              <b-form-input
                v-model="directory.mobile_no"
                type="text"
                placeholder="Enter mobile_no"
                :state="'mobile_no' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "mobile_no" in errors ? errors.mobile_no[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider landline *">
              <b-form-input
                v-model="directory.landline"
                type="text"
                placeholder="Enter landline"
                :state="'landline' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "landline" in errors ? errors.landline[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider additional_contacts *">
              <b-form-input
                v-model="directory.additional_contacts"
                type="text"
                placeholder="Enter additional_contacts"
                :state="'additional_contacts' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{
                  "additional_contacts" in errors
                    ? errors.additional_contacts[0]
                    : true
                }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider facebook *">
              <b-form-input
                v-model="directory.facebook"
                type="text"
                placeholder="Enter facebook"
                :state="'facebook' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "facebook" in errors ? errors.facebook[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Service provider instagram *">
              <b-form-input
                v-model="directory.instagram"
                type="text"
                placeholder="Enter instagram"
                :state="'instagram' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "instagram" in errors ? errors.instagram[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-button block type="submit" variant="primary">Submit</b-button>
          </b-form>
        </Widget>
      </b-col>
      <b-col md="6" xl="6" sm="6" xs="12">
        <Widget class="h-100 mb-0" title="Manage" close>
          <div class="table-responsive" v-if="user_role.role == 'admin'">
            <table class="table table-striped table-lg mb-0 requests-table">
              <thead>
                <tr class="text-muted">
                  <th>Provider Name</th>
                  <th>Provider Mobile</th>
                  <th>Manage</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in directories.data" :key="row.id">
                  <td>{{ row.name }}</td>
                  <td>{{ row.mobile_no }}</td>
                  <td>
                    <b-button variant="danger" @click="deleteRow(row.id)"
                      ><span class="fa fa-times" /> Delete</b-button
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Widget>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "AddDirectory",
  data() {
    return {
      errorMessage: null,
      loading: false,
      errors: {},
      directories: {},
      directory: {
        name: "",
        address: "",
        mobile_no: "",
        landline: "",
        additional_contacts: "",
        facebook: "",
        twitter: "",
        instagram: "",
        gps: "",
      },
      user_role: userRole(),
    };
  },
  methods: {
    handleSubmit() {
      this.loading = true;
      this.$axios
        .post(this.$base_url + "directory", this.directory, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          this.loadDirectory();
        })
        .catch((error) => {
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
          this.errors = error.response.data.errors;
          // this.$swal("error!", "There was an error" + error, "error");
        });
    },
    deleteRow(id) {
      this.loading = true;
      this.$axios
        .delete(this.$base_url + "directory/" + id, authHeader())
        .then((response) => {
          this.$swal("Success!", response.message, "success");
          this.loadDirectory();
        })
        .catch((error) => {
          this.message =
            (error.response &&
              error.response.data &&
              error.response.data.message) ||
            error.message ||
            error.toString();
          this.errorMessage = this.message;
          this.errors = error.response.data.errors;
          // this.$swal("error!", "There was an error" + error, "error");
        });
    },
    loadDirectory() {
      this.loading = true;
      this.$axios
        .get(this.$base_url + "directory", authHeader())
        .then(({ data }) => {
          this.directories = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error" + error, "error");
        });
    },
  },
  created() {
    if (this.user_role.role == "admin") {
      this.loadDirectory();
    }
  },
};
</script>


