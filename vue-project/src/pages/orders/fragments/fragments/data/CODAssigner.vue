<template>
    <div class="flex items-center gap-2">
        <span class="font-bold">COD / Total:</span>
        <div class="relative flex-1 max-w-[150px]">
            <!-- <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500" v-html="order.currency_symbol"></span> -->
            
            <Input.Primary
                type="number"
                v-model="newTotal"
                placeholder="Enter new total"
                class="border rounded-sm py-1 pl-6 pr-2 w-full"
                :disabled="isUpdating"
                @keyup.enter="handleUpdate"
                hideMicrophone
            />
        </div>
        
        <Button.Native
            class="!py-1 px-2"
            @onClick="handleUpdate"
            :loading="isUpdating"
            title="Save new COD amount"
        >
            <Icon name="PhChecks" size="25" />
        </Button.Native>
    </div>
</template>

<script setup lang="ts">
    import { ref, inject, watchEffect } from 'vue';
    import { Input, Button, Icon } from '@components';
    import { updateOrderTotal } from '@/api'; // Import the new API function
    import { filterOrderById, showNotification } from "@/helper";

    const props = defineProps({
        order: Object as () => {
            id: number | string,
            total: number,
            currency_symbol: string
        }
    });

    const { getOrders, setActiveOrder, orders } = inject('useOrders'); // Inject to refresh order list
    
    const isUpdating = ref(false);
    const newTotal = ref(props.order.total); // Initialize with current total

    // Prop পরিবর্তন হলে newTotal কে আপডেট করা
    watchEffect(() => {
        newTotal.value = props.order.total;
    });

    const handleUpdate = async (btn: any) => {
        if (newTotal.value === props.order.total) {
            showNotification({ type: 'info', message: 'No change detected in COD amount.' });
            return;
        }

        if (newTotal.value < 0) {
            showNotification({ type: 'danger', message: 'Total amount cannot be negative.' });
            return;
        }

        const payload = {
            order_id: props.order.id,
            new_total: parseFloat(newTotal.value)
        };

        try {
            isUpdating.value = true;
            btn.isLoading = true;
            
            await updateOrderTotal(payload);

            // অর্ডার লিস্ট রিফ্রেশ করা
            await getOrders(); 
            const order = filterOrderById(props.order?.id, orders.value)
            setActiveOrder(order)
            showNotification({ 
                type: 'success', 
                message: `Order Total (COD) updated to ${props.order.currency_symbol}${payload.new_total}` 
            });

        } catch (error) {
            console.error("COD Update Error:", error);
            showNotification({ type: 'danger', message: 'Failed to update COD amount.' });
        } finally {
            isUpdating.value = false;
            btn.isLoading = false;
        }
    };
</script>