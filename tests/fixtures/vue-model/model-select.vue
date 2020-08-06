<template>
    <div>
        <select v-model="foo">
            <option>foo</option>
            <option>bar</option>
            <option>baz</option>
        </select>
    </div>
</template>

<script>
    export default {
        props: {
            foo: {
                type: String,
                default: 'bar',
            },
        },
    };
</script>