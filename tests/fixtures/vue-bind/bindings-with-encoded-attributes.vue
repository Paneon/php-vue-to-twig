<template>
  <div>
    <a :href="isBool ? varA.url : varB.url">
      <img :src="isBool ? varA.src : varB.src" :alt="isBool ? varA.alt : varB.alt">
    </a>
    <form :action="isBool ? varA.url : varB.url" method="get">
      <label>
        <input type="text" :name="isBool ? varA.name : varB.name">
      </label>
      <input type="submit">
    </form>
    <a :name="isBool ? varA.anchor : varB.anchor"/>
  </div>
</template>
