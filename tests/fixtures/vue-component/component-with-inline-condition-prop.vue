<template>
  <div>
    <ChildComponent :product="testProduct"
                    :show-something="index > 5 && a === true"
                    test-a="Hello"
                    string="Test" />
  </div>
</template>

<script>
    export default {
        name: 'ComponentWithPropVue',
    };
</script>
