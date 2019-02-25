<template>
  <div class="block">
    <div :class="`dynamic dynamic--${modifier}`" class="static1 static2">
      Hello World
    </div>
    <div :class="{ 'my-class': true }" class="static1 static2">
      With Object Binding
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
