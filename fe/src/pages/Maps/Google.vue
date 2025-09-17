<template>
  <div>
    <h1 class="mapTitle page-title">
      Google <span class="fw-semi-bold">Maps</span>
    </h1>
    <div class="mapContainer">
      <GmapMap
        :center="{lat: -33.80672617589333, lng: 18.48931609383872}"
        :zoom="12"
        style="width: 100%; height: inherit"
      >
      <GmapMarker
        :position="{lat: -33.80672617589333, lng: 18.48931609383872}"
      />
      </GmapMap>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GoogleMap',
};
</script>

<style src="./Google.scss" lang="scss" scoped />
