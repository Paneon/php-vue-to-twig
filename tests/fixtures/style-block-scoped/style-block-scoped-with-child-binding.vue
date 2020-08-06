<template>
  <div class="root">
    <div class="foo">
      <ChildComponent />
    </div>
  </div>
</template>

<script>
  export default {
    name: 'cstyle-block-scoped-with-child-binding.vue',
    component: {
      ChildComponent,
    }
  };
</script>

<style scoped>
  .foo {
    color: red;
  }
</style>
