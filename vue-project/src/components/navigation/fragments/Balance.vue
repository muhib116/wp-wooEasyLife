<template>
    <div class="flex items-center gap-2">
        <Button.Native
            v-if="isValidLicenseKey"
            :loading="userDataLoading"
            class="py-[2px] md:py-[4px] px-1 md:px-2 rounded font-medium text-[10px] md:text-sm text-white"
            :class="{
                'animate-bounce' : balance <= 5
            }"
            :style="{
                backgroundColor: getBgColor
            }"
            :title="`Remaining Order Balance ${userData?.remaining_order || 0}`"
        >
            Token: {{ userData?.remaining_order || 0 }}
        </Button.Native>
        
        <Button.Native
            v-if="isValidLicenseKey"
            :loading="userDataLoading"
            class="py-[2px] md:py-[4px] px-1 md:px-2 rounded bg-gray-700 font-medium text-[10px] md:text-sm text-white mr-4"
            :title="`Remaining SMS Balance ${userData?.sms_balance || 0}tk`"
        >
            SMS: {{ userData?.sms_balance.toFixed(2) || 0 }}tk
        </Button.Native>
    </div>
</template>

<script setup lang="ts">
    import { Button } from '@/components'
    import { watch, inject, ref } from 'vue'

    const {
        userData,
        userDataLoading,
        isValidLicenseKey
    } = inject('useServiceProvider')

    const balance = ref(userData.value?.remaining_order || 0);
    const getBgColor = ref('#00b002');

    watch(() => [userDataLoading], () => {
        if (balance.value <= 20 && balance.value > 10) {
            getBgColor.value = '#f97315';
        } else if (balance.value <= 10 && balance.value > 5) {
            getBgColor.value = '#ff4733';
        } else if (balance.value <= 5) {
            getBgColor.value = '#ff0000';
        }
    }, {
        deep: true,
        immediate: true
    })
</script>