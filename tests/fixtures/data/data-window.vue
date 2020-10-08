<template>
  <div>
    Hello World!
  </div>
</template>

<script>
export default {
  name: 'Foo',
  data() {
    return {
      foo: window.location.href.indexOf('foo') != -1 ? 'www.foo.baz' : 'www.bar.baz'
    };
  },
};
</script>