<template>
  <div>
    <div>
      <input type="radio" :checked="true">
      <img :src="imageSrc">
      <div :class="['a', 'b', 'c']"></div>
      <div :class="{ 'block': isSomething, 'block2': isSomething}" class="category-filter-list categories"></div>
      </div>
  </div>
</template>
