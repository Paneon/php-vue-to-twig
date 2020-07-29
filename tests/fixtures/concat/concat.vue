<template>
  <div>
    <div :style="'fontSize: ' + size + 'px'"></div>
    <div v-text="'foo' + ' bar'"></div>
    <div v-text="text + ' foo'"></div>
    <div v-text="text + 1"></div>
    <div v-text="size + ' foo'"></div>
    <div v-text="size + 1"></div>
    <div v-text="1 + 1"></div>
    <div v-text="'foo ' + size + size + ' bar'"></div>
    <div v-text="'foo ' + ( size + size ) + ' bar'"></div>

  </div>
</template>

<script>
export default {
  props: {
    text: {
      type: string,
      required: true,
    },
    size: {
      type: number,
      required: true,
    },
  },
};
</script>
