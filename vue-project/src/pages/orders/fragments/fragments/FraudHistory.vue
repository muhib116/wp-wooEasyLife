<template>
<div>
    <div class="border-b pb-6 mb-0">
        <h3 class="font-bold">
            Name: 
            {{ order.customer_name }}
        </h3>
        <h3>{{ getContactInfo }}</h3>
    </div>

    <FraudData
        :hideShadow="false"
        :data="{report: order.customer_report}"
    />
</div>
</template>

<script setup lang="ts">
    import { computed } from 'vue'
    import FraudData from '@/pages/fraudChecker/FraudData.vue'

    const props = defineProps<{
        order: object
    }>()

    const getContactInfo = computed(() => {
        if(!props.order?.billing_address) return ''

        const { phone, email } = props.order.billing_address
        let data = phone ? `Phone: ${phone}` : ''
            data += email ? ` | Email: ${email}` : ''
        return data
    })
</script>