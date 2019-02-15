<template>
  <div>
    <div v-if="array.length" data-twig-if="array|length">
      <h1>Headline</h1>
    </div>
    <div v-else-if="array.length < 100" data-twig-if="array|length < 100">
      <h1>Headline</h1>
    </div>
  </div>
</template>
