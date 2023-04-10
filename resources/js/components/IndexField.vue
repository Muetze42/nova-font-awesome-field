<template>
    <span class="font-awesome-field font-awesome-field-index" v-html="fieldValue"/>
</template>

<script>
export default {
    props: ['resourceName', 'field'],

    computed: {
        fieldValue() {
            return this.field.currentSVG || ''
        },
    }
}
</script>
