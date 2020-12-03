<template>
<div>
  <div class="foo__bar">
    baz
  </div>
</div>
</template>

<style lang="scss" scoped>
  @import "imports/import";
  @import "imports/subfolder/import2";
  .foo {
    color: $blue;
    &__bar {
      color: $red;
    }
  }
</style>

<script>
    export default {
    }
</script>
