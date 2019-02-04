<template>
  <div>
    <ChildComponent :product="testProduct" test-a="Hello" string="Test" />
  </div>
</template>

<script>
    export default {
        name: 'ComponentWithPropVue',
    };
</script>
