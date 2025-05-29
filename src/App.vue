<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <NewRequisitionForm @close="handleClose" />
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import NewRequisitionForm from './components/NewRequisitionForm.vue'

export default defineComponent({
  name: 'App',
  components: {
    NewRequisitionForm
  },
  setup() {
    const handleClose = () => {
      console.log('Close clicked')
    }

    return {
      handleClose
    }
  }
})
</script>