<template>
  <div>
    <div :style="'fontSize: ' + size + 'px'"></div>
    <div v-text="'foo' + ' bar'"></div>
    <div v-text="text + ' foo'"></div>
    <div v-text="text + 1"></div>
    <div v-text="size + ' foo'"></div>
    <div v-text="size + 1"></div>
    <div v-text="1 + 1"></div>
  </div>
</template>

<script lang="ts">
  import { Component, Prop, Vue } from 'vue-property-decorator';

  @Component
  export default class PropsExample extends Vue {
    @Prop({ type: String, required: true }) text: string;
    @Prop({ type: Number, required: true }) size: number;
  }
</script>


