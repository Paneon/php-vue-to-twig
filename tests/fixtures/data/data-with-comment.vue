<template>
    <div>
        Hello World!
    </div>
</template>

<script>
    export default {
        name: 'Foo',
        props: {
            bar: {
                type: String,
                default: 'baz',
            },
        },
        data() {
            return {
              /**
               * Foo
               */
                myObject: {
                    a: 'x',
                    b: 'y',
                    c: 'z',
                },
            };
        },
    };
</script>