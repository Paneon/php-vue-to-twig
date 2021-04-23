<template>
  <div>
    <slot>
      <ChildComponent/>
    </slot>
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
