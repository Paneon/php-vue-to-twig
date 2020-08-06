<template>
    <div>
        <textarea v-model="foo" />
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