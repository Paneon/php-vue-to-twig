<template>
  <div>
    <div v-if="someVar">Text</div>
    <div v-if="!someVar && mobile !== true">Mobile</div>
  </div>
</template>