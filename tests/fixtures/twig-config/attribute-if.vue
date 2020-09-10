<template>
    <div>
        <div :foo="count > 5">
            Foo
        </div>
        <div :bar="count > 5">
            Bar
        </div>
    </div>
</template>

<twig-config>
if-attributes = foo
</twig-config>

<script>
    export default {
        props: {
            count: {
                type: Number,
                default: 0,
            },
        },
    };
</script>