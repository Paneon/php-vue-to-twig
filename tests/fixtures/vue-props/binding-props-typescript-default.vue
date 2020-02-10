<template>
  <div>
    {{ test }}
  </div>
</template>

<script lang="ts">
    import { Component, Prop, Vue } from 'vue-property-decorator';

    @Component
    export default class PropsExample extends Vue {
        @Prop({ type: Boolean, required: true }) ignored: boolean;
        @Prop({ default: false }) propBoolean: string;
        @Prop({ default: '' }) propPresent!: string;
        @Prop({ default: 0, type: Number }) propWithTypeNumber!: number;
        @Prop({ type: String, default: 'foo' }) readonly propPresentWithTypeAndDefault!: string;
        @Prop({ default: 'I\'m a text' }) readonly propPresentWithApostrophe!: string;
        @Prop({ default: 'vuetotwig@example.org' }) readonly propPresentWithAt!: string;
    }
</script>
