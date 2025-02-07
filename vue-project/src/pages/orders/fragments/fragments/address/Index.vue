<template>
    <div class="grid grid-cols-2 gap-10">
        <AddressEditor
            title="Billing Info"
            :address="order.billing_address"
        />
        <AddressEditor
            title="Shipping Info"
            :address="order.shipping_address"
        />
    </div>
</template>

<script setup lang="ts">
    import { provide } from 'vue'
    import AddressEditor from './AddressEditor.vue'
    import { useAddress } from './useAddress'

    defineProps<{
        order: object
    }>()

    const _useAddress = useAddress()
    provide('useAddress', _useAddress)
</script>