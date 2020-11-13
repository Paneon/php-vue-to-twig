<template>
  <ChildComponent>
    <slot>{{ text }}</slot>
  </ChildComponent>
</template>
<script>
  export default {
    name: 'ComponentWithSlot',
    component: {
      ChildComponent,
    },
    props: {
      text: {
        type: String,
        default: 'foo'
      },
    },
  };
</script>
