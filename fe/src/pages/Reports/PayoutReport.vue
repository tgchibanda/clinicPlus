<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      Payouts - <span class="fw-semi-bold">Report</span>
    </h1>

    <b-row>
      <b-col xs="12">
        <processed-payout-table></processed-payout-table>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import ProcessedPayoutTable from '../../components/Payments/ProcessedPayoutTable.vue';
export default {
  components: { ProcessedPayoutTable },
  name: "PaymentReport",
};
</script>


