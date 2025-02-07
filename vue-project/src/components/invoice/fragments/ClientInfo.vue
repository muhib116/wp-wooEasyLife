<template>
    <div class="grid grid-cols-2 items-center mt-8">
        <div>
            <p class="font-bold text-gray-800">
                Bill to :
            </p>
            <p class="text-gray-500">
                {{ data.first_name }} {{ data.last_name }}
                <br />
                {{ data.address_1 }} {{ data.address_2 }}
            </p>
            <p class="text-gray-500">
                {{ data.phone }}
            </p>
        </div>
    
        <div class="text-right">
            <p class="font-semibold">
                Consignment id:
                <span class="text-gray-500">
                    {{ courierData?.consignment_id }}
                </span>
            </p>
            <p class="">
                Invoice number:
                <span class="text-gray-500">
                    {{ data.order_id }}
                </span>
            </p>
            <p>
                Invoice date: 
                <span class="text-gray-500">
                    {{ date }}
                </span>
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { printDate } from '@/helper'
    defineProps<{
        data: {
            first_name: string
            last_name: string
            address_1: string
            address_2: string
            phone: string
            order_id: string
        }
        date: string
        courierData: {
            consignment_id: string
        }
    }>()
</script>