<template>
    <div>
        <select v-model="foo" multiple>
            <option value="1">foo</option>
            <option value="2">bar</option>
            <option value="3">baz</option>
        </select>
    </div>
</template>

<script lang="ts">
    import { Component, Vue, Prop } from 'vue-property-decorator';
    @Component
    export default class ButtonCollectionContainer extends Vue {
        @Prop({ type: Array, default: ['2', '3'] }) readonly foo!: string[];
    }
</script>