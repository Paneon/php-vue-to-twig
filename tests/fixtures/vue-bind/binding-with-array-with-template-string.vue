<template>
  <div>
    <div :class="['a', `b ${isDisabled ? 'b--disabled' : ''}`]">
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
