<template>
    <div class="grid gap-2 justify-center text-center">
        <Button.Native 
            class="size-8 bg-orange-500 text-white grid place-content-center rounded-full shadow border hover:bg-white hover:border-orange-500 hover:text-orange-500 active:scale-75 duration-200 overflow-hidden"
            @onClick="btn => {
                item.product_quantity++
                handleUpdateOrder(item, btn)
            }"
        >
            <Icon
                name="PhPlus"
                size="20"
                weight="bold"
            />
        </Button.Native>
        <span>
            {{ item.product_quantity }}
        </span>
        <Button.Native
            class="size-8 bg-orange-500 text-white grid place-content-center rounded-full shadow border hover:bg-white hover:border-orange-500 hover:text-orange-500 active:scale-75 duration-200 overflow-hidden"
            @onClick="btn => {
                if(item.product_quantity>=1) {
                    item.product_quantity--
                }
                handleUpdateOrder(item, btn)
            }"
        >
            <Icon
                name="PhMinus"
                size="20"
                weight="bold"
            />
        </Button.Native>
    </div>
</template>

<script setup lang="ts">
    import { Button, Icon } from '@components'
    import { inject } from 'vue'

    defineProps<{
        item: {
            product_quantity: number
        }
    }>()
    
    const {
        handleUpdateOrder
    } = inject('useOrders')
</script>