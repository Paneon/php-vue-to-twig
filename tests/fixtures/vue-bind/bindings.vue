<template>
  <div>
    <div>
      <input type="radio" :checked="true">
      <img v-bind:src="imageSrc">
      <img :src="imageSrc">
      <div :class="['a', 'b', 'c']"></div>
      <div title="Title" :class="{ 'block': isSomething, 'block2': !isSomething}" class="category-filter-list categories"></div>
      <div :class="getClasses(!hasSomething)" ></div>
      <div :style="'display: none !important'">Hidden</div>
      <div style="color: red" :style="'display: none !important'">Hidden</div>
      <div style="border: 1px solid #000000" :style="{ 'color: red': isSomething, 'color: blue': !isSomething}">Hidden</div>
      </div>
  </div>
</template>
