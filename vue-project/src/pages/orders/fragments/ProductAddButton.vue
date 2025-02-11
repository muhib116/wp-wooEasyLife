<template>
    <Button.Outline 
        class="justify-center"
        v-bind="$attrs"
        @click="toggleProductAddModal = true"
    >
        Add Product
        <Icon
            name="PhPlus"
            weight="bold"
        />
    </Button.Outline>

    <Modal 
        cancelText
        confirmText
        hideHeader
        hideFooter
        v-model="toggleProductAddModal" 
        @close="toggleProductAddModal = false"
    >
        <ProductListWithSearch
            @close="toggleProductAddModal = false"
            :addProductToForm="handleUpdateOrder"
        />
    </Modal>
</template>

<script setup lang="ts">
    import { Button, Icon, Modal, ProductListWithSearch } from '@components'
    import { ref, inject } from 'vue'

    defineOptions({
        inheritAttrs: false
    })

    const toggleProductAddModal = ref(false);

    const {
        handleUpdateOrder
    } = inject('useOrders')
</script>