<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      My - <span class="fw-semi-bold">Patients</span>
    </h1>

    <b-row>
      <b-col xs="12">
        <walk-in-patients-table></walk-in-patients-table>
      </b-col>
    </b-row>
  </section>
</template>

<script>
export default {
  name: "WalkInPatients",
};
</script>


