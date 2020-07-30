<template>
  <div class="block">
    <div :style="`fontSize: ${size}px`"></div>
    <div :class="`block block--${modifier}`" :style="`fill: ${color}; height: ${height}`">
      Hello World
    </div>
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
