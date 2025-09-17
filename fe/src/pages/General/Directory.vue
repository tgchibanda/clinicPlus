<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      Service - <span class="fw-semi-bold">Directory</span>
    </h1>

    <b-row>
      <b-col xs="6">
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" title="Filter" close>
            <b-form-group
              label="Filter Service Providers"
              v-slot="{ ariaDescribedby }"
            >
              <b-form-radio-group
                id="btn-radios-2"
                v-model="selectedType"
                :options="options"
                :aria-describedby="ariaDescribedby"
                button-variant="outline-primary"
                size="lg"
                name="radio-btn-outline"
                buttons
                @change="changeProvider"
              ></b-form-radio-group>
            </b-form-group>
          </Widget>
        </div>
      </b-col>
    </b-row>

    <b-row>
      <b-col
        md="6"
        xl="3"
        sm="6"
        xs="12"
        v-for="row in directory.data"
        :key="row.id"
      >
        <div class="pb-xlg h-100">
          <Widget class="h-100 mb-0" close>
            <div class="clearfix">
              <div class="row flex-nowrap">
                <div xs="12" class="col">
                  <h4 class="m-0">{{ row.name }}</h4>
                </div>
              </div>
              <div class="row">
                <div xs="6" class="col">
                  Call
                  <h6 class="m-0">{{ row.mobile_no }} / {{ row.landline }}</h6>
                </div>
                <div xs="6" class="col">
                  Address
                  <h6 class="m-0">{{ row.address }}</h6>
                </div>
              </div>
              <div class="row mt-1">
                <div xs="6" class="col">
                  Facebook
                  <h6 class="m-0"><a :href="row.facebook" target="_blank">{{ row.name }}</a></h6>
                </div>
                <div xs="6" class="col">
                  Instagram
                  <h6 class="m-0"><a :href="row.instagram" target="_blank">{{ row.name }}</a></h6>
                </div>
              </div>
            </div>
          </Widget>
        </div>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import authHeader from "../../services/auth-header";
import userRole from "../../services/user-role";

export default {
  name: "Directory",
  data() {
    return {
      errorMessage: null,
      loading: false,
      user_id: JSON.parse(localStorage.getItem("user")).user_id,
      directory: {},
      selectedType: "",
      user_role: userRole(),
      isLoading: false,
      options: [
        { text: "Pharmacies", value: "Pharmacies" },
        { text: "Pathology", value: "Pathology" },
        { text: "Radiology", value: "Radiology" },
        { text: "Allied Health", value: "Allied Health" },
      ],
    };
  },
  methods: {
    loadDirectory(selectedType = "") {
      this.loading = true;
      let url = `${this.$base_url}directory`;
      if (selectedType != "") {
        url = `${this.$base_url}directory/${selectedType}`;
      }
      this.$axios
        .get(url, authHeader())
        .then(({ data }) => {
          this.directory = data;
          this.loading = false;
        })
        .catch((error) => {
          this.$swal("error!", "There was an error " + error, "error");
        });
    },
    changeProvider() {
      this.loadDirectory(this.selectedType);
    },
  },
  created() {
    this.loadDirectory();
  },
};
</script>



