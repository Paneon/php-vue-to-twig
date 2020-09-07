<template>
<div>
  <div class="foo__bar">
    baz
  </div>
</div>
</template>

<style lang="scss" scoped>
  $green: #00FF00;
  .foo {
    color: red;
    &__bar {
      color: blue;
    }
    &__baz {
      color: $green;
    }
  }
</style>

<script>
    export default {
    }
</script>
