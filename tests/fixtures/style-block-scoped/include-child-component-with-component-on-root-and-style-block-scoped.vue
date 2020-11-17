<template>
  <ChildComponent class="foo" style="color: black" />
</template>

<script>
    export default {
        name: 'ComponentWithComponentOnRoot',
        component: {
          ChildComponent,
        }
    };
</script>

<style scoped>
  .foo {
    color: purple;
  }
</style>
