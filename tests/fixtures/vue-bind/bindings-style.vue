<template>
  <div>
    <span :style="{color: '#ff0000'}">
      test
    </span>
    <span :style="{width: w + 'px', height: h + 'px'}">
      test
    </span>
    <span :style="{'color: red': isSomething, 'color: blue': !isSomething}">
      test
    </span>
    <span :style="isSomething ? 'display: block;' : 'display: inline;'">
      test
    </span>
  </div>
</template>
