<template>
  <a v-if="true" href="#">
    <slot>{{ text }}</slot>
  </a>
  <button v-else>
    <slot>{{ text }}</slot>
  </button>
</template>
<script>
    export default {
        name: 'ComponentWithTowDefaultSlots',
    };
</script>
