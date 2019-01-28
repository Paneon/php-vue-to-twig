<template>
  <div>
    <ChildComponent v-if="a > 5" string="string" :product="productA"/>
  </div>
</template>
<script>
    export default {
        name: 'component-with-if.vue'
    };
</script>
