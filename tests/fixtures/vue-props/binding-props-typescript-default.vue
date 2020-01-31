<template>
  <div>
    {{ test }}
  </div>
</template>

<script lang="ts">
    import { Component, Prop, Vue } from 'vue-property-decorator';

    @Component
    export default class FromPrice extends Vue {
        @Prop({ type: Boolean, required: true }) ignored: boolean;
        @Prop({ default: false }) propBoolean: string;
        @Prop({ default: '' }) propPresent!: string;
        @Prop({ default: 0, type: Number }) propWithTypeNumber!: number;
        @Prop({ type: String, default: 'foo' }) readonly propPresentWithTypeAndDefault!: string;
    }
</script>
