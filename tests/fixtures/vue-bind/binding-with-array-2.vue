<template>
  <div>
    <div :class="['a', { 'a--disabled': isDisabled } ]">
      Foo
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isDisabled: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
