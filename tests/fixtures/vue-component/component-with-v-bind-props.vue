<template>
  <div>
    <ChildComponent v-bind="$props" bar="baz" />
  </div>
</template>

<script lang="ts">
    import { Component, Prop, Vue } from 'vue-property-decorator';

    @Component
    export default class ComponentWithVBind extends Vue {
      @Prop({ type: String, required: true }) a: string;
      @Prop({ type: Number, required: true }) b: number;
    }
</script>
