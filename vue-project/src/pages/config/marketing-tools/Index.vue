<template>
  <Card.Native class="px-0 md:px-6 shadow-none md:shadow-md">
    <div class="p-4 bg-white rounded-xl shadow mb-6 max-w-lg">
      <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" fill="currentColor" />
        </svg>
        Facebook Pixel Integration
      </h2>
      <form @submit.prevent="onSave">
        <div class="mb-3">
          <label class="block mb-1 font-medium">Pixel ID</label>
          <input
            v-model="settings.pixel_id"
            class="w-full p-2 border rounded"
            placeholder="e.g., 1234567890"
            type="text"
            autocomplete="off"
          />
        </div>
        <div class="mb-3">
          <label class="block mb-1 font-medium">CAPI Access Token</label>
          <input
            v-model="settings.capi_token"
            class="w-full p-2 border rounded"
            placeholder="Long token from Events Manager"
            type="text"
            autocomplete="off"
          />
        </div>
        <div class="mb-3">
          <label class="inline-flex items-center">
            <input
              type="checkbox"
              v-model="settings.server_side"
              class="mr-2"
            />
            Enable Server-Side (CAPI) Tracking
          </label>
        </div>
        <button
          class="bg-blue-600 text-white px-4 py-2 rounded"
          :disabled="saving"
          type="submit"
        >
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
        <div v-if="message" class="mt-2 text-green-600">{{ message }}</div>
      </form>
    </div>
  </Card.Native>
</template>

<script setup lang="ts">
import { Card } from '@/components'
import { ref, onMounted } from 'vue'
import { fetchPixelSettings, savePixelSettings } from '@/api'

interface PixelSettings {
  pixel_id: string
  capi_token: string
  server_side: boolean
}

const settings = ref<PixelSettings>({
  pixel_id: '',
  capi_token: '',
  server_side: false
})

const saving = ref(false)
const message = ref('')

const loadSettings = async () => {
  try {
    const data = await fetchPixelSettings()
    Object.assign(settings.value, data)
  } catch (e) {
    message.value = 'Failed to load settings.'
  }
}

const onSave = async () => {
  saving.value = true
  message.value = ''
  try {
    await savePixelSettings(settings.value)
    message.value = 'Settings saved!'
  } catch (e) {
    message.value = 'Failed to save.'
  } finally {
    saving.value = false
  }
}

onMounted(loadSettings)
</script>