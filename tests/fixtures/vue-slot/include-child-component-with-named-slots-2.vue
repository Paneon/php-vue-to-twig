<template>
  <div>
    <ChildComponent>
      <template v-slot:foo-bar>
        baz
      </template>
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
