<template>
  <div>
    <ChildComponent :style="`fill:${color};`" :key=testkey test="test" />
    <ChildComponent class="foo" :class="`bar`" />
    <ChildComponent class="foo" :class="bar" />
    <ChildComponent class="product-column__tile" :class="{'product-column__tile--large': isSingleProductTile }" />
  </div>
</template>
<script>
    export default {
        name: 'component-with-child-binding.vue',
        component: {
            ChildComponent,
        }
    };
</script>
