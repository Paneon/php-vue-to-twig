<template>
  <div class="block">
    <div :class="`block block--${modifier}`" :style="`fill: ${color}`">
      Hello World
    </div>
    <div :class="`${isTrue ? 'a' : 'b'}`"></div>
    <div :style="`${isTrue ? 'display: block' : 'display: none !important'}`"></div>
  </div>
</template>

<script>
  export default {
      props: {
          modifier: {
              type: String,
              required: true,
          },
      },
  };
</script>
