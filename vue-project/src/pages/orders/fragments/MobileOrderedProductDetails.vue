<template>
    <div class="grid gap-4">
        <ProductAddButton />
        <div
            v-for="item in activeOrder?.product_info?.product_info || []"
            :key="item.id"
            class="p-4 border border-gray-200/70 rounded"
        >
            <div class="grid grid-cols-[60px_1fr] items-center gap-3 text-lg">
                <img 
                    :src="item.product_image" 
                    alt="product image"
                    class="size-[70px] object-center object-cover"
                />
                <div class="w-full text-sm leading-[20px]">
                    <h3>{{ item.product_name }}</h3>
                    <hr class="my-2 border-b-0 border-gray-100" />
                    <div class="flex font-bold divide-x-[1px]">
                        <div class="pr-2">
                            <span v-html="activeOrder.currency_symbol"></span>{{ item.product_price }}
                        </div>
                        <div class="pl-2">
                            Total 
                            <span v-html="activeOrder.currency_symbol"></span>{{ item.product_quantity * item.product_price }}
                        </div>
                    </div>
                </div>
            </div>

            <ProductQuantityHandle
                class="!flex flex-row-reverse w-[110px] justify-between items-center ml-auto mt-4 text-[20px]"
                :item="item"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import ProductQuantityHandle from './ProductQuantityHandle.vue';
    import ProductAddButton from './ProductAddButton.vue';

    const {
        activeOrder
    } = inject('useOrders')
</script>