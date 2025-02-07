<template>
    <div
        v-if="order.order_notes.invoice_note"
        class="border-t-2 pt-4 text-xs text-gray-500 text-center mt-16"
    >
        Note: {{ order.order_notes.invoice_note }}
    </div>
</template>

<script lang="ts" setup>
    defineProps<{
        order: {
            order_notes: {
                invoice_note: string
            }
        }
    }>()
</script>