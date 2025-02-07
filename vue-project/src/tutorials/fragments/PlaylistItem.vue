<template>
  <div
    class="cursor-pointer rounded transition duration-300 grid grid-cols-[65px_1fr] items-center gap-2"
    :class="{
      'text-blue-500 font-medium': index === currentIndex,
      'hover:text-blue-500': index !== currentIndex,
    }"
  >
    <img
      :src="`https://img.youtube.com/vi/${extractVideoId(
        item.path
      )}/default.jpg`"
      alt="Thumbnail"
      class="aspect-video rounded"
      :class="{
        'outline-2 outline-blue-500': index === currentIndex
      }"
    />
    <h3 
        class="flex-1 text-sm line-clamp-2 leading-[20px] relative"
        :title="item.title"
    >
        <Loader
            :active="isLoading"
            class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
            size="30"
            weight="regular"
        />
      {{ item.title }}
    </h3>
  </div>
</template>

<script setup lang="ts">
import { inject, onMounted, ref } from 'vue'
import { Loader } from '@components'

const props = defineProps<{
    index: string | number
  item: {
    path: string;
    title: string;
  };
}>();

const isLoading = ref(false)
const { extractVideoId, currentIndex, activeTutorialList, fetchVideoTitle } = inject("useTutorials")

onMounted(async () => {
    try {
        isLoading.value = true
        await fetchVideoTitle(props.item)
    } finally {
        isLoading.value = false
    }
})
</script>
