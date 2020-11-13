<template>
  <div class="bar">
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
export default class Progressbar extends Vue {
}
</script>

<style lang="scss" scoped>
  .grow-enter,
  .grow-leave-to {
    width: 0 !important;
  }
  .grow-enter-active,
  .grow-leave-active {
    transition: width 0.6s ease;
  }
</style>