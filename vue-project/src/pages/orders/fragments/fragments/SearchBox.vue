<template>
    <div class="print:hidden flex gap-1">
        <Input.Native
            placeholder="Search customer"
            class="text-base border px-2 py-1 rounded-sm"
            v-model="orderFilter.search"
            @keyup.enter="handleEnterPress"
        />
        <Button.Primary
            ref="submitBtn"
            class="!py-1 px-2"
            @onClick="debouncedGetOrders"
        >
            <Icon
                name="PhMagnifyingGlass"
                weight="bold"
                size="20"
            />
        </Button.Primary>
        <slot name="afterButton"></slot>
    </div>
</template>

<script setup lang="ts">
import { Button, Icon, Input } from '@components'
import { inject, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

// Setup refs
const submitBtn = ref<InstanceType<typeof Button.Primary> | null>(null)
const route = useRoute()
// Inject dependencies
const injected = inject('useOrders') as {
    debouncedGetOrders: () => void,
    orderFilter: { search: string }
}
const { debouncedGetOrders, orderFilter } = injected

const handleEnterPress = () => {
    if (!submitBtn.value) return

    // Try to trigger button click via the exposed method or native element
    submitBtn.value.$el?.click?.()
}

onMounted(() => {
    if(route.query?.phone) {
        orderFilter.value.search = route.query?.phone
        submitBtn.value.$el?.click?.()
    }
})
</script>