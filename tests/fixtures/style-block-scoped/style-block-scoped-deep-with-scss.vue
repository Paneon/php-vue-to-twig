<template>
<div>
  <div class="foo">
    foo
  </div>
</div>
</template>

<style lang="scss" scoped>
  .foo {
    color: red;
    & /deep/ .bar {
      color: blue;
    }
  }
</style>

<script>
    export default {
    }
</script>
