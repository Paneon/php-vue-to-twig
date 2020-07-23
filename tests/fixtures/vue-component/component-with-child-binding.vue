<template>
  <div>
    <ChildComponent :style="`fill:${color};`" :key=testkey test="test" />
    <ChildComponent class="foo" :class="`bar`" />
    <ChildComponent class="foo" :class="bar" />
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
