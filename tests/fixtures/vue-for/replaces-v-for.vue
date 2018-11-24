<template>
  <div>
    <div v-for="item in items">
      {{ item.text }}
    </div>
    <div v-for="(item, key) in items">A</div>
    <div v-for="(val, key) in object">B</div>
    <div v-for="(val, key, index) in object">C</div>
    <div>
      <div v-for="item in items" :key="item.id">
        {{ item.text }}
      </div>
    </div>
  </div>
</template>