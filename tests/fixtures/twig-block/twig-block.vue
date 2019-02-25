<template>
<div>
  <div>
    <p v-if="myCustomComputedProperty">Show something</p>
  </div>
</div>
</template>

<twig>
  {% set myCustomComputedProperty = false %}
</twig>

<script>
    export default {
        computed: {
            myCustomComputedProperty(){
                return Math.random() > 1;
            },
        }
    }
</script>
