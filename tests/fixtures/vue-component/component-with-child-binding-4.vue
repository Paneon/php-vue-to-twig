<template>
  <div>
    <ChildComponent :class="['foo', {'bar': test}, {'baz': !test}, ]" />
  </div>
</template>
<script>
    export default {
        name: 'component-with-child-binding.vue',
        component: {
            ChildComponent,
        },
        props: {
            test: {
                type: Boolean,
                default: false
            },
        },
    };
</script>
