<template>
  <div class="block">
    <div :style="`width: ${size}px; height: ${size}px;`">
      Foo
    </div>
    <div :style="foo ? foo : `width: ${size}px; height: ${size}px;`">
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
