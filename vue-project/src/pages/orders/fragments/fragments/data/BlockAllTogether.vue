<template>
    <Button.Native
        v-if="userBlockHasEnabled"
        @onClick="async (btn) => {
            await handleBlocking(btn)
            $emit('closeDropdown')
        }"
        class="w-full font-medium hover:scale-1 gap-0 whitespace-nowrap text-left px-3 py-2 text-sm bg-red-400 text-white rounded disabled:opacity-50 hover:bg-red-600"
    >
        <Icon name="PhLockSimple" size="16" class="inline-block mr-2"/> Block this user
    </Button.Native>
</template>

<script setup lang="ts">
import { Button, Icon } from '@/components'
import { computed, inject } from 'vue'

const props = defineProps<{
    order: any
}>()

const { configData } = inject('configData') as any
const {
    clearSelectedOrders,
    setSelectedOrder,
    getOrders,
    handlePhoneNumberBlock, 
    handleEmailBlock, 
    handleDeviceBlock,
    handleIPBlock,
    showNotification // Make sure this is injected or imported
} = inject('useOrders') as any

const handleBlocking = async (btn: { isLoading: boolean }) => {
    const errors: string[] = [];
    let blockCount = 0;

    clearSelectedOrders();
    setSelectedOrder(props.order);

    // Prepare block actions
    const blockActions = [
        { enabled: configData?.value?.ip_block, fn: () => handleIPBlock(btn, false), msg: 'IP block failed' },
        { enabled: configData?.value?.phone_number_block, fn: () => handlePhoneNumberBlock(btn, false), msg: 'Phone number block failed' },
        { enabled: configData?.value?.email_block, fn: () => handleEmailBlock(btn, false), msg: 'Email block failed' },
        { enabled: configData?.value?.device_block, fn: () => handleDeviceBlock(btn, false), msg: 'Device block failed' },
    ].filter(action => action.enabled);

    if (blockActions.length === 0) {
        showNotification?.({ type: 'warning', message: 'No block method is enabled.' });
        return { success: false, errors: ['No block method is enabled.'], blockCount: 0 };
    }

    // Run all enabled block actions in parallel and collect their results
    const results = await Promise.all(blockActions.map(action => action.fn()));

    results.forEach((result, idx) => {
        if (result && result.success) {
            blockCount += result.blockCount || 0;
        } else {
            // Prefer detailed error from result, fallback to default msg
            if (result && result.errors && result.errors.length) {
                errors.push(...result.errors);
            } else {
                errors.push(blockActions[idx].msg);
            }
        }
    });

    if (blockCount > 0 && errors.length === 0) {
        showNotification?.({ type: 'success', message: 'User blocked by all enabled methods.' });
    } else if (blockCount > 0 && errors.length > 0) {
        showNotification?.({ type: 'warning', message: `Some blocks failed: ${errors.join('; ')}` });
    } else {
        showNotification?.({ type: 'error', message: errors.join('; ') });
    }

    clearSelectedOrders();
    await getOrders();

    return { success: blockCount > 0 && errors.length === 0, errors, blockCount };
};

const userBlockHasEnabled = computed(() => {
    return configData?.value?.ip_block || configData.value.phone_number_block || configData.value.email_block || configData.value.device_block
})
</script>