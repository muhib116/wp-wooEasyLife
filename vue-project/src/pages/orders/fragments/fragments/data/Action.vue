<template>
    <div class="grid gap-2">
        <button
            class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-orange-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
            @click="toggleNotesModel = true"
        >
            Notes
        </button>
        <button
            class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-sky-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
            @click="toggleAddressModel = true"
        >
            Address
        </button>
        <button
            class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-blue-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
            title="Order details"
            @click="(e) => {
                e.preventDefault();
                setActiveOrder(order)
            }"
        >
            Order Details
        </button>
    </div>


    <Modal 
        v-model="toggleAddressModel"
        @close="toggleAddressModel = false"
        class="max-w-[70%] w-full"
        title="Address manage"
    >
        <Address :order="order" />
    </Modal>

    <Modal 
        v-model="toggleNotesModel"
        @close="toggleNotesModel = false"
        class="max-w-[50%] w-full"
        title="Order Notes"
        hideFooter
    >
        <Notes
            :order="order"
        />
    </Modal>
</template>

<script setup lang="ts">
    import { Icon, Modal } from '@components'
    import { ref, inject } from 'vue'
    import Notes from '@/pages/orders/fragments/fragments/notes/Index.vue'
    import Address from '@/pages/orders/fragments/fragments/address/Index.vue'

    defineProps({
        order: Object
    })
    
    const toggleNotesModel = ref(false)
    const toggleAddressModel = ref(false)

    const {
        setActiveOrder
    } = inject('useOrders')
</script>