<!-- eslint-disable max-len -->
<template>
  <section>
    <h1 class="page-title">System - <span class="fw-semi-bold">Users</span></h1>
    <users-table></users-table>
  </section>
</template>

<script>
import UsersTable from '../../components/UserDetails/UsersTable.vue';
export default {
  components: { UsersTable },
  name: "Users",
};
</script>

