<template>
<div>
  {{ test }}
</div>
</template>

<script lang="ts">
  import { Vue, Component, Prop } from 'vue-property-decorator';

  @Component
  export default class FromPrice extends Vue {
    @Prop({ type: String, default: 'foo' }) readonly test!: string;
  }
</script>
