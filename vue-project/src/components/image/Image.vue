<template>
    <img
        :src="src || altSrc"
        v-bind="$attrs"
        loading="lazy"
        :onerror="e => (e.target.src = altSrc)"
    />
</template>

<script setup>
    defineOptions({
        name: 'Image',
    })

    const props = defineProps({
        src: {
            type: String,
            required: true,
        },
        altSrc: {
            type: String,
            required: false,
            default: () => '/images/placeholder.jpeg',
        },
    })
</script>
