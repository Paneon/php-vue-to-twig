<template>
  <div>
    <div v-for="item in 5">
      {{ item.text }}
    </div>
    <span v-for="n in 10">{{ n }}</span>
  </div>
</template>
