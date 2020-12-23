<template>
  <div>
    <ChildComponent :class="['foo', {'bar': true}, {'baz': false}]" />
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
