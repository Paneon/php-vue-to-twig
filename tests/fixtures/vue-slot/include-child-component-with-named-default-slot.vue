<template>
  <div>
    <ChildComponent>
      <template v-slot:header>
        <h1>Here might be a page title</h1>
      </template>

      <template v-slot:default>
        <p>A paragraph for the main content.</p>
        <p>And another one.</p>
      </template>

      <template v-slot:footer>
        <p>Here's some contact info</p>
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
