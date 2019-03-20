<template>
<div>
  <div>
    <p v-if="title === 'Something'">Default</p>
    <p>{{ title }}</p>
    <p v-if="req">{{ value }}</p>
  </div>
</div>
</template>

<twig>
  {% set optionallyIgnore = false %}
</twig>

<script>
    export default {
        props: {
            title: {
                type: String,
                default: 'Default'
            },
            value: {
                type: Number,
                default: 80
            },
            req: {
                type: Boolean,
                required: true,
            },
            optionallyIgnore: {
                type: String,
            }
        },
    }
</script>
