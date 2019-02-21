<template>
  <div class="block">
    <div :class="`dynamic dynamic--${modifier}`" class="static1 static2">
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
