<template>
    <Modal 
        v-model="toggleVideoPlayer"
        hideHeader
        cancelText="Close player"
        class="w-[600px]"
        @close="() => {
            resetTutorials()
            toggleVideoPlayer = false
        }"
    >
        <div class="grid">
            <Player />
            <Playlist />
        </div>
    </Modal>
</template>
  
  <script setup>
  import { provide } from 'vue'
  import Player from './fragments/Player.vue'
  import Playlist from './fragments/Playlist.vue'
  import { useTutorials } from './useTutorials'
  import { Modal } from '@components'
  
  const _useTutorials = useTutorials()
  const { toggleVideoPlayer, resetTutorials } = _useTutorials

  provide('useTutorials', _useTutorials)
</script>