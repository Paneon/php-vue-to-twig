<template>
  <div>
    <ChildComponent v-bind="$props" bar="baz" />
  </div>
</template>

<script lang="ts">
    import { Component, Prop, Vue } from 'vue-property-decorator';

    @Component
    export default class ComponentWithVBind extends Vue {
      @Prop({ type: String, default: 'foo' }) a: string;
      @Prop({ type: Number, default: 42 }) b: number;
    }
</script>
