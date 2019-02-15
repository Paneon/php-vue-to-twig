<template>
  <div>
    <ChildComponent>
      <h4>My default slot title</h4>
      <p>some text</p>
    </ChildComponent>
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
