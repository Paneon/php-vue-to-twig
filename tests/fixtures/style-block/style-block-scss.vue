<template>
<div>
  <div class="foo">
    foo
  </div>
</div>
</template>

<style lang="scss">
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
