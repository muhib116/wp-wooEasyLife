<template>
    <Create
        hideTitle
    >
        <template #btn>
            <Button.Primary
                @onClick="async (btn) => {
                    await handleCustomStatusUpdate(item, id, btn)
                    $emit('onUpdate')
                }"
                class="ml-auto"
            >
                Update Status
            </Button.Primary>
        </template>
    </Create>
</template>

<script setup lang="ts">
    import Create from './Create.vue'
    import { inject, onMounted } from 'vue'
    import { Button } from '@components'

    const props = defineProps<{
        item: {
            label: string
            color: string
            description: string
        },
        id: string
    }>()

    const {
        form,
        handleCustomStatusUpdate
    } = inject('useCustomStatus')

    onMounted(() => {
        form.value = props.item
    })
</script>