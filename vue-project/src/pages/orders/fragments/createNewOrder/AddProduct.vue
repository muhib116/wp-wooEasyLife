<template>
    <Table.Table v-if="form.products?.length">
        <Table.THead>
            <Table.Th>Item</Table.Th>
            <Table.Th>Price</Table.Th>
            <Table.Th>Quantity</Table.Th>
            <Table.Th class="text-right">Total</Table.Th>
            <Table.Th>Action</Table.Th>
        </Table.THead>
        <Table.TBody>
            <Table.Tr
                v-for="(item, index) in form.products"
                :key="index"
            >
                <Table.Td>
                    <div class="flex gap-4 items-center">
                        <img
                            :src="item.product.image"
                            class="size-8"
                        />
                        <p>
                            #{{ item.product.id }} {{ item.product.name }}
                        </p>
                    </div>
                </Table.Td>
                <Table.Td>
                    <div class="whitespace-nowrap">
                        <span v-html="item.product.currency_symbol"></span>{{ item.product.price }}
                    </div>
                </Table.Td>
                <Table.Td>
                    <Input.Native
                        label="quantity"
                        type="number"
                        v-model="item.quantity"
                        class="bg-transparent border w-10 pl-1 mx-auto"
                        @input="calculateCouponDiscountAmount(form.coupons)"
                    />
                </Table.Td>
                <Table.Td class="text-right">
                    <div class="whitespace-nowrap">
                        <span v-html="item.product.currency_symbol"></span>{{ +(item.product.price) * +(item.quantity) }}
                    </div>
                </Table.Td>
                <Table.Td>
                    <Button.Native 
                        class="hover:text-red-500 ml-auto" 
                        title="Remove product"
                        @click="() => {
                            form.products.splice(index, 1)
                            calculateCouponDiscountAmount(form.coupons)
                        }"
                    >
                        <Icon 
                            name="PhX" 
                            weight="bold"
                            size="20"
                        />
                    </Button.Native>
                </Table.Td>
            </Table.Tr>

            <Table.Tr class="border-t border-black">
                <Table.Th colspan="3" class="text-right !font-light">
                    Items Subtotal:	
                </Table.Th>
                <Table.Th class="text-right">
                    <div class="whitespace-nowrap">
                        <span v-html="form.products[0].product.currency_symbol"></span>{{ getItemsTotal }}
                    </div>
                </Table.Th>
            </Table.Tr>
            <Table.Tr>
                <Table.Th colspan="3" class="text-right !font-light">
                    Coupon(s)/Discount:
                </Table.Th>
                <Table.Th class="text-right">
                    <div class="whitespace-nowrap">
                        -<span v-html="form.products[0].product.currency_symbol"></span>{{ couponDiscount }}
                    </div>
                </Table.Th>
            </Table.Tr>
            <Table.Tr>
                <Table.Th colspan="3" class="text-right !font-light">
                    Shipping {{ form.shippingMethod.method_title && `(${form.shippingMethod.method_title})` }}:
                </Table.Th>
                <Table.Th class="text-right">
                    <div class="whitespace-nowrap">
                        <span v-html="form.products[0].product.currency_symbol"></span>
                        {{ form.shippingMethod.shipping_cost || 0 }}
                    </div>
                </Table.Th>
            </Table.Tr>
            <Table.Tr>
                <Table.Th colspan="3" class="text-right !font-light">
                    Order Total:
                </Table.Th>
                <Table.Th class="text-right">
                    <div class="whitespace-nowrap">
                        <span v-html="form.products[0].product.currency_symbol"></span>
                        {{ (getItemsTotal - couponDiscount) + +(form.shippingMethod.shipping_cost || 0) }}
                    </div>
                </Table.Th>
            </Table.Tr>

        </Table.TBody>
    </Table.Table>


    <div v-click-outside="() => toggleProductList = false" >
        <Button.Primary
            class="!bg-orange-500"
            @click="toggleProductList = true"
        >
            <Icon
                name="PhPlus"
            />
            Add Product *
        </Button.Primary>


        <ProductListWithSearch
            v-if="toggleProductList"
            :addProductToForm="async (item, btn) => {
                addProductToForm(item, btn)
                toggleProductList = false
            }"
            @close="toggleProductList = false"
        />
    </div>
</template>

<script setup lang="ts">
    import { Input, Button, Icon, Table, ProductListWithSearch } from '@components'
    import { inject, ref } from 'vue'

    const toggleProductList = ref(false)
    const {
        form,
        getItemsTotal,
        couponDiscount,
        addProductToForm,
        calculateCouponDiscountAmount,
    } = inject('useCustomOrder')
</script>