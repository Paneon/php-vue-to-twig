<template>
<div>
  <div class="foo">
    foo
  </div>
</div>
</template>

<style lang="scss">
@import "./file";

.foo {
  color: red;
  &__bar {
    color: blue;
  }
  &__baz {
    color: $color;
  }
}
</style>

<script>
    export default {
    }
</script>
