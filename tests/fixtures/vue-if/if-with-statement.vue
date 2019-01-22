<template>
  <div>
    <div v-if="variable >= 1">
      <h1>Headline</h1>
    </div>
    <div v-else-if="variable < 100 && variable >= 1">
      <h1>Headline</h1>
    </div>
  </div>
</template>
