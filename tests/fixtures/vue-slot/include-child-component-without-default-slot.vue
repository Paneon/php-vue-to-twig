<template>
  <div>
    <ChildComponent />
  </div>
</template>
<script>
    export default {
        name: 'ComponentWithSlot',
        component: {
            ChildComponent,
        }
    };
</script>
