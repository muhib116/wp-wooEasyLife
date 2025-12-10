<template>
    <div
        v-if="order?.customFieldData"
        class="relative"
        v-click-outside="() => toggleCartFlowFieldsData ? toggleCartFlowFieldsData = false : null"
    >
        <button 
            class="relative cursor-pointer"
            @click="toggleCartFlowFieldsData = !toggleCartFlowFieldsData"
            title="Toggle custom fields info"
        >
            <Icon name="PhTextbox" size="25" />
        </button>
        <div 
            v-if="toggleCartFlowFieldsData" 
            class="absolute top-full right-0 md:left-1/2 md:-translate-x-1/2 bg-white border p-2 w-[300px] shadow-lg z-10"
        >
            <h3 class="font-bold text-sm">Custom Fields Info</h3>
            <!-- getting custom field data as object, make loop here to show all key value pairs -->
            <div class="divide-y">
                <div
                    class="flex items-center justify-between gap-2 py-1"
                    v-for="(value, key) in order?.customFieldData" 
                    :key="key"
                >
                    <span class="flex-1 text-sm">{{ value }}</span>
                    <div class="relative" v-click-outside="() => activeDropdown[key] = false">
                        <Button.Native
                            title="Save to note"
                            class="hover:text-orange-500"
                            @onClick="() => activeDropdown[key] = !activeDropdown[key]"
                        >
                            <Icon name="PhLink" size="22" />
                        </Button.Native>
                        
                        <!-- Dropdown Menu -->
                        <div 
                            v-if="activeDropdown[key]"
                            class="absolute divide-y right-0 top-full mt-1 bg-white border shadow-lg rounded z-20 min-w-[160px]"
                        >
                            <Button.Native
                                @onClick="(btn: any) => handleSave(btn, order, value, 'courier_note', key)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm flex items-center gap-2"
                            >
                                <Icon name="PhTruck" size="16" />
                                Save as<br/>Courier Note
                            </Button.Native>
                            <Button.Native
                                @onClick="(btn: any) => handleSave(btn, order, value, 'invoice_note', key)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm flex items-center gap-2"
                            >
                                <Icon name="PhReceipt" size="16" />
                                Save as<br/>Invoice Note
                            </Button.Native>
                            <Button.Native
                                @onClick="(btn: any) => handleSave(btn, order, value, 'customer_note', key)"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm flex items-center gap-2"
                            >
                                <Icon name="PhUser" size="16" />
                                Save as<br/>Customer Note
                            </Button.Native>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Icon, Button } from '@/components'
    import { ref, reactive } from 'vue'
    import { saveOrderNote } from '@/api'
    import { toast } from 'vue3-toastify'

    const props = defineProps<{
        order: any
    }>()
    const toggleCartFlowFieldsData = ref(false);
    const activeDropdown = reactive<Record<string | number, boolean>>({});

    const handleSave = async (btn: any, order: any, value: string, noteType: 'courier_note' | 'invoice_note' | 'customer_note', key: string | number) => {
        // Prepare payload with the selected note type updated
        const payload = {
            order_id: order.id,         
            courier_note: noteType === 'courier_note' 
                ? (order.order_notes.courier_note ? `${order.order_notes.courier_note}\n${value}` : value)
                : order.order_notes.courier_note || '',
            customer_note: noteType === 'customer_note'
                ? (order.order_notes.customer_note ? `${order.order_notes.customer_note}\n${value}` : value)
                : order.order_notes.customer_note || '',
            invoice_note: noteType === 'invoice_note'
                ? (order.order_notes.invoice_note ? `${order.order_notes.invoice_note}\n${value}` : value)
                : order.order_notes.invoice_note || ''
        }

        try {
            btn.isLoading = true
            await saveOrderNote(payload)
            
            // Update the order notes in the local state
            order.order_notes[noteType] = payload[noteType]
            
            // Show success message
            const noteTypeLabel = noteType.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())
            toast.success(`Saved as ${noteTypeLabel}!`)
        } catch (error) {
            toast.error('Failed to save note. Please try again.')
            console.error('Error saving note:', error)
        } finally {
            btn.isLoading = false
            // Close the dropdown
            activeDropdown[key] = false
        }
    }
</script>