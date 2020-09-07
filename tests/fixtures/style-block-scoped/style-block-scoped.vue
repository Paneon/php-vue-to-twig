<template>
  <div class="root">
    <div class="foo">
      foo
    </div>
    <div class="bar">
      bar
    </div>
    <div class="baz">
      baz
    </div>
  </div>
</template>

<style scoped>
  .foo {
    color: red;
  }
  .bar,
  .baz {
    color: blue;
  }
  .foo:after {
    display: none;
  }
  .root .bar {
    text-decoration: underline;
  }
</style>

<script>
    export default {
    }
</script>
