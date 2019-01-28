<template>
  <div>
    <ChildComponent v-for="a in listOfProducts" string="string" :product="productA" />
  </div>
</template>
<script>
    export default {
        name: 'component-with-for.vue'
    };
</script>
