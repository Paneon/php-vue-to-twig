<template>
  <div>
    <ChildComponent class="foo" style="color: black" />
  </div>
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
