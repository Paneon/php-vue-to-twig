<template>
    <div>
        <input type="radio" v-model="foo" value="1">
        <input type="radio" v-model="foo" value="2">
        <input type="radio" v-model="foo" value="3">
        <input type="radio" v-model="foo" value="4">
        <input type="radio" v-model="foo" value="5">
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