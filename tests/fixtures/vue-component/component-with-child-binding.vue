<template>
  <div>
    <ChildComponent :style="`fill:${color};`" :key=testkey test="test" />
    <ChildComponent style="color: red" :style="`fill:${color};`" />
    <ChildComponent class="foo" :class="`bar`" />
    <ChildComponent class="foo" :class="bar" />
    <ChildComponent class="product-column__tile" :class="{'product-column__tile--large': isSingleProductTile }" />
    <ChildComponent :aria-labelledby="`foo-${_uid}`" />
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
