<template>
  <div
    v-if="activeTutorialList.length>1"
    class="mt-4"
  >
    <h2 class="font-medium mb-4">Playlist ({{activeTutorialList.length}})</h2>

    <div class="grid grid-cols-2 gap-3 items-start h-[128px] overflow-auto">
      <PlaylistItem
        v-for="(video, index) in activeTutorialList"
        :key="index"
        @click="currentIndex = index"
        :index="index"
        :item="video"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import PlaylistItem from "./PlaylistItem.vue";
import { inject } from "vue";

const { currentIndex, activeTutorialList } = inject("useTutorials")
</script>
