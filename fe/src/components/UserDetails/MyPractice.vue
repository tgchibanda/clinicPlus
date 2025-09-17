<template>
  <b-row v-if="this.user_role.role == 'doctor'">
    <b-col md="6" xl="3" sm="6" xs="12">
      <div class="pb-xlg h-100">
        <Widget class="h-100 mb-0" title="Registration Number">
          <b-form @submit.prevent="handleSubmit">
            <b-form-group label="Registration Number*">
              <b-form-input
                type="text"
                v-model="doctor.reg_number"
                placeholder="Registration Number"
                :state="'reg_number' in errors ? false : null"
              >
              </b-form-input>
            </b-form-group>
            <b-button block type="submit" variant="primary">Submit</b-button>
          </b-form>
        </Widget>
      </div>
    </b-col>
    <b-col md="6" xl="3" sm="6" xs="12">
      <div class="pb-xlg h-100">
        <Widget class="h-100 mb-0" title="Supporting Files">
          <b-form @submit.prevent="handleUpload">
            <b-form-group label="Supporting File Name">
              <b-form-input
                type="text"
                v-model="files.name"
                placeholder="eg National ID"
                :state="'name' in errors ? false : null"
              >
              </b-form-input>
              <b-form-invalid-feedback>
                {{ "name" in errors ? errors.name[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>
            <b-form-group label="Supporting File *">
              <b-form-file
                v-model="file"
                id="file"
                name="file"
                drop-placeholder="Drop file here..."
                placeholder="Choose a file or drop it here..."
              >
              </b-form-file>
              <b-form-invalid-feedback>
                {{ "file" in errors ? errors.file[0] : true }}
              </b-form-invalid-feedback>
            </b-form-group>

            <b-button block type="submit" variant="primary">Submit</b-button>
          </b-form>
        </Widget>
      </div>
    </b-col>
    <b-col md="6" xl="6" sm="6" xs="12">
      <div class="pb-xlg h-100">
        <Widget class="h-100 mb-0" title="Manage Files">
          <div class="table-responsive">
            <table class="table table-striped table-lg mb-0 requests-table">
              <thead>
                <tr class="text-muted">
                  <th>Date</th>
                  <th>File Name</th>
                  <th>File</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in doctorFiles.data" :key="row.id">
                  <td>{{ row.created_at }}</td>
                  <td>{{ row.description }}</td>
                  <td>{{ row.upload_name }}</td>

                  <td>
                    <b-button variant="danger" @click="deleteFile(row)"
                      ><span class="fa fa-times" /> Delete</b-button
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Widget>
      </div>
    </b-col>
  </b-row>
</template>

<script>
import authHeader from "../../services/auth-header";
import authHeaderUpload from "../../services/auth-header-upload";
import userRole from "../../services/user-role";

export default {
  data: () => ({
    file: [],
    user_role: userRole(),
    user_id: JSON.parse(localStorage.getItem("user")).user_id,
    doctorFiles: {},
    files: {
      name: "",
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
    },
    errors: {},
    doctor: {
      reg_number: "",
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
    },
  }),
  methods: {
    handleSubmit() {
      this.$axios
        .post(this.$base_url + "doctor_details", this.doctor, authHeader())
        .then((response) => {
          this.$swal("Success!", "Data saved successfully", "success");
          this.loadDetails();
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
        });
    },

    loadFiles() {
      this.$axios
        .get(this.$base_url + "doctor_files/" + this.user_id, authHeader())
        .then(({ data }) => {
          this.doctorFiles = data;
          this.loading = false;
        });
    },

    loadDetails() {
      this.$axios
        .get(this.$base_url + "doctor_details/" + this.user_id, authHeader())
        .then(({ data }) => {
          this.doctor.reg_number = data.data.reg_number;
          this.loading = false;
        });
    },

    handleUpload() {
      this.loading = true;
      // this.user.append("file", this.file);
      let formData = new FormData();
      formData.append("file", this.file);
      formData.append("name", this.files.name);
      formData.append("user_id", this.files.user_id);

      this.$axios
        .post(this.$base_url + "doctor_files", formData, authHeaderUpload())
        .then((response) => {
          this.$swal("Success!", "Data saved successfully", "success");
          this.loadFiles();
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
        });
    },
  },
  created() {
    this.loadFiles();
    this.loadDetails();
  },
};
</script>

<style lang="scss">
form {
  max-width: 500px;
  margin: 0 auto;
  text-align: left;
}
.col-form-label {
  font-weight: 600;
}
</style>