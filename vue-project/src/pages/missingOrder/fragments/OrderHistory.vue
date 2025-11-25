<template>
    <button 
        v-if="item?.lifetime_orders && item.lifetime_orders?.length > 0" 
        class="relative"
        @click="() => {
            toggleOrderHistory = !toggleOrderHistory;
        }"
        v-click-outside="() => toggleOrderHistory && (toggleOrderHistory = false)"
    >
        <Icon 
            name="PhInfo" 
            size="22"
        />
        <div v-if="toggleOrderHistory" class="absolute top-full -right-4 md:right-0 bg-white border shadow-md rounded mt-1 z-10 w-fit block min-w-[250px]">
            <div class="text-center font-semibold p-2 border-b border-opacity-50 pb-1">
                Lifetime Orders History
            </div>
            
            <div class="grid gap-2">
                <div 
                    v-for="(order, index) in item.lifetime_orders" 
                    :key="order.status"
                    class="flex justify-between items-start gap-2 p-3"
                    :class="{
                        'border-t border-opacity-10' : index != 0
                    }"
                >
                    <div class="flex-1">
                        <div class="text-gray-800 text-left">{{ order.title }}</div>
                        <div class="text-xs text-gray-500 text-left font-light" v-if="order.order_at">
                            {{ order.order_at }}
                        </div>
                    </div>
                    <div class="bg-blue-400 text-white size-6 grid place-content-center text-sm rounded-full font-light">
                        {{ order.count }}
                    </div>
                </div>
            </div>
            <div class="border-t border-opacity-50 p-2 text-xs text-gray-800 text-center">
                Total: {{ item.lifetime_orders.reduce((sum, order) => sum + order.count, 0) }} orders
            </div>
        </div>
    </button>
</template>

<script lang="ts" setup>
import { Icon } from '@/components'
import { ref } from 'vue'

const props = defineProps<{
  item: {
    lifetime_orders: Array<{
      title: string;
      order_at: string;
      count: number;
    }>;
  };
}>();

const toggleOrderHistory = ref(false);
</script>