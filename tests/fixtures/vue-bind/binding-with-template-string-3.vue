<template>
  <div class="block">
    <div :style="`width: ${100 / items.length}%;`">
      Hello World
    </div>
  </div>
</template>

<script>
  export default {
      props: {
        items: {
              type: Array,
              required: true,
          },
      },
  };
</script>
