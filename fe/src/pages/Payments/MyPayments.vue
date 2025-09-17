<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">
      My - <span class="fw-semi-bold">Payments</span>
    </h1>

    <b-row>
      <b-col xs="12">
        <payments-table></payments-table>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import PaymentStatus from './PaymentStatus.vue';
export default {
  components: { PaymentStatus },
  name: "PaymentStatus",
};
</script>


