<template>
  <div class="social-buttons-clinicPlus">
    <b-button
      variant="primary"
      class="social-button-clinicPlus mb-2"
      @click="socialLoginRedirect('google')"
    >
      <i class="social-icon social-google"></i>
      <p class="social-text">GOOGLE</p>
    </b-button>

    <b-button
      variant="inverse"
      class="social-button-clinicPlus"
      @click="socialLoginRedirect('github')"
    >
      <i class="social-icon social-github"></i>
      <p class="social-text">GITHUB</p>
    </b-button>
  </div>
</template>

<script>
export default {
  name: "SocialLogin",
  data() {
    return {};
  },
  methods: {
    socialLoginRedirect(service) {
      window.location.href = `${this.$base_url}oauth/${service}`;
    },
  },
  created() {},
};
</script>

<style src="./SocialLogin.scss" lang="scss" scoped/>
