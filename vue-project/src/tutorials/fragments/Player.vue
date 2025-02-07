<template>
    <iframe
        v-if="activeTutorialList.length"
        :src="`https://www.youtube.com/embed/${currentVideoId}?autoplay=1&controls=1`"
        frameborder="0"
        allowfullscreen
        allow="autoplay; encrypted-media"
        class="w-full max-w-4xl aspect-video rounded-lg shadow-lg bg-black"
    ></iframe>
    <p v-else class="text-lg bg-black text-gray-400 aspect-video grid place-content-center">
        No videos available
    </p>
</template>

<script setup lang="ts">
    import { inject } from 'vue'

    const {
        currentVideoId,
        activeTutorialList
    } = inject('useTutorials')
</script>