<template>
    <div class="relative">
        <MessageBox
            :title="alertMessage.title"
            :type="alertMessage.type"
        />
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />
        <div class="space-y-4 pr-1">
            <Input.Primary
                label="Monthly sales target amount"
                type="number"
                v-model="salesTargetData.data.monthly_target_amount"
            />
            <Input.Primary
                label="Daily sales target amount"
                type="number"
                :bgStyle="{background: '#efefef'}"
                :modelValue="dailyTargetAmount"
            />
            <Input.Primary
                label="Start Date"
                type="date"
                v-model="salesTargetData.data.start_date"
            />
            <Input.Primary
                label="End Date"
                type="date"
                :bgStyle="{background: '#efefef'}"
                :modelValue="endDate"
            />
            <Button.Primary 
                class="ml-auto"
                @onClick="async (btn) => {
                    await saveSalesTarget(btn)
                    $emit('close')
                }"
            >
                Save
            </Button.Primary>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Input, Button, MessageBox, Loader } from '@components'
    import { inject } from 'vue'

    const {
        isLoading,
        alertMessage,
        endDate,
        salesTargetData,
        dailyTargetAmount,
        saveSalesTarget
    } = inject('useSalesTarget')
</script>