<template>
  <div class="block">
    <div :style="`width: ${size}; height: ${size}`">
      Foo
    </div>
    <div :style="foo ? foo : `width: ${size}; height: ${size}`">
      Bar
    </div>
  </div>
</template>

<script>
  export default {
      props: {
        foo: {
          type: String,
          default: '',
        },
        size: {
              type: Number,
              required: true,
          },
      },
  };
</script>
