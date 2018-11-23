<template>
  <div>
    <div v-if="someCondition">
      <h1>Headline</h1>
    </div>
    <div v-else-if="someOtherCondition">
      <h2>Headline 2</h2>
    </div>
    <div v-else>
      <h3>Headline 3</h3>
    </div>
  </div>
</template>