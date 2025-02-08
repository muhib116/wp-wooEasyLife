<template>
    <div>
        <h3 
            class="md:text-xl leading-[20px] md:leading-[26px] font-semibold text-gray-900"
            :class="titleClass"
        >
            {{ title }}
        </h3>
        <div
            v-if="subtitle" 
            class="hidden md:block text-lg text-gray-500"
            :class="subtitleClass"
            v-html="subtitle"
        ></div>
    </div>
</template>

<script setup lang="ts">
    defineProps<{
        title: string,
        subtitle?: string,
        titleClass?: string,
        subtitleClass?: string,
    }>()
</script>