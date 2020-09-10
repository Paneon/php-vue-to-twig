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
    attributes-with-if = foo,bar
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