<template>
    <div class="print:hidden flex justify-between text-[10px] px-4 my-4">
        <div 
            v-bind="$attrs"
            class="flex justify-center md:justify-start flex-wrap gap-2 items-center relative"
            v-click-outside="() => toggleCourierDropdown = false"
        >
            <span 
                v-if="[...selectedOrders].length"
                class="size-6 shadow -mr-2 flex items-center justify-center md:text-[10px] aspect-square rounded-full bg-orange-500 text-white "
            >
                {{ [...selectedOrders].length }}
            </span>
            <template
                v-for="(item, index) in actionBtns"
                :key="index"
            >
                <div class="relative"
                    v-if="item.active"
                >
                    <Button.Native
                        :title="item.title"
                        class="py-1 px-2 border shadow rounded-sm"
                        :style="{
                            backgroundColor: item.bg,
                            color: item.color   
                        }"
                        @onClick="item.method"
                    >
                        <Icon
                            :name="item.icon"
                            size="16"
                        />
                        {{ item.title }}
                    </Button.Native>
                </div>
            </template>

            <CourierEntry
                @close="$emit('close')"
                showRefreshBtn
            />

            <Button.Native
                v-if="orders && orders[0]?.total_new_orders_not_handled_by_wel_plugin && userData?.remaining_order > 0"
                class="opacity-100 w-fit text-white bg-green-500 shadow rounded-sm truncate px-2 md:px-1 py-1"
                title="Include your previous new orders that are missing from this order list."
                @onClick="async (btn) => {
                    await include_past_new_orders_thats_not_handled_by_wel_plugin(orders[0].total_new_orders_not_handled_by_wel_plugin, btn)
                    $emit('close')
                }"
            >
                <Icon
                    name="PhArrowSquareIn"
                    size="16"
                    weight="bold"
                    class="rotate-[180deg]"
                />
                Include Past New Orders
                ({{ orders[0].total_new_orders_not_handled_by_wel_plugin }})
            </Button.Native>

            <Button.Native
                v-if="orders && orders[0]?.total_new_order_handled_by_wel_but_balance_cut_failed && userData?.remaining_order > 0"
                class="opacity-100 w-fit text-white bg-teal-500 shadow rounded-sm truncate px-2 md:px-1 py-1"
                title="Include your new orders that failed to deduct balance."
                @onClick="async (btn) => {
                    await include_balance_cut_failed_new_orders(orders[0].total_new_order_handled_by_wel_but_balance_cut_failed, btn)
                    $emit('close')
                }"
            >
                <Icon
                    name="PhArrowSquareIn"
                    size="16"
                    weight="bold"
                    class="rotate-[180deg]"
                />
                Include missing new orders
                ({{ orders[0].total_new_order_handled_by_wel_but_balance_cut_failed }})
            </Button.Native>
        </div>

        <div>
            <slot></slot>
        </div>
    </div>

    <Modal
        v-model="toggleNewOrder"
        title="Create New Order"
        @close="toggleNewOrder = false"
        class="max-w-[650px] w-full"
        hideFooter
    >
        <CreateNewOrder />
    </Modal>
</template>

<script setup lang="ts">
    import { inject, computed, ref } from 'vue'
    import CreateNewOrder from './createNewOrder/Index.vue'
    import {
        Button,
        Icon,
        Modal,
        CourierEntry
    } from '@components'

    defineOptions({
        inheritAttrs: false
    })
    
    const toggleCourierDropdown = ref(false)
    const {configData} = inject('configData')
    const { userData } = inject('useServiceProvider')


    const { 
        handleFraudCheck, 
        handleCourierEntry,
        handlePhoneNumberBlock, 
        handleEmailBlock, 
        handleIPBlock,
        selectedOrders,
        showInvoices,
        toggleNewOrder,
        refreshBulkCourierData,
        orders,
        include_past_new_orders_thats_not_handled_by_wel_plugin,
        include_balance_cut_failed_new_orders
    } = inject('useOrders')

    const actionBtns = computed(() => [
        {
            icon: 'PhPlus',
            title: 'Create New Order',
            active: true,
            bg: '#155E95',
            color: '#fff',
            method: () => {
                toggleNewOrder.value = true
            }
        },
        {
            icon: 'PhPrinter',
            title: 'Print Invoice',
            active: configData.value.invoice_print,
            bg: '#16404D',
            color: '#fff',
            method: () => {
                showInvoices.value = true
            }
        },
        {
            icon: 'PhNetworkSlash',
            title: 'Block IP',
            active: configData.value.ip_block,
            bg: '#F93827',
            color: '#fff',
            method: handleIPBlock
        },
        {
            icon: 'PhSimCard',
            title: 'Block Phone',
            active: configData.value.phone_number_block,
            bg: '#E82561',
            color: '#fff',
            method: handlePhoneNumberBlock
        },
        {
            icon: 'PhEnvelopeSimple',
            title: 'Block Email',
            active: configData.value.email_block,
            bg: '#444',
            color: '#fff',
            method: handleEmailBlock
        },
        {
            icon: 'PhUserList',
            title: 'Fraud Check',
            active: configData.value.fraud_customer_checker,
            bg: '#F14A00',
            color: '#fff',
            method: handleFraudCheck
        }
    ])
</script>