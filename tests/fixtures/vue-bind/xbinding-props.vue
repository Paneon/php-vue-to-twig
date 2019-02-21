<template>
<div>
  <div>
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
                default: 'Default title'
            },
            req: {
                type: Boolean,
                required: true,
            },
        },
    }
</script>
