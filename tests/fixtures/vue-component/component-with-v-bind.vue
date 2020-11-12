<template>
  <div>
    <ChildComponent v-bind="foo" bar="baz" />
  </div>
</template>

<script>
    export default {
        name: 'ComponentWithVBind',
    };
</script>
