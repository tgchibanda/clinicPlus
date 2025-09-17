<template>
  <router-view />
</template>

<script>
export default {
  name: "App",
  mounted() {
    const currentPath = this.$router.history.current.path;
    if (window.localStorage.getItem("authenticated") === "false" && currentPath === "/app") {
      this.$router.push("/login");
    }

    if (currentPath === "/" || currentPath === "/app") {
      this.$router.push("/login");
    }
  },
};
</script>

<style src="./styles/theme.scss" lang="scss" />
