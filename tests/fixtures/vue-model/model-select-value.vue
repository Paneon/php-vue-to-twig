<template>
    <div>
        <select v-model="foo">
            <option value="1">foo</option>
            <option value="2">bar</option>
            <option value="3">baz</option>
        </select>
    </div>
</template>

<script>
    export default {
        props: {
            foo: {
                type: String,
                default: '2',
            },
        },
    };
</script>