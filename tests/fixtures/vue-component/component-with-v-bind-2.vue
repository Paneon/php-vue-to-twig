<template>
  <div>
    <ChildComponent v-bind="$props" bar="baz" />
  </div>
</template>

<script>
    export default {
        name: 'ComponentWithVBind',
    };
</script>
