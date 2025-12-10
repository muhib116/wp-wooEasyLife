<template>
    <div class="space-y-3">
        <Textarea.Native
            label="Customer Note"
            placeholder="Write customer note."
            v-model="order.order_notes.customer_note"
        />
        <Textarea.Native
            label="Invoice Note"
            placeholder="Write invoice note."
            v-model="order.order_notes.invoice_note"
        />
        <Textarea.Native
            label="Courier Note"
            placeholder="Write courier note."
            v-model="order.order_notes.courier_note"
        />
        
        <div class="space-y-1 mt-2">
            <h3 class="text-[16px]">COD Modification Note:</h3>
            <div class="text-red-500" v-html="order.cod_modification_note"></div>
        </div>

        <Button.Primary
            class="ml-auto"
            @onClick="(btn) => handleSave(order, btn)"
        >
            Save Note
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { Textarea, Button } from '@/components'
    import { saveOrderNote } from '@/api'

    interface Order {
        id: number;
        order_notes: {
            customer_note: string;
            courier_note: string;
            invoice_note: string;
        };
        cod_modification_note: string;
    }

    const props = defineProps<{
        order: Order
    }>()

    const handleSave = async (order: Order, btn: any) => {
        const payload = {
            order_id: order.id,            
            customer_note: order.order_notes.customer_note,            
            courier_note: order.order_notes.courier_note,            
            invoice_note: order.order_notes.invoice_note
        }

        try {
            btn.isLoading = true
            await saveOrderNote(payload)
        } finally {
            btn.isLoading = false
        }
    }
</script>