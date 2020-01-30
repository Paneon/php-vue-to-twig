<template>
    <div>
        <span v-html="rawHtml" />
    </div>
</template>

<twig>
  {% set rawHtml = '<strong>text</strong>' %}
</twig>

<script>
    export default {
        computed: {
            rawHtml(){
                return '<strong>text</strong>';
            },
        }
    }
</script>
