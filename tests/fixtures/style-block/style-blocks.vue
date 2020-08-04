<template>
<div>
  <div class="foo bar baz">
    42
  </div>
</div>
</template>

<style>
  .foo {
    color: red;
  }
</style>

<style scoped>
  .bar {
    color: blue;
  }
</style>

<style lang="scss">
  $green: #00FF00;
  .baz {
    color: $green;
  }
</style>

<script>
    export default {
    }
</script>
