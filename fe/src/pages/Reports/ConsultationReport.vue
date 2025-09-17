<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      Consultations - <span class="fw-semi-bold">Report</span>
    </h1>

    <b-row>
      <b-col xs="12">
        <consultation-table></consultation-table>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import ConsultationTable from '../../components/Patients/ConsultationTable.vue';
import ProcessedPayoutTable from '../../components/Payments/ProcessedPayoutTable.vue';
export default {
  components: { ProcessedPayoutTable, ConsultationTable },
  name: "PaymentReport",
};
</script>


