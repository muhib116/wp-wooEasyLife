<template>
    <div class="w-[500px]">
        <Create
            hideTitle
        >
            <template #btn>
                <Button.Primary
                    @onClick="async (btn) => {
                        await handleUpdateSMS(btn, form)
                        $emit('onUpdate')
                    }"
                    class="ml-auto"
                >
                    Update Message
                </Button.Primary>
            </template>
        </Create>
    </div>
</template>

<script setup lang="ts">
    import { inject, onMounted } from 'vue'
    import Create from './Create.vue'
    import { Button } from '@components'

    const props = defineProps<{
        item: object
    }>()


    const {
        form,
        handleUpdateSMS
    } = inject('useSmsConfig')


    onMounted(() => {
        form.value = {...props.item}
    })
</script>