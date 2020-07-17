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
      </div>
  </div>
</template>
