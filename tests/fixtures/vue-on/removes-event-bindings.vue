<template>
  <div>
    <div v-on:click="someFunction">
      <h1>Headline</h1>
    </div>
    <div v-on:blur="false">
      <h2
        @click="something"
        class="some-thing">Headline 2</h2>
    </div>
    <div>
      <h3>{{ var }}</h3>
    </div>
  </div>
</template>