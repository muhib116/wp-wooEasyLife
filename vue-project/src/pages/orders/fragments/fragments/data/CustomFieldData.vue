<template>
    <div
        v-if="order?.customFieldData"
        class="relative"
        v-click-outside="() => toggleCartFlowFieldsData ? toggleCartFlowFieldsData = false : null"
    >
        <button 
            class="relative cursor-pointer"
            @click="toggleCartFlowFieldsData = !toggleCartFlowFieldsData"
            title="Toggle custom fields info"
        >
            <Icon name="PhInfo" size="25" />
        </button>
        <div 
            v-if="toggleCartFlowFieldsData" 
            class="absolute top-full right-0 md:left-1/2 md:-translate-x-1/2 bg-white border p-2 w-[300px] shadow-lg z-10"
        >
            <h3 class="font-bold text-sm">Custom Fields Info</h3>
            <!-- getting custom field data as object, make loop here to show all key value pairs -->
            <div class="divide-y">
                <div
                    class="flex items-center gap-2 py-1"
                    v-for="(value, key) in order?.customFieldData" 
                    :key="key"
                >
                    {{ value }}
                    <button
                        title="Add as courier note!"
                        class="hover:text-orange-500"
                    >
                        <Icon name="PhNotePencil" size="22" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@/components'
import { ref } from 'vue'

const props = defineProps<{
    order: any
}>()
const toggleCartFlowFieldsData = ref(false);
</script>