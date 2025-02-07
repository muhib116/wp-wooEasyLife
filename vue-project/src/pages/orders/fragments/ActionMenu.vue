<template>
    <Card.Native
        class="basis-[250px] px-0 sticky top-12"
        v-if="shouldShow"
    >
        <Heading
            class="px-6 mb-6"
            title="Quick Actions"
        />
        <div class="[&>*]:border-t">
            <MenuButton
                v-if="configData?.fraud_customer_checker"
                title="Check Customer"
                subtitle="Check delivery success rate for selected items."
                iconName="PhUserList"
                :cb="handleFraudCheck"
            />
            <MenuButton
                v-if="configData?.courier_automation"
                title="Courier"
                subtitle="Book courier for selected items."
                iconName="PhTruck"
            />
            <MenuButton
                v-if="configData?.ip_block"
                title="Block Ip"
                subtitle="Book courier for selected items."
                iconName="PhCellTower"
                :cb="handleIPBlock"
            />
            <MenuButton
                v-if="configData?.phone_number_block"
                title="Block Phone"
                subtitle="Block phone number for selected items."
                iconName="PhSimCard"
                :cb="handlePhoneNumberBlock"
            />
        </div>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Heading, Card } from '@components'
    import MenuButton from './fragments/MenuButton.vue'
    import { inject, computed } from 'vue'
    
    const {configData} = inject('configData')
    const { handleFraudCheck, handlePhoneNumberBlock, handleIPBlock } = inject('useOrders')
    const shouldShow = computed(() => {
        const {
            fraud_customer_checker,
            courier_automation,
            ip_block,
            phone_number_block
        } = configData.value

        return fraud_customer_checker || courier_automation || ip_block || phone_number_block
    })
</script>