<template>
  <div>
    <ChildComponent/>
  </div>
</template>
<script>
    export default {
        name: 'component-with-child.vue',
        component: {
            ChildComponent,
        }
    };
</script>
