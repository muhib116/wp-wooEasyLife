<template>
    <div>
        <div
            class="flex flex-wrap items-start gap-2 relative"
        >
            <span 
                class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
                title="Order Id"
            >
                #{{ order?.id }}
            </span>
            <span 
                v-if="order?.order_source"
                class="px-1 bg-sky-500 text-white capitalize rounded-sm text"
                :class="{
                    '!bg-orange-500' : order?.order_source   
                }"
                title="Order source"
            >
                {{ order?.order_source }}
            </span>

            <QuickOrderStatusChange
                :order="order"
            >
                <BlockAllTogether
                    :order="order"
                />
            </QuickOrderStatusChange>

            <a 
                class="absolute top-0 left-full text-orange-500 hover:scale-150 duration-200 opacity-0 group-hover:opacity-100"
                :href="`${baseUrl}/wp-admin/post.php?post=${order?.id}&action=edit`"
                target="_blank"
            >
                <Icon 
                    name="PhArrowSquareOut"
                    size="20"
                    weight="bold"
                />
            </a>
        </div>
        <div
            class="flex gap-1 font-medium max-w-[225px]"
        >
            {{ order?.billing_address?.first_name }}
            {{ order?.billing_address?.last_name }}
            <span
                v-if="order?.repeat_customer"
                class="text-green-500 tex-sm"
                title="Repeat customer"
            >
                (R)
            </span>
        </div>
        <div class="flex gap-1 items-center whitespace-nowrap justify-between">
            <span>📅 {{ order?.date_created }}</span>
            <a 
                v-if="order?.referrer_url"
                :href="order?.referrer_url"
                target="_blank"
                class="bg-red-500 ml-1 px-1 inline-block font-medium text-white rounded shadow"
                title="Referrer URL"
            >
                URL
            </a>
        </div>
        <div class="flex items-center gap-2 truncate">
            <a :href="`tel:${order?.billing_address?.phone}`" class="flex gap-1 items-center text-orange-500 underline">
                📞 {{ order?.billing_address?.phone }}
            </a>
            <Whatsapp
                :phone_number="order?.billing_address?.phone"
            />
        </div>
        <div
            class="flex gap-1 items-center"
            :title="`${order?.billing_address?.address_1}, ${order?.billing_address?.address_2}`"
        >
            <div class="max-w-[240px] break-all">
                🏠 
                {{ order?.billing_address?.address_1 }},
                {{ order?.billing_address?.address_2 }}
            </div>
        </div>

        <div class="flex flex-wrap gap-x-2">
            <span
                v-if="order?.ip_block_listed"
                class="!py-0 !text-[10px] flex items-center text-[#f93926]"
            >
                <Icon
                    name="PhCellTower"
                    size="12"
                />
                Ip blocked
            </span>
            <span
                v-if="order?.phone_block_listed"
                class="!py-0 !text-[10px] flex items-center text-[#e82661]"
            >
                <Icon
                    name="PhSimCard"
                    size="12"
                />
                Phone blocked
            </span>
            <span
                v-if="order?.email_block_listed"
                class="!py-0 !text-[10px] flex items-center text-[#444444]"
            >
                <Icon
                    name="PhEnvelopeSimple"
                    size="12"
                />
                Email blocked
            </span>
            <span
                v-if="order?.device_block_listed"
                class="!py-0 !text-[10px] flex items-center text-[#e82661]"
            >
                <Icon
                    name="PhDeviceMobileSlash"
                    size="12"
                />
                Device blocked
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, inject } from 'vue'
    import { Icon, Whatsapp, Button } from '@/components'
    import { baseUrl, changeStatus } from '@/api'
    import QuickOrderStatusChange from '@/pages/orders/fragments/fragments/data/QuickOrderStatusChange.vue'
    import BlockAllTogether from '@/pages/orders/fragments/fragments/data/BlockAllTogether.vue'
    
    const props = defineProps({
        order: Object
    })
</script>