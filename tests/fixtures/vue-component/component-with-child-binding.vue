<template>
  <div>
    <ChildComponent :style="`fill:${color};`" :key=testkey test="test" />
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
