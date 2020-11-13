<template>
<div>
  <div class="foo">
    foo
  </div>
</div>
</template>

<style scoped>
  .foo {
    color: red;
  }
  .foo /deep/ .bar {
   color: blue;
  }
</style>

<script>
    export default {
    }
</script>
