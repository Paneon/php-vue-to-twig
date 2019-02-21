<template>
<div>
  <div>
    <p v-if="title === 'Something'">Default</p>
    <p>{{ title }}</p>
    <p v-if="req">Boolean</p>
  </div>
</div>
</template>

<script>
    export default {
        props: {
            title: {
                type: String,
                default: 'Default'
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
