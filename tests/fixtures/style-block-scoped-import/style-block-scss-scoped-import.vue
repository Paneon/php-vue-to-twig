<template>
<div>
  <div class="foo__bar">
    baz
  </div>
</div>
</template>

<style lang="scss" scoped>
  @import "import";
  .foo {
    color: blue;
    &__bar {
      color: $red;
    }
  }
</style>

<script>
    export default {
    }
</script>
