<template>
    <div class="grid gap-4 mt-4">
        <div class="grid grid-cols-2 gap-4">
            <Input.Primary
                label="First name"
                v-model="address.first_name"
            />
            <Input.Primary
                label="Last name"
                v-model="address.last_name"
            />
        </div>
        <div>
            <Input.Primary
                label="Company"
                v-model="address.company"
            />
        </div>
        <div>
            <Input.Primary
                label="Address line 1"
                v-model="address.address_1"
            />
        </div>
        <div>
            <Input.Primary
                label="Address line 2"
                v-model="address.address_2"
            />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <Input.Primary
                label="City"
                v-model="address.city"
            />
            <Input.Primary
                label="State"
                v-model="address.state"
            />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <Input.Primary
                label="Post code"
                v-model="address.postcode"
            />
            <Input.Primary
                label="Country"
                v-model="address.country"
            />
        </div>
        <div v-if="address.type=='billing'" class="grid grid-cols-2 gap-4">
            <Input.Primary
                label="Email"
                v-model="address.email"
            />
            <Input.Primary
                label="Phone"
                v-model="address.phone"
            />
        </div>
        <div v-if="address.hasOwnProperty('transaction_id')">
            <Input.Primary
                label="Transaction ID"
                v-model="address.transaction_id"
            />
        </div>
        <div v-if="address.hasOwnProperty('customer_note')">
            <Textarea.Native
                label="Transaction ID"
                v-model="address.customer_note"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Input, Textarea } from '@components'
    import { onMounted } from 'vue'
    import { getPaymentMethods } from '@/api'

    const props = defineProps<{
        address: object
    }>()

    onMounted(async () => {
        if(props.address.type == 'shipping') return
        const { data } = await getPaymentMethods()
    })
</script>