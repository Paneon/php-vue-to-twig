<template>
  <div>
    <div v-if="variable >= 1">
      <h1>Headline</h1>
    </div>
    <div v-else-if="variable < 100 && variable >= 1">
      <h1>Headline</h1>
    </div>
    <span v-if="showIcon">Oh no.</span>
  </div>
</template>

<script>
    export default {
        props: {
            show: {
                type: Boolean,
                default: false,
            },
        },
        computed: {
            showIcon() {
                return true;
            },
        },
    };
</script>
