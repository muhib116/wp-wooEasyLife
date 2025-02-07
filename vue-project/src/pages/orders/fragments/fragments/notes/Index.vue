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

        <Button.Primary
            class="ml-auto"
            @onClick="(btn) => handleSave(order, btn)"
        >
            Save Note
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { Textarea, Button } from '@components'
    import { saveOrderNote } from '@/api'

    const props = defineProps<{
        order: {
            productId: number;
            productName: string;
            quantity: number;
            price: number;
            total: number;
        }
    }>()

    const handleSave = async (order: object, btn: object) => {
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