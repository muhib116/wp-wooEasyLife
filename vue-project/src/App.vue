<template>
  <ServiceProvider>
    <RouterView />
  </ServiceProvider>
</template>

<script setup lang="ts">
  import { onMounted } from 'vue'
  import { RouterView } from 'vue-router'
  import ServiceProvider from '@/service/ServiceProvider.vue'

  onMounted(() => {
    // wp admin left side off
    document.body.classList.add('folded')
  })
</script>