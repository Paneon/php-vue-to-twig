<template>
  <div class="foo"></div>
</template>

<style scoped>
  .foo {
      transition: all 400ms ease;
      overflow: hidden;
      will-change: height, margin, padding, opacity;
  }
</style>

<script>
    export default {
    }
</script>
