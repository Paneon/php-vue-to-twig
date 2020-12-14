<template>
  <div>
    <div :class="['a', { 'b': foo || bar } ]">
      Foo
    </div>
  </div>
</template>

<script>
export default {
  props: {
    foo: {
      type: Boolean,
      default: false,
    },
    bar: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
