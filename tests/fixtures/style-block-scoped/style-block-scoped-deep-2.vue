<template>
<div>
  <ul class="foo">
    <li>foo</li>
    <li>bar</li>
    <li>baz</li>
  </ul>
</div>
</template>

<style scoped>
  .foo /deep/ li:nth-child(2) {
   color: purple;
  }
</style>

<script>
    export default {
    }
</script>
