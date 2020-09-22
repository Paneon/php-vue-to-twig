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
                myNumber: 1,
                myString: 'foo',
                myArray: [
                    'a',
                    'b',
                    'c',
                ],
                myObject: {
                    a: 'x',
                    b: 'y',
                    c: 'z',
                },
                calculatedNumber: this.myString.length + 1,
            };
        },
    };
</script>