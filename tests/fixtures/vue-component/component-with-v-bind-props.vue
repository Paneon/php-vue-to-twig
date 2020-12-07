<template>
  <div>
    <ChildComponent v-bind="$props" />
  </div>
</template>

<script lang="ts">
    import { Component, Prop, Vue } from 'vue-property-decorator';

    @Component
    export default class ComponentWithVBind extends Vue {
      @Prop({ type: String, default: 'foo' }) foo: string;
      @Prop({ type: Number, default: 42 }) bar: number;
    }
</script>
