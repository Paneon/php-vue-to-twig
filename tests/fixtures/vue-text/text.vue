<template>
    <div>
        <span v-text="text" />
    </div>
</template>

<twig>
  {% set text = 'follow the white rabbit' %}
</twig>

<script>
    export default {
        computed: {
            text(){
                return 'follow the white rabbit';
            },
        }
    }
</script>
