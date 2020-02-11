<template>
    <div>
        <span v-html="rawHtml" />
    </div>
</template>

<twig>
  {% set rawHtml = '&lt;strong&gt;text&lt;/strong&gt;' %}
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
