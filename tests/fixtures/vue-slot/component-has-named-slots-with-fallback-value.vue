<template>
  <div class="container">
    <header>
      <slot name="header">header</slot>
    </header>
    <main>
      <slot>content</slot>
    </main>
    <footer>
      <slot name="footer">footer</slot>
    </footer>
  </div>
</template>
<script>
    export default {
        name: 'BaseLayout',
    };
</script>
