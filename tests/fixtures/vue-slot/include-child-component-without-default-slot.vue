<template>
  <div>
    <ChildComponent />
  </div>
</template>
<script>
    export default {
        name: 'ComponentWithoutDefaultSlot',
        component: {
            ChildComponent,
        }
    };
</script>
