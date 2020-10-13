<template>
    <div>
        Hello World!
    </div>
</template>

<script lang="ts">
    import { Vue, Component, Prop } from 'vue-property-decorator';

    @Component()
    export default class Foo extends Vue {

        @Prop({ type: String, default: 'baz' }) readonly bar!: string;

        private myNumber = 1;

        private myString = 'foo';

        private myArray = ['a', 'b', 'c'];

        private myObject =  { a: 'x', b: 'y', c: 'z' };
    };
</script>