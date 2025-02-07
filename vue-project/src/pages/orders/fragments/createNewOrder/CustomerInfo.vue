<template>
    <Select.Primary
        label="Order status"
        defaultOption="Select Status"
        :options="wooCommerceStatuses"
        itemKey="slug"
        v-model="form.order_status"
    />
    <div class="grid grid-cols-2 gap-4">
        <Input.Primary
            label="Customer Name *"
            placeholder="Write customer name."
            v-model="form.first_name"
        />
        <Input.Primary
            label="Customer Phone Number *"
            placeholder="Write valid phone number."
            v-model="form.phone"
        />
    </div>

    <Textarea.Native
        label="Customer Address *"
        placeholder="Write customer address."
        v-model="form.address_1"
    />

    <div class="flex gap-4">
        <Select.Primary
            label="Order Source *"
            :options="orderSource"
            v-model="selectedSource"
            wrapperClass="flex-1"
            @change="() => {
                if(selectedSource == 'other') {
                    form.created_via = ''
                }else {
                    form.created_via = selectedSource
                }
            }"
        />
        <Input.Primary
            v-if="selectedSource == 'other'"
            label="Order source name"
            placeholder="Write order source name."
            wrapperClass="flex-1"
            v-model="form.created_via"
        />
    </div>
</template>

<script setup lang="ts">
    import { Input, Select, Textarea } from '@components'
    import { ref, inject } from 'vue'

    const selectedSource = ref(null)
    const orderSource = [
        {
            id: 'whats-app',
            title: 'Whats app'
        },
        {
            id: 'messenger',
            title: 'Messenger'
        },
        {
            id: 'instagram',
            title: 'Instagram'
        },
        {
            id: 'phone-call',
            title: 'Phone call'
        },
        {
            id: 'other',
            title: 'Other'
        },
    ]

    const {
        form,
    } = inject('useCustomOrder')

    const {
        wooCommerceStatuses,
    } = inject('useOrders')
</script>