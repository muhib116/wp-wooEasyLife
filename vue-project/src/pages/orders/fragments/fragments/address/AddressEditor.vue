<template>
    <div class="relative">
        <div class="flex bg-white z-20  justify-between items-center sticky -top-3">
            <h3 class="font-semibold text-lg mb-2">
                {{ title }}
            </h3>

            <div class="flex gap-4">
                <Button.Native
                    v-if="isEditable"
                    @onClick="isEditable = false"
                    title="Back to preview"
                    class="gap-1"
                >
                    <Icon
                        name="PhCaretLeft"
                        size="25"
                    />
                    Back
                </Button.Native>
                <Button.Primary
                    v-if="isEditable"
                    @onClick="handleUpdate"
                    icon="PhCheck"
                    title="Click to save"
                >
                    Save
                </Button.Primary>
                <Button.Native
                    v-else
                    class="!p-0 bg-transparent shadow-none opacity-60"
                    @onClick="handleUpdate"
                    title="Click to edit"
                >
                    <Icon
                        name="PhNotePencil"
                        size="25"
                    />
                </Button.Native>
            </div>
        </div>

        <AddressForm
            v-if="isEditable"
            :address="address"
        />
        <AddressPreview
            v-else
            :address="address"
        />
    </div>
</template>

<script setup lang="ts">
    import { inject, ref } from 'vue'
    import AddressPreview from './AddressPreview.vue'
    import AddressForm from './AddressForm.vue'
    import { Icon, Button } from '@components'
import { normalizePhoneNumber, validateBDPhoneNumber } from '@/helper';

    const props = defineProps<{
        title?: string
        address: object
    }>()

    const {
        handleAddressEdit
        
    } = inject('useAddress')

    const isEditable = ref(false)
    const handleUpdate = async (btn) => {
        if(!validateBDPhoneNumber(props.address?.phone)) {
            alert('Phone number is not valid! \n Enter a valid bangladeshi number.')
            return
        }

        if(isEditable.value){
            try {
                btn.isLoading = true
                props.address.phone = normalizePhoneNumber(props.address?.phone)
                await handleAddressEdit(props.address)
            } finally {
                btn.isLoading = false
            }
        }
        isEditable.value = !isEditable.value
    }
</script>